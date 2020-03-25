$(document).ready(function () {

    const $data_source_handler = $('#data_source_handler');
    const $node_count_container = $('#node_count_container');
    const $node_count_input = $('#node_count_input');
    const $preloader = $('.transition-loader');
    const $full_preloader = $('.full_preloader');
    const $node_relations_outcoming = $('.node_relations_outcoming');
    const $node_relations_incoming = $('.node_relations_incoming');


    let data_source = localStorage.getItem('data_source') || 'flare';
    let node_length = parseInt(localStorage.getItem('node_length'));

    let url = '';

    let data = {};

    let k = 1;
    let x, y;
    let beta_value = parseFloat(localStorage.getItem('beta_value') || 0);
    let diameter = 960;
    let radius = diameter / 2;
    let innerRadius = radius - 120;
    let data_loaded = false;

    let cluster = d3.cluster()
        .size([360, innerRadius]);

    getDataUrl();
    menuSetup();


    $data_source_handler.on('change', function () {
        let new_data_source = $(this).val();

        if (new_data_source == data_source) {
            return;
        }

        data_source = new_data_source;

        localStorage.setItem('data_source', data_source);
        getDataUrl();
    });

    $node_count_input.on('change', function () {
        node_length = parseInt($(this).val()) || 10;
        localStorage.setItem('node_length', node_length);
        getDataUrl();
    });


    function getDataUrl() {
        preloaderActivate();

        $node_count_container.hide();

        switch (data_source) {
            case 'flare':
                url = '/flare.json';
                break;
            case 'elibrary':
                $node_count_container.show();

                $node_count_input.val(node_length);

                url = '/ajax.php?l=' + node_length;
                break;
            case 'generator':
                url = '/generator.php';
                break;
        }


        $data_source_handler.val(data_source);

        d3.json(url, function (er, cl) {
            data = cl;
            data_loaded = true;
            graphBuild();
        });

    }

    function menuSetup() {
        BetaSlider();

        $('.nice-select').niceSelect();
    }


    function BetaSlider() {
        let sliderSimple = d3
            .sliderBottom()
            .min(0)
            .max(1)
            .width(200)
            .ticks(10)
            .default(beta_value)
            .on('onchange', val => {
                d3.select('#value-simple').text(d3.format('.2')(val));
                beta_value = val;
                localStorage.setItem('beta_value', beta_value);
                graphBuild();
            });

        let gSimple = d3
            .select('#slider-simple')
            .append('svg')
            .append('g')
            .attr('transform', 'translate(50,30)');


        gSimple.call(sliderSimple);

        d3.select('#value-simple').text(sliderSimple.value());
    }


    function preloaderActivate() {
        $preloader.fadeIn();
        $full_preloader.show();
    }

    function preloaderDisable() {
        $preloader.fadeOut();
        $full_preloader.hide();
    }

    function graphBuild() {
        graphBuild1();
        preloaderDisable();
    }

    function graphBuild1() {
        if (!data_loaded) {
            return false;
        }

        let line = d3.radialLine()
            .radius(function (d) {
                return d.y;
            })
            .angle(function (d) {
                return d.x / 180 * Math.PI;
            })
            .curve(d3.curveBundle.beta(beta_value));

        let svg = d3.select("#graph_svg");

        svg.select("#first").remove();

        width = svg.node().getBoundingClientRect().width / 2;

        height = svg.node().getBoundingClientRect().height / 2;

        let zoomable_layer = svg.append('g')
            .attr('id', 'first');
        let graph_layer = zoomable_layer.append('g');


        let link = graph_layer.append("g").selectAll(".link"),
            node = graph_layer.append("g").selectAll(".node");


        graph_layer.attr(
            'transform', "translate(" + width + "," + height + ")"
        );


        Zoom(x, y, k);

        zoom = d3.zoom().scaleExtent([-Infinity, Infinity]).on('zoom', function () {
            let scale = d3.event.transform.k;
            let x = d3.event.transform.x;
            let y = d3.event.transform.y;

            // k *= scale;

            Zoom(x, y, scale);
        });

        svg.call(zoom);


        let root = d3.hierarchy(packageHierarchy(data), (d) => d.children);
        cluster(root);

        let nodes = root.descendants();


        let links = packageImports(nodes);
        console.log(links);


        link = link
            .data(links)
            .enter().append('path')
            .attr('class', 'link')
            // .merge(edges)
            .attr('d', d => line(d.source.path(d.target)));

        node = node
            .data(nodes.filter(function (n) {
                return !n.children;
            }))
            .enter().append("text")
            .attr("class", "node")
            .attr("dy", ".31em")
            .attr("transform", function (d) {
                return "rotate(" + (d.x - 90) + ")translate(" + (d.y + 8) + ",0)" + (d.x < 180 ? "" : "rotate(180)");
            })
            .style("text-anchor", function (d) {
                return d.x < 180 ? "start" : "end";
            })
            .text(function (d) {
                if (d.data.title !== undefined && d.data.title.length) {
                    return d.data.title;
                }
                return d.data.name;
            })
            .on("mouseover", mouseovered)
            .on("mouseout", mouseouted);

        node.append('title').text(function (d) {
            if (d.data.title !== undefined && d.data.title.length) {
                return d.data.title;
            }
            return d.data.name;
        });


        /**
         * @return {boolean}
         */
        function Zoom(px, py, pk) {
            if (!px || !py || !pk) {
                return false;
            }

            x = px;
            y = py;
            k = pk;

            return zoomable_layer.attr(
                'transform', "translate(" + x + "," + y + ") scale(" + k + ")"
            );
        }

        function showNodeRelations(p_data) {
            let incoming = [];
            let outcoming = [];
            for (let i = 0; i < p_data.imports.length; i++) {
                for (let j = 0; j < data.length; j++) {
                    if (data[j].name == p_data.imports[i]) {
                        if (data[j].title !== undefined) {
                            outcoming.push(data[j].title);
                        }
                        else {
                            outcoming.push(data[j].name);
                        }
                        break;
                    }
                }
            }

            for (let i = 0; i < data.length; i++) {
                for (let j = 0; j < data[i].imports.length; j++) {
                    if (data[i].imports[j] == p_data.name) {
                        if (p_data.title !== undefined) {
                            incoming.push(p_data.title);
                        }
                        else {
                            incoming.push(p_data.name);
                        }
                        break;
                    }
                }
            }

            if (outcoming.length) {
                $node_relations_outcoming.html(outcoming.join('<br>'));
                $node_relations_outcoming.show();
            }
            if (incoming.length) {
                $node_relations_incoming.html(outcoming.join('<br>'));
                $node_relations_incoming.show();
            }
        }

        function mouseovered(d) {
            showNodeRelations(d.data);

            node
                .each(function (n) {
                    n.have_target = n.have_source = false;
                });

            link
                .classed("link--target", function (l) {
                    if (l.target === d) {
                        return l.source.have_source = true;
                    }
                })
                .classed("link--source", function (l) {
                    if (l.source === d) {
                        return l.target.have_target = true;
                    }
                })
                .filter(function (l) {
                    return l.target === d || l.source === d;
                })
                .each(function () {
                    this.parentNode.appendChild(this);
                });


            node
                .classed("node--target", function (n) {
                    return n.have_target;
                })
                .classed("node--source", function (n) {
                    return n.have_source;
                });
        }

        function mouseouted(d) {
            $node_relations_outcoming.hide();
            $node_relations_incoming.hide();

            link
                .classed("link--target", false)
                .classed("link--source", false);

            node
                .classed("node--target", false)
                .classed("node--source", false);
        }

        // d3.select(self.frameElement).style("height", diameter + "px");

        // Lazily construct the package hierarchy from class names.
        function packageHierarchy(classes) {
            let map = {};

            classes.forEach(function (d) {
                find(d.name, d);
            });

            function find(name, data) {
                let node = map[name];
                let i;

                if (!node) {
                    map[name] = data || {name: name, children: []};
                    node = map[name];


                    if (name.length) {
                        i = name.lastIndexOf(".");
                        node.parent = find(name.substring(0, i));
                        node.parent.children.push(node);
                        node.key = name.substring(i + 1);
                    }
                }
                return node;
            }

            return map[""];
        }

        // Return a list of imports for the given array of nodes.
        function packageImports(nodes) {
            let map = {},
                imports = [];

            // Compute a map from name to node.
            nodes.forEach(function (d) {
                map[d.data.name] = d;
            });

            // For each import, construct a link from the source to target node.
            nodes.forEach(function (d) {
                if (d.data.imports) {
                    d.data.imports.forEach(function (i) {
                        imports.push({source: map[d.data.name], target: map[i], path: map[d.data.name].path(map[i])}   );
                    });
                }
            });

            return imports;
        }
    }

});
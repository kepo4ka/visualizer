$(document).ready(function () {

    const $data_source_handler = $('#data_source_handler');
    const $node_count_container = $('#node_count_container');
    const $node_count_input = $('#node_count_input');
    const $graph_type_handler = $('#graph_type_handler');

    const $preloader = $('.transition-loader');
    const $full_preloader = $('.full_preloader');
    const $node_relations_outcoming = $('.node_relations_outcoming');
    const $node_relations_incoming = $('.node_relations_incoming');


    let data_source = localStorage.getItem('data_source') || 'flare';
    let node_length = parseInt(localStorage.getItem('node_length'));
    let graph_type = localStorage.getItem('graph_type') || 'hierarchy';

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


    $graph_type_handler.on('change', function () {
        preloaderActivate();
        let new_type = $(this).val();

        if (graph_type == new_type) {
            return;
        }
        graph_type = new_type;
        localStorage.setItem('graph_type', graph_type);
        getDataUrl();
    });

    $data_source_handler.on('change', function () {
        preloaderActivate();
        let new_data_source = $(this).val();

        if (new_data_source == data_source) {
            return;
        }

        data_source = new_data_source;
        localStorage.setItem('data_source', data_source);
        getDataUrl();
    });

    $node_count_input.on('change', function () {
        preloaderActivate();
        node_length = parseInt($(this).val()) || 10;
        localStorage.setItem('node_length', node_length);
        getDataUrl();
    });


    function getDataUrl() {


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
        $graph_type_handler.val(graph_type);

        d3.json(url, function (er, cl) {

            data_loaded = true;

            switch (graph_type) {
                case 'hierarchy':
                    data = cl;
                    graphBuild1();
                    break;

                case 'stratify':

                    if (!checkOneRoot(cl)) {
                        // cl = addOneRoot(cl);
                    }
                    data = convertJsonData(cl);
                    console.log(data);

                    links_data = convertJsonDataLinks(cl);
                    graphBuild2();
                    break;
            }
            preloaderDisable();

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
                getDataUrl();
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

        svg = d3.select("#graph_svg");
        svg.select("#first").remove();

        width = svg.node().getBoundingClientRect().width / 2;
        height = svg.node().getBoundingClientRect().height / 2;

        let zoomable_layer = svg.append('g')
            .attr('id', 'first');
        let graph_layer = zoomable_layer.append('g');


        let node = graph_layer.append("g").selectAll(".node");
        let link = graph_layer.append("g").selectAll(".link");


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
                .classed("link--primary", function (l) {
                    if (l.target === d) {

                        l.source.have_source = true;
                        return true;
                    }
                })
                .classed("link--primary1", function (l) {
                    if (l.source === d) {

                        l.target.have_target = true;
                        return true;
                    }
                })
                // .filter(function (l) {
                //     return l.target === d || l.source === d;
                // })
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
                .classed("link--source", false)
                .classed("link--2", false);

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
                        imports.push({source: map[d.data.name], target: map[i], path: map[d.data.name].path(map[i])});
                    });
                }
            });

            return imports;
        }
    }


    function addOneRoot(p_data) {
        for (let i = 0; i < p_data.length; i++) {
            p_data[i].name = 'flare.' + p_data[i].name;
            for (let j = 0; j < p_data[i].imports.length; j++) {
                p_data[i].imports[j] = 'flare.' + p_data[i].imports[j];
            }
        }
        return p_data;
    }

    function checkOneRoot(p_data) {
        let root_str = p_data[0].name.substr(0, p_data[0].name.indexOf('.'));

        for (let i = 0; i < p_data.length / 2; i++) {
            if (p_data[i].name.indexOf(root_str) > -1) {
                return false;
            }
        }
        return true;
    }

    function convertJsonData(json) {
        let converted = [];
        let temp_ids = [];


        for (let i = 0; i < json.length; i++) {
            let split_names = json[i].name.split('.');

            let split_rubrics;

            if (json[i].rubric !== undefined) {
                split_rubrics = json[i].rubric.split('.');
            }
            else {
                split_rubrics = split_names;
            }

            let temp = '';


            for (let j = 0; j < split_names.length; j++) {
                if (j === 0) {
                    temp += split_names[j];
                }
                else {
                    temp += '.' + split_names[j];
                }

                if (temp_ids.includes(temp)) {
                    continue;
                }
                else {
                    temp_ids.push(temp);
                }

                let value = '';
                let title = '';

                if (j == split_names.length - 1) {

                    if (json[i].size == undefined) {
                        json[i].size = json[i].imports.length;
                    }
                    value = json[i].size + '';

                    title = json[i].title;
                }
                else {

                    title = split_rubrics[j - 1];

                    if (title !== undefined) {
                        title = title.replace(/_/gi, ' ');
                    }

                }

                let array = {
                    id: temp,
                    value: value,
                    title: title
                };
                converted.push(array);
            }
        }

        return converted;
    }


    function convertJsonDataLinks(json) {
        let converted = [];

        for (let i = 0; i < json.length; i++) {

            for (let j = 0; j < json[i].imports.length; j++) {
                const array = {
                    source: json[i].name,
                    target: json[i].imports[j]
                };
                converted.push(array);
            }
        }
        return converted;
    }

    function graphBuild2() {
        let bubble_layer, h, height, line, pack, stratify, svg, vis, w, width, zoom, zoomable_layer;
        let bubbles, enb, index, links, root;


        svg = d3.select("#graph_svg");
        svg.select("#first").remove();

        width = svg.node().getBoundingClientRect().width;

        height = svg.node().getBoundingClientRect().height;

        zoomable_layer = svg.append('g').attr('id', 'first');

        zoom = d3.zoom().scaleExtent([-Infinity, Infinity]).on('zoom', function () {
            return zoomable_layer.attrs({
                transform: d3.event.transform
            });
        });

        svg.call(zoom);


        vis = zoomable_layer.append('g').attrs({
            transform: "translate(" + (width / 2) + "," + (height / 2) + ")"
        });

        stratify = d3.stratify().parentId(function (d) {
            return d.id.substring(0, d.id.lastIndexOf("."));
        });

        w = width - 8;

        h = height - 8;

        pack = d3.pack().size([w, h]).padding(3);

        line = d3.line()
            .curve(d3.curveBundle.beta(beta_value))

            .x(function (d) {
                return d.x;
            })
            .y(function (d) {
                return d.y;
            });

        let betaf = 50;
        let beta = betaf / 100;


        bubble_layer = vis.append('g').attrs({
            transform: "translate(" + (-w / 2) + "," + (-h / 2) + ")"
        });


        root1 = stratify(data);


        root2 = root1.sum(function (d) {
            return d.value;
        });


        root = root2.sort(function (a, b) {
            return d3.descending(a.value, b.value);
        });


        pack(root);
        index = {};
        root.eachBefore(function (d) {
            index[d.data.id] = d;
            return index[d.data.id];
        });

        links_data.forEach(function (d) {
            d.source = index[d.source];
            d.target = index[d.target];
            d.path = d.source.path(d.target);
            return d.path;
        });
        links = bubble_layer.selectAll('.link').data(links_data).enter()
            .append('path')
            .attr('class', 'link')
            .attr('d', d => line(d.source.path(d.target)));

        bubbles = bubble_layer.selectAll('.bubble').data(root.descendants());

        enb = bubbles.enter().append('circle').attrs({
            "class": 'bubble',
            cx: function (d) {
                return d.x;
            },
            cy: function (d) {
                return d.y;
            },
            r: function (d) {
                return d.r;
            }
        }).on("mouseover", mouseovered)
            .on("mouseout", mouseouted);


        function mouseovered(d) {
            // showNodeRelations(d.data);

            enb
                .each(function (n) {
                    n.have_target = n.have_source = false;
                });

            links
                .classed("link--target", function (l) {

                    if (l.target === d) {
                        l.source.have_source = true;
                        return true;
                    }
                })
                .classed("link--source", function (l) {
                    if (l.source === d) {
                        l.target.have_target = true;
                        return true;
                    }
                })
                // .filter(function (l) {
                //     return l.target === d || l.source === d;
                // })
                .each(function () {
                    this.parentNode.appendChild(this);
                });


            enb
                .classed("node--target", function (n) {
                    return n.have_target;
                })
                .classed("node--source", function (n) {
                    return n.have_source;
                });
        }

        function mouseouted(d) {


            links
                .classed("link--target", false)
                .classed("link--source", false);

            enb
                .classed("node--target", false)
                .classed("node--source", false);
        }

        enb.append('title').text(function (d) {
            return d.data.title || d.id.substring(d.id.lastIndexOf(".") + 1).split(/(?=[A-Z][^A-Z])/g).join(' ');
        });


        return links.enter().append('path').attrs({
            "class": 'link',
            d: function (d) {
                return line(d.path);
            }
        });
    }


});
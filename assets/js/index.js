$(document).ready(function () {

    const $data_source_handler = $('#data_source_handler');
    const $node_count_container = $('#node_count_container');
    const $node_count_input = $('#node_count_input');
    const $graph_type_handler = $('#graph_type_handler');

    const $preloader = $('.transition-loader');
    const $full_preloader = $('.full_preloader');
    const $node_relations_outcoming = $('.node_relations_outcoming');
    const $node_relations_incoming = $('.node_relations_incoming');
    const $restriction_filter_input_container = $('#restriction_filter_input_container');
    const $modal_info__airport = $('#modal_info__airport');
    const $modal_info__publication = $('#modal_info__publication');
    const $reload_ajax_data_handler = $('.reload_ajax_data_handler');
    const $reset_params_handler = $('.reset_params_handler');


    let data_source = localStorage.getItem('data_source') || 'flare';
    let node_length = parseInt(localStorage.getItem('node_length')) || 10;
    if (node_length < 1 || node_length > 5000) {
        node_length = 100;
        localStorage.setItem('node_length', node_length);
    }

    let graph_type = localStorage.getItem('graph_type') || 'hierarchy';

    let url = '';

    let data = {};
    let global_cl = {};
    let global_cl_not_filtered = {};

    let k = 1;
    let x, y;
    let beta_value = parseFloat(localStorage.getItem('beta_value') || 0);
    let link_opacity = parseFloat(localStorage.getItem('link_opacity') || 0.5);
    let data_loaded = false;

    DynamicLinksStyle();
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
        $node_count_input.attr('disabled', true);
        preloaderActivate();
        node_length = parseInt($(this).val());
        if (node_length < 1) {
            node_length = 10;
        }

        localStorage.setItem('node_length', node_length);
        getDataUrl();
    });


    function getDataUrl() {
        $node_count_container.hide();
        $restriction_filter_input_container.hide();

        switch (data_source) {
            case 'flare':
                url = '/flare.json';


                break;
            case 'elibrary':
                $node_count_container.show();
                $node_count_input.val(node_length);

                url = '/ajax.php?source=elibrary&l=' + node_length;
                break;
            case 'generate':
                $node_count_container.show();
                $node_count_input.val(node_length);

                url = '/ajax.php?source=generate&l=' + node_length;
                break;
            case 'covid':
                $node_count_container.show();
                $node_count_input.val(node_length);
                $restriction_filter_input_container.show();
                url = '/ajax.php?source=covid&l=' + node_length;
                break;
        }


        $data_source_handler.val(data_source);
        $graph_type_handler.val(graph_type);

        d3.json(url, function (er, cl) {
            try {
                global_cl_not_filtered = JSON.parse(JSON.stringify(cl));
                updateData(cl);
            } catch (e) {
                alert("Не удалось загрузить данные!");
                console.log(e);
                preloaderDisable();
            }
        });

    }

    function reDraw() {
        switch (graph_type) {
            case 'hierarchy':
                graphBuild1();
                break;

            case 'stratify':
                graphBuild2(links_data);
                break;
        }
    }

    /**
     * @return {boolean}
     */
    function updateData(cl) {
        global_cl = cl;
        data_loaded = true;


        if (!global_cl.length) {
            alert('Нет данных!');
            preloaderDisable();

            return false;
        }

        switch (graph_type) {
            case 'hierarchy':
                data = global_cl;
                graphBuild1();
                break;

            case 'stratify':
                data = convertJsonData(global_cl);
                links_data = convertJsonDataLinks(global_cl);
                graphBuild2(links_data);
                break;
        }
        preloaderDisable();
        return true;
    }

    function menuSetup() {
        BetaSlider();

        $('.nice-select').niceSelect();


        $("#restriction_filter_input").bsMultiSelect()
            .on('change', function () {
                let key = $(this).data('filter');

                let filter = {};

                filter[key] = $(this).val();

                global_cl = FilterGlobalData(global_cl_not_filtered, filter);
                updateData(global_cl);

            });

        $reload_ajax_data_handler.on('click', function () {
            $(this).attr('disabled', true);
            $(this).find('.not_loading').addClass('d-none');
            $(this).find('.loading').removeClass('d-none');

            getDataUrl();
        });

        $reset_params_handler.on('click', function () {
            localStorage.setItem('data_source', 'flare');
            localStorage.setItem('node_length', 100);
            localStorage.setItem('graph_type', 'hierarchy');
            localStorage.setItem('beta_value', 0.5);
            window.location.reload();
        });

    }


    function BetaSlider() {
        let sliderSimple = d3
            .sliderBottom()
            .min(0)
            .max(1)
            .width(100)
            .ticks(10)
            .default(beta_value)
            .on('onchange', val => {
                d3.select('#value-simple').text(d3.format('.2')(val));
                beta_value = val;
                localStorage.setItem('beta_value', beta_value);
                reDraw();
            });

        let gSimple = d3
            .select('#slider-simple')
            .append('svg')
            .append('g')
            .attr('transform', 'translate(30,30)');


        gSimple.call(sliderSimple);

        d3.select('#value-simple').text(sliderSimple.value());


        let LinkOpacitySlider = d3
            .sliderBottom()
            .min(0)
            .max(1)
            .width(100)
            .ticks(10)
            .default(link_opacity)
            .on('onchange', val => {
                d3.select('#link_opacity_value').text(d3.format('.2')(val));
                link_opacity = val;
                localStorage.setItem('link_opacity', link_opacity);
                DynamicLinksStyle();
            });

        let gSimple1 = d3
            .select('#link_opacity_slider')
            .append('svg')
            .append('g')
            .attr('transform', 'translate(30,30)');


        gSimple1.call(LinkOpacitySlider);

        d3.select('#link_opacity_value').text(LinkOpacitySlider.value());
    }

    function DynamicLinksStyle() {
        const tag = document.getElementById('link_dynamic_style');
        tag.innerHTML = '.link { stroke-opacity: ' + link_opacity + '; }';
    }

    function preloaderActivate() {
        $preloader.fadeIn();
        $full_preloader.show();
    }

    function preloaderDisable() {
        $preloader.fadeOut();
        $full_preloader.hide();
        $node_count_input.attr('disabled', false);
        $reload_ajax_data_handler.attr('disabled', false);
        $reload_ajax_data_handler.find('.not_loading').removeClass('d-none');
        $reload_ajax_data_handler.find('.loading').addClass('d-none');

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


        let diameter = node_length * 5 + 900;
        let radius = diameter / 2;
        let innerRadius = radius - 120;

        let cluster = d3.cluster()
            .size([360, innerRadius]);

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
            .on("mouseout", mouseouted)
            .on('click', function (d, i, nodes) {
                // const node = d3.select(nodes[i]);
                // node.classed('node--selected', !node.classed('node--selected'));

                switch (data_source) {
                    case 'covid':
                    case 'elibrary':
                        getAjaxModalInfo(d.data.id);
                        break;


                }

            });

        node.append('title').text(function (d) {
            if (d.data.title1 !== undefined && d.data.title1.length) {
                return d.data.title1;
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
                        } else {
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
                        } else {
                            incoming.push(p_data.name);
                        }
                        break;
                    }
                }
            }

            if (outcoming.length) {
                // $node_relations_outcoming.html(outcoming.join('<br>'));
                // $node_relations_outcoming.show();
            }
            if (incoming.length) {
                // $node_relations_incoming.html(outcoming.join('<br>'));
                // $node_relations_incoming.show();
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

                if (d.data.imports !== undefined) {
                    d.data.imports.forEach(function (i) {

                        try {
                            imports.push({
                                source: map[d.data.name],
                                target: map[i],
                                path: map[d.data.name].path(map[i])
                            });
                        } catch (e) {
                            return true;
                        }

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
            } else {
                split_rubrics = split_names;
            }

            let temp = '';


            for (let j = 0; j < split_names.length; j++) {
                if (j === 0) {
                    temp += split_names[j];
                } else {
                    temp += '.' + split_names[j];
                }

                if (temp_ids.includes(temp)) {
                    continue;
                } else {
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
                } else {

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


        console.log(converted);

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

    function graphBuild2(p_links_data) {
        let bubble_layer, h, height, line, pack, stratify, svg, vis, w, width, zoom, zoomable_layer;
        let bubbles, enb, index, links, root;


        p_links_data = convertJsonDataLinks(global_cl);
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

        let loc_links_data = p_links_data.slice();


        loc_links_data.forEach(function (d) {
            try {
                d.source = index[d.source];
                d.target = index[d.target];
                d.path = d.source.path(d.target);
            } catch (e) {
                return false;
            }
            return d.path;
        });
        links = bubble_layer.selectAll('.link').data(loc_links_data).enter()
            .append('path')
            .attr('class', 'link')
            .attr('d', function (d) {
                try {
                    let path = d.source.path(d.target);
                    return line(path);
                } catch (e) {
                    return null;
                }

            });

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


    function getAjaxModalInfo(id) {

        $.ajax({
            method: "get",
            url: "ajax.php",
            data: {
                source: data_source,
                type: 'single',
                id: id
            },
            dataType: 'json',
            success: function (response) {
                switch (data_source) {
                    case 'covid':
                        setAirportModal(response);
                        break;
                    case 'elibrary':
                        console.log(response);
                        setPublicationModal(response);
                        break;
                }


            },
            error: function () {
                alert('Не удалось получить информацию!');
            }
        });
    }

    function setPublicationModal(info) {
        let keys = Object.keys(info);
        for (let i = 0; i < keys.length; i++) {
            if (info[keys[i]] == null) {
                info[keys[i]] = '-';
            }
            $modal_info__publication.find('[data-type="' + keys[i] + '"]').text(info[keys[i]]);
        }

        $modal_info__publication.find('.modal-title').text(info['title']);


        $list = $modal_info__publication.find('.publication_authors_list');

        for (let i = 0; i < info['authors'].length; i++) {
            let keys = Object.keys(info['authors'][i]);

            let tr_element = document.createElement('tr');
            for (let j = 0; j < keys.length; j++) {
                $(tr_element).append('<td>' + info['authors'][i][keys[j]] + "</td>");
            }
            $list.append($(tr_element));
        }

        $list = $modal_info__publication.find('.publication_relations_list');

        try {
            for (let i = 0; i < info['relations'].length; i++) {
                let keys = Object.keys(info['relations'][i]);

                let tr_element = document.createElement('tr');
                for (let j = 0; j < keys.length; j++) {
                    $(tr_element).append('<td>' + info['relations'][i][keys[j]] + "</td>");
                }
                $list.append($(tr_element));
            }
        }
        catch (e) {

        }



        $modal_info__publication.modal('show');
    }


    function setAirportModal(info) {

        let keys = Object.keys(info);


        for (let i = 0; i < keys.length; i++) {
            if (info[keys[i]] == null) {
                info[keys[i]] = '-';
            }

            if (keys[i] == 'destinations') {
                continue;
            }

            if (keys[i] == 'restriction_type') {
                info[keys[i]] = resriction_types[info[keys[i]]];
            }
            $modal_info__airport.find('.' + keys[i]).text(info[keys[i]]);
        }

        $modal_info__airport.find('.modal-title').text(info['airport_name']);


        $airport_destinations_list = $modal_info__airport.find('.airport_destinations_list');

        for (let i = 0; i < info['destinations'].length; i++) {
            let keys = Object.keys(info['destinations'][i]);

            let tr_element = document.createElement('tr');
            for (let j = 0; j < keys.length; j++) {

                $(tr_element).append('<td>' + info['destinations'][i][keys[j]] + "</td>");
            }
            $airport_destinations_list.append($(tr_element));
        }


        $modal_info__airport.modal('show');
    }


});


function FilterGlobalData(p_data_array, filter) {

    let data_array = JSON.parse(JSON.stringify(p_data_array));

    let keys = Object.keys(filter);

    if (!keys.length) {
        return data_array;
    }
    let bad_array = [];

    for (let i = 0; i < keys.length; i++) {
        for (let j = 0; j < data_array.length; j++) {

            if (filter[keys[i]].indexOf(data_array[j][keys[i]]) > -1) {
                bad_array.push(data_array[j]['name']);
            }
        }
    }

    for (let i = 0; i < data_array.length; i++) {
        data_array[i]['imports'] = data_array[i]['imports'].filter(x => bad_array.indexOf(x) == -1);
    }

    return data_array;
}
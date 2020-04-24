let data, links_data;

const beta_value = 1;
let json;

d3.json('/ajax.php?l=50', function (p_data) {
    if (!checkOneRoot(p_data)) {
        p_data = addOneRoot(p_data);
    }

    data = convertJsonData(p_data);
    links_data = convertJsonDataLinks(p_data);

    graphBuild2();
});

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
        let split_rubrics = json[i].rubric.split('.');

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
                    json[i].size = json[i].imports.length * 100;
                }
                value = json[i].size + '';

                title = json[i].title;
            }
            else {
                title = split_rubrics[j - 1];
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


    svg = d3.select('svg');

    width = svg.node().getBoundingClientRect().width;

    height = svg.node().getBoundingClientRect().height;

    zoomable_layer = svg.append('g');

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
    });
    enb.append('title').text(function (d) {
        return d.data.title || d.id.substring(d.id.lastIndexOf(".") + 1).split(/(?=[A-Z][^A-Z])/g).join(' ');
    });
    links = bubble_layer.selectAll('.link').data(links_data);

    svg.on('click', function (d) {
        betaf += 10;
        beta = betaf / 100;
        links.each(function (d1) {
            d1.classed("link--target", false)
                .classed("link--source", true)
        })

        // bubble_layer.selectAll('.link').enter().curve(d3.curveBundle.beta(beta));
    });


    return links.enter().append('path').attrs({
        "class": 'link',
        d: function (d) {
            return line(d.path);
        }
    });
}


function randomInteger(min, max) {
    // случайное число от min до (max+1)
    let rand = min + Math.random() * (max + 1 - min);
    return Math.floor(rand);
}
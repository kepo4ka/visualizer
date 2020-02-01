// const primary_field = 'id';
// const references_field = 'references';
// const title_field = 'title';

const primary_field = 'name';
const references_field = 'imports';
const title_field = 'name';

let url = '/ajax.php?';


let svg = d3.select("svg"),
    width = +svg.attr("width"),
    height = +svg.attr("height");


let diameter = 10000,
    radius = diameter / 2,
    innerRadius = radius - 120;

let cluster = d3.cluster()
    .size([360, innerRadius]);

let line = d3.radialLine()
    .curve(d3.curveBundle.beta(1))
    .radius(function (d) {
        return d.y;
    })
    .angle(function (d) {
        return d.x / 180 * Math.PI;
    });


let g = svg.append("g");




svg.append("rect")
    .attr("fill", "none")
    .attr("pointer-events", "all")
    .attr("width", width)
    .attr("height", height)
    .call(d3.zoom()
        .scaleExtent([0, Infinity])
        .on("zoom", zoom));


// first_zoom();
// function first_zoom() {
//     var t = d3.zoomIdentity.translate(800, 400).scale(0.04);
//     g.attr("transform", t);
// }

let link = g.selectAll(".link"),
    node = g.selectAll(".node");


let length = randomInteger(50, 500);

let data_url = "ajax.php?l=" + length;
data_url = 'data_examples/flare.json';

d3.json(data_url, function (error, classes) {
    if (error) throw error;

    let
        root = packageHierarchy(classes)
            .sum(function (d) {
                return d.size;
            });

    cluster(root);

    link = link
        .data(packageImports(root.leaves()))
        .enter().append("path")
        .each(function (d) {
            d.source = d[0], d.target = d[d.length - 1];
        })
        .attr("class", "link")
        .attr("stroke", "red")
        .attr("d", line);

    node = node
        .data(root.leaves())
        .enter().append("text")
        .attr("class", "node")
        .attr("dy", "0.31em")
        .attr("transform", function (d) {
            return "rotate(" + (d.x - 90) + ")translate(" + (d.y + 8) + ",0)" + (d.x < 180 ? "" : "rotate(180)");
        })
        .attr("text-anchor", function (d) {
            return d.x < 180 ? "start" : "end";
        })
        .text(function (d) {
            return d.data[title_field];
        });
});


function randomInteger(min, max) {
    // случайное число от min до (max+1)
    let rand = min + Math.random() * (max + 1 - min);
    return Math.floor(rand);
}

function zoom() {
    console.log(d3.event.transform);
    g.attr("transform", d3.event.transform);
}

// Lazily construct the package hierarchy from class names.
function packageHierarchy(classes) {
    let
        map = {};


    function find(name, data) {


        let node = map[name], i;
        if (!node) {
            node = map[name] = data || {
                name:
                name, children: []
            };
            if (name.length) {
                node.parent = find(name.substring(0, i = name.lastIndexOf(".")));
                node.parent.children.push(node);
                node.key = name.substring(i + 1);
            }
        }
        return node;
    }

    classes.forEach(function (d) {
        find(d[primary_field], d);
    });

    return d3.hierarchy(map[""]);
}

// Return a list of imports for the given array of nodes.
function packageImports(nodes) {
    let
        map = {},
        imports = [];

    // Compute a map from name to node.
    nodes.forEach(function (d) {
        map[d.data[primary_field]] = d;
    });

    // For each import, construct a link from the source to target node.
    nodes.forEach(function (d) {


        if (d.data[references_field]) {
            d.data[references_field].forEach(function (i) {
                try {
                    imports.push(map[d.data[primary_field]].path(map[i]));
                }
                catch (e) {
                    console.warn(i);
                }
            });
        }
    });

    return imports;
}

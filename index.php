<?php

require_once('init.php');


$length = 100;
$beta = 1;

if (!empty($_GET['l'])) {
    $length = (int)$_GET['l'];
}

if (!empty($_GET['b'])) {
    $beta = (float)$_GET['b'];
}

// $users = \JsonMachine\JsonMachine::fromFile('dblp_papers_v11.json');


//splitDataSet($length);
//exit;


?>

<!DOCTYPE html>

<head>
    <script src="assets/lib/d3/d3.v4.js"></script>

</head>

<style>

    .node {
        font: 10px sans -serif;
    }

    .link {
        stroke: steelblue;
        stroke -opacity: 0.5;
        fill: none;
        pointer -events: none;
    }

    .container {
        padding: 10px;
        border: 1px solid black;
    }


</style>


<body>


<div class="container">
    <svg width="1980" height="1080">

    </svg>
</div>


<script>

    const primary_field = 'id';
    const references_field = 'references';
    const title_field = 'title';

    let svg = d3.select("svg"),
        width = +svg.attr("width"),
        height = +svg.attr("height");


    let diameter = 10000,
        radius = diameter / 2,
        innerRadius = radius - 120;

    let cluster = d3.cluster()
        .size([360, innerRadius]);

    let line = d3.radialLine()
        .curve(d3.curveBundle.beta(<?=$beta?>))
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
            .scaleExtent([0.0001, 100])
            .on("zoom", zoom));

    function zoom() {
        console.log(d3.event.transform);
        g.attr("transform", d3.event.transform);
    }

    // first_zoom();
    // function first_zoom() {
    //     var t = d3.zoomIdentity.translate(800, 400).scale(0.04);
    //     g.attr("transform", t);
    // }

    let link = g.selectAll(".link"),
        node = g.selectAll(".node");

    d3.json("ajax.php?l=<?=$length?>", function (error, classes) {
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

</script>
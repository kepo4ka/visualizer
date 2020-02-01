import {require} from "https://unpkg.com/d3-require@1?module";

export default async function() {

  const d3 = await require("d3@5");

  const width = 640;

  const radius = width / 2;

  const line = d3.radialLine()
      .curve(d3.curveBundle.beta(0.85))
      .radius(d => d.y)
      .angle(d => d.x);

  const tree = d3.cluster().size([2 * Math.PI, radius - 100]);

  const data = await loadData();

  const root = tree(d3.hierarchy(data));

  const map = new Map(root.leaves().map(d => [d.data.id, d]));

  const svg = d3.create("svg")
      .attr("width", width)
      .attr("height", width)
      .attr("viewBox", `${-width / 2} ${-width / 2} ${width} ${width - 40}`)
      .style("max-width", "100%")
      .style("height", "auto")
      .style("display", "block")
      .style("margin", "auto")
      .style("font", "10px sans-serif");

  svg.append("g")
      .attr("fill", "none")
      .attr("stroke", "steelblue")
      .attr("stroke-opacity", 0.5)
    .selectAll("path")
    .data(d3.merge(root.leaves().map(d => d.data.targets.map(i => d.path(map.get(i))))))
    .enter().append("path")
      .style("mix-blend-mode", "multiply")
      .attr("d", line);

  svg.append("g")
    .selectAll("text")
    .data(root.leaves())
    .enter().append("text")
      .attr("transform", d => `
        rotate(${d.x * 180 / Math.PI - 90})
        translate(${d.y},0)${d.x >= Math.PI ? `
        rotate(180)` : ""}
      `)
      .attr("dy", "0.31em")
      .attr("x", d => d.x < Math.PI ? 3 : -3)
      .attr("text-anchor", d => d.x < Math.PI ? "start" : "end")
      .text(d => d.data.id);

  return svg.node();
}

async function loadData() {
	let test = await require("@observablehq/miserables@0.0");
	console.log(test);
  const {nodes, links} = await require("@observablehq/miserables@0.0");
  const map = new Map;

  for (const node of nodes) {
    let group = map.get(node.group);
    if (!group) map.set(node.group, group = {name: node.group, children: []});
    group.children.push(node);
    node.targets = [];
  }

  for (const {source, target} of links) {
    source.targets.push(target.id);
  }

  return {name: "miserables", children: [...map.values()]};
}

var InteractionGraph = function(placeholder_id, graph_size, interaction_data) {
	var vis = d3.select('#'+placeholder_id)
	.append("svg")
	.attr("width", graph_size)
	.attr("height", graph_size)
	.style("display", "block")
	.style("margin", "0 auto");

	var force = d3.layout.force()
	.charge(-800)
	.linkDistance(150)
	.size([graph_size, graph_size]);

	var graph = {nodes:[], links:[]};

	graph.nodes.push({
		index: 0,
		name: interaction_data.user.user_name,
		avatar: interaction_data.user.avatar
	});

	var max_count = 0;
	var temp_index = 1;

	for (var i = 0; i < interaction_data.hashtags.length; i++) {
		graph.nodes.push({
			index: temp_index++,
			name: interaction_data.hashtags[i].hashtag,
			avatar: null,
			url: interaction_data.hashtags[i].url,
			value: interaction_data.hashtags[i].count,
			related_mentions: interaction_data.hashtags[i].related_mentions
		});

		max_count = Math.max(max_count,interaction_data.hashtags[i].count);
	}

	for (var i = 0; i < interaction_data.mentions.length; i++) {
		graph.nodes.push({
			index: temp_index++,
			name: interaction_data.mentions[i].mention,
			avatar: (interaction_data.mentions[i].user != null) ? interaction_data.mentions[i].user.avatar : null,
			url: (interaction_data.mentions[i].user != null) ? interaction_data.mentions[i].user.url : null,
			value: interaction_data.mentions[i].count,
			related_mentions: []
		});

		max_count = Math.max(max_count,interaction_data.mentions[i].count);
	}

	function getIndexOfMention(nodes, mention) {
		for (var i = 1; i < nodes.length; i++) {
			if (nodes[i].name == mention) {
				return nodes[i].index;
			}
		}
	}

	function getCountOfRelatedMentions(related_mentions, mention) {
		var count = 0;
		for (var i = 0; i < related_mentions.length; i++) {
			if (related_mentions[i] == mention) {
				count++;
			}
		}
		return count;
	}

	for (var i = 1; i < graph.nodes.length; i++) {
		graph.links.push({
			group: 0,
			source: 0,
			target: i,
			value: graph.nodes[i].value
		});
		for (var j = 0; j < graph.nodes[i].related_mentions.length; j++) {
			if (getIndexOfMention(graph.nodes,graph.nodes[i].related_mentions[j]) != 0) {
				graph.links.push({
					group: 1,
					source: i,
					target: getIndexOfMention(graph.nodes,graph.nodes[i].related_mentions[j]),
					value: getCountOfRelatedMentions(graph.nodes[i].related_mentions,graph.nodes[i].related_mentions[j])
				});
			}
		}
	}

	force.nodes(graph.nodes).links(graph.links).start();

	var link = vis.selectAll(".link")
	.data(graph.links)
	.enter()
	.append("line")
	.style("stroke", "#999")
	.style("stroke-opacity", function(d) { return (d.group == 0 ? 0.6 : 0.3); })
	.style("stroke-width", function(d) { return (d.value/max_count)*10; });

	var node = vis.selectAll(".node")
	.data(graph.nodes)
	.enter()
	.append("g")
	.call(force.drag);

	node.append("circle")
	.attr("r", 20)
	.style("stroke", "#000")
	.style("stroke-width", "1px")
	.style("fill", "#ff8000");

	node.append("clipPath")
	.attr("id", function(d) { return "avatar_clip_"+placeholder_id+"_"+d.index; })
	.append("circle")
	.attr("transform","translate(20,20)")
	.attr("r", 20);

	node.append("image")
	.attr("xlink:href", function(d) { return d.avatar; })
	.attr("width", 40)
	.attr("height", 40)
	.attr("transform","translate(-20,-20)")
	.attr("clip-path", function(d) { return "url(#avatar_clip_"+placeholder_id+"_"+d.index+")"; });

	node.append("text")
	.text(function(d) { return d.name; })
	.attr("transform","translate(22,0)");

	force.on("tick", function() {
		link.attr("x1", function(d) { return d.source.x; })
		.attr("y1", function(d) { return d.source.y; })
		.attr("x2", function(d) { return d.target.x; })
		.attr("y2", function(d) { return d.target.y; });

		node.attr("transform", function(d) { return "translate("+d.x+","+d.y+")"; });
	});
};
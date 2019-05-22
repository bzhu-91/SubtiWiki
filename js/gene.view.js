function ucfirst (str) {
  //  discuss at: http://locutus.io/php/ucfirst/
  // original by: Kevin van Zonneveld (http://kvz.io)
  // bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
  // improved by: Brett Zamir (http://brett-zamir.me)
  //   example 1: ucfirst('kevin van zonneveld')
  //   returns 1: 'Kevin van zonneveld'
  str += ''
  var f = str.charAt(0)
    .toUpperCase()
  return f + str.substr(1)
}

$(document).ready(function(){
	// context browser
	(function(){
		var container = $("#context-browser-wrapper");
		var browser = new ContextBrowser(container);
		$.ajax({
			url: "genome/context?gene=" + geneId + "&span=30000",
			dataType:"json",
			success: function (data) {
				browser.setData(data, genomeLength);
				browser.on("click", function(ev){
					if (ev.currentViews) {
						var view = ev.currentViews[0];
						if (view.id && view.type == "gene") window.open("gene?id=" + view.id);
					}
				});
				browser.diagram.focus(geneId);
			},
			error: function (data) {
				browser.setMessage(data.message);
			}
		});
	})();
	// interactions
	(function(){
		var view = $('<h3>Interactions</h3><div style="margin-top: 10px; width: 100%;"></div><div></div><br><br>');
		$.ajax({
			url: "interaction?radius=1&gene=" + geneId,
			dataType:"json",
			success: function (data) {
				$("#interaction-diagram").append(view);
				for (var i = 0; i < data.nodes.length; i++) {
					data.nodes[i].label = ucfirst(data.nodes[i].title);
				}
				for (var i = 0; i < data.edges.length; i++) {
					if (data.edges[i].description) data.edges[i].color = "green";
				}
				var options = {
					edge:{
						strokeWidth: 4
					}, 
					width:"100%", 
					height:"auto",
					node:{
						color:"#2196F3"
					}
				};
				var dataSet = new Interactome.dataSet(data.nodes, data.edges);
				var diagram = new Interactome.diagram(view[1], dataSet, options);
				diagram.on("mouseover", function(ev){
					if (ev.currentNode) {
						ev.currentNode.highlight();
					}
					if (ev.currentEdge) {
						ev.currentEdge.highlight();
					}
				});
				diagram.on("mouseout", function(ev){
					if (ev.currentNode) {
						ev.currentNode.unhighlight();
						if (edge) {
							edge.highlight();
						}
					}
					if (ev.currentEdge) {
						ev.currentEdge.unhighlight();
						if(edge) {
							edge.highlight();
						}
					}
				});
				var edge;
				diagram.on("click", function(ev){
					$('#interactions-description').hide();
					if (edge) {
						edge.unhighlight();
					}
					if (ev.currentNode) {
						window.open("gene?id=" + ev.currentNode.id);
					}
					if (ev.currentEdge) {
						if (!edge || edge != ev.currentEdge) {
							if (edge) edge.unhighlight();
							edge = ev.currentEdge;
							edge.highlight();
							if (edge.description) {
								desc = edge.description;
								if (desc.length == 1 && desc[0].match(/^\[pubmed\|([\d, ]+?)\]$/gi)) {
									var regex = /^\[pubmed\|([\d, ]+?)\]$/gi;
									var matches = regex.exec(desc.join(",").trim());
									window.open("https://www.ncbi.nlm.nih.gov/pubmed/" + matches[1]);
								} else {
									view[2].innerHTML = "<p>" + edge.from.label + "-" + edge.to.label + "</p>" + parseMarkup(desc.join('<br/>'));
								}
							}
						}
					}
				})
			}
		});
	})();
	// categories
	(function(){
		// categories
		$.ajax({
			url: "category?gene=" + geneId,
			headers: {Accept: "text/html_partial"},
			success: function (data) {
				var view = $(parseMarkup(data));
				for (var i = 0; i < view.length; i++) {
					view[i] = $("<li class='m_value'>" + view[i].innerHTML + "</li>");
					$("#categories-container").append(view[i]);
				}
			},
			error: function () {
				$("#categories-wrapper").hide();
			}
		});
	})();
	// regulations and regulons
	(function(){
		var view = $('<h3>Regulations</h3><div style="margin-top: 15px; width: 100%;"></div style="margin-top: 15px; width: 100%;"><div style="margin-top: 15px; width: 100%;"></div><div></div>');
		$.ajax({
			url: "regulation?radius=1&sigA=true&gene=" + geneId,
			dataType:"json",
			success: function (data) {
				// regulation diagrams
				$("#regulation-diagram").append(view);
				var _nodes = {};
				var edges = data.edges.filter(function(e){
					if(e.to == geneId){
						_nodes[e.from] = true;
						_nodes[e.to] = true;
						return true;
					}
				});
				var edges2 = data.edges.filter(function(e){
					return e.from == geneId;
				});
				var count = edges2.length;
				var nodes = data.nodes.filter(function(n){
					return n.id in _nodes;
				});
				// regulons
				var regulons = nodes.filter(function(n){
					return n.id !== geneId;
				}).map(function(n){
					if (n.id.match(/^[a-f0-9]{40}$/i)) {
						// is a gene
						return "<a href='regulon?id=protein:"+n.id+"' target='_blank'>" + ucfirst(n.title) + " regulon</a>";
					} else {
						return "<a href='regulon?id=riboswitch:"+n.id+"' target='_blank'>" + ucfirst(n.title) + "</a>";
					}
				});
				if (count) {
					regulons.push("<a href='regulon?id=protein:"+geneId+"'>" + ucfirst(geneTitle) + " regulon</a>");
				}
				$("#regulon-container").html(regulons.join(", "));

				// diagrams
				for (var i = 0; i < nodes.length; i++) {
					nodes[i].label = ucfirst(nodes[i].title);
					nodes[i].href = "gene?id=" + nodes[i].id;
				}
				for (var i = 0; i < edges.length; i++) {
					if (edges[i].description) {
						edges[i].textColor = "blue";
					}
					edges[i].label = edges[i].mode;
					var tmp = edges[i].from;
					edges[i].from = edges[i].to;
					edges[i].to = tmp;
				}
				var dataSet1 = new SimpleTree.dataSet(nodes, edges);
				var diagram1 = new SimpleTree.diagram(view[1], dataSet1, {direction: "RL", width: "100%"});
				diagram1.on("click", function(ev){
					if (ev.currentNode) {
						if(ev.currentNode.href) window.open(ev.currentNode.href);
					}
					if (ev.currentEdge) {
						var from = dataSet1.getNodeById(ev.currentEdge.from)
						var to = dataSet1.getNodeById(ev.currentEdge.to)
						if (ev.currentEdge.description) {
							if (ev.currentEdge.description.trim().match(/^\[pubmed\|([\d, ]+?)\]$/gi)) {
								var regex = /^\[pubmed\|([\d, ]+?)\]/gi;
								var matches = regex.exec(ev.currentEdge.description.trim());
								window.open("https://www.ncbi.nlm.nih.gov/pubmed/" + matches[1]);
							} else $(view[3]).html(
								"<p>" + to.label + ":" + ev.currentEdge.label + ", " + parseMarkup(ev.currentEdge.description) + "</p>"
							);
						}
					}
				})
				if (count) {
					var dataSet2 = new SimpleTree.dataSet([
						{id:geneId, label: ucfirst(geneTitle)}, 
						{href: "regulation?gene=" + geneId, label: count + " genes", id: "__links"}
					], [{
						to:geneId, from: "__links", label: "regulates"
					}]);

					var diagram2 = new SimpleTree.diagram(view[2], dataSet2, {direction: "RL", width: "100%"});

					diagram2.on("click", function(ev){
						if (ev.currentNode) {
							if(ev.currentNode.href) window.open(ev.currentNode.href);
						}
					})
				} else {
					$("#mainnav").find("li:contains('Regulon')").remove();
					$("#floatTop nav").find("li:contains('Regulon')").remove();
				}
			},
			error: function () {
				$("#regulon-wrapper").hide();
				$("#mainnav").find("li:contains('Regulon')").remove();
				$("#floatTop nav").find("li:contains('Regulon')").remove();
			}
		});
	})();

	$(".m_object").each(function(){
		if (this.innerHTML == "") {
			$(this).prev().remove();
		}
	});
})
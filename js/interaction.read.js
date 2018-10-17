function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
    return null;
}

var GroupSelect = GroupSelect || function (id, data) {
	this.view = document.getElementById(id);
	this.data = data;
	this.populate();
}

GroupSelect.prototype.populate = function () {
	var self = this;
	self.view.innerHTML = "<option value='-1'>Please select</option>";
	
	for (var group in self.data) {
		var optgroup = document.createElement("optgroup");
		optgroup.label = group;
		self.view.appendChild(optgroup);
		for (var id in self.data[group]) {
			var option = document.createElement("option");
			option.value = self.data[group][id].id;
			option.innerHTML = self.data[group][id].title;
			self.view.appendChild(option);
		}
	}
}

// main
$(document).ready(function(){
	$.getScript($("base").attr("href") + "js/libs/colorSpectrum.js", function(){
		if (getQueryVariable("gene")) {
			browser = new InteractionBrowser(getQueryVariable("gene"));
		} else {
			// show full graph
			browser = new InteractionBrowser();
			// $("#display-block, #loading, #control-block").hide();
		}
		lightbox = new SomeLightBox({
			width: "400px",
			height: "auto",
			animation: false
		});
	});
});

$(document).on("click", "#collapsed", function() {
	$("#full").show();
	$(this).hide();
});

$(document).on("click", "#control-collapse", function() {
	$("#full").hide();
	$("#collapsed").show();
});

$(document).on("submit", "#search", function(ev){
	ev.stopPropagation();
	ev.preventDefault();

	var geneName = this.geneName.value.trim();

	if (geneName.length >= 2) {
		ajax.get({
			url:"gene?keyword="+geneName+"&mode=title",
			headers: {Accept: "application/json"}
		}).done(function(state, data, error, xhr){
			if (error) {
				SomeLightBox.error("Connection to server lost");
			} else if (state == 200) {
				if (data.length > 1) {
					SomeLightBox.error("Gene name " + geneName + " is ambigious");
				} else {
					window.location = $("base").attr("href") + "interaction?gene=" + data[0].id;
				}
			} else {
				SomeLightBox.error("Gene " + geneName + " not found");
			}
		})
	}
	return false;
});

$(document).on("click", "#increase-radius", function () {
	if (browser.radius < 4) {
		browser.setRadius(browser.radius + 1);
	}
});

$(document).on("click", "#decrease-radius", function () {
	if (browser.radius > 0) {
		browser.setRadius(browser.radius - 1);
	}
});

$(document).on("click", "#increase-spacing", function () {
	if (browser.spacing < 29) {
		browser.setSpacing(browser.spacing + 1);
	}
});

$(document).on("click", "#decrease-spacing", function () {
	if (browser.spacing > 0) {
		browser.setSpacing(browser.spacing - 1);
	}
});

$(document).on("submit", "#highlight", function(ev){
	ev.stopPropagation();
	ev.preventDefault();

	var geneName = this.geneName.value.trim();

	if (geneName.length >= 2) {
		ajax.get({
			url:"gene?keyword="+geneName+"&mode=title",
			headers: {Accept: "application/json"}
		}).done(function(state, data, error, xhr){
			if (error) {
				SomeLightBox.error("Connection to server lost");
			} else if (state == 200) {
				if (data.length > 1) {
					SomeLightBox.error("Gene name " + geneName + " is ambigious");
				} else {
					browser.addHighlight(data[0]);
				}
			} else {
				SomeLightBox.error("Gene " + geneName + " not found");
			}
		})
	}
});

$(document).on("click", "#popup-cancel", function(ev){
	$("#popup").hide();
});

$(document).on("click", "#open-settings", function () {
	if ($("#settings").length) {
		lightbox.loadById("settings");
	}
	lightbox.show();
});

$(document).on("change", "#node-color", function () {
	if (browser.highlights.length) {
		browser.nodeColor = "#" + this.value;
	} else browser.setNodeColor("#" + this.value);
});

$(document).on("change", "#edge-color", function (){
	if (browser.highlights.length) {
		browser.edgeColor = "#" + this.value;
	} else browser.setEdgeColor("#" + this.value);
});

$(document).on("click", "#go-to-gene", function() {
	window.location = $("base").attr("href") + "interaction?gene=" + $(this).attr("target");
	$(".contextmenu").hide();
});

$(document).on("click", "#cluster-gene", function(){
	browser.cluster($(this).attr("target"));
	$(".contextmenu").hide();
});

$(document).on("click", "#export-image", function () {
	var url = $("canvas")[0].toDataURL();
	var a = document.createElement("a");
	a.href = url;
	a.target = "blank";
	a.download = browser.data.nodes.get(getQueryVariable("gene")).label + "_radius_" + browser.radius + "_" + (getQueryVariable("sigA") ? "with": "without") + "_sigA_regulon";
	a.click();
})

$(document).on("click", "#export-csv", function () {
	var url = "data:text/csv;charset=utf8,";
	var data = browser.getData();
	for (var i = 0; i < data.length; i++) {
		data[i] = data[i].join("\t");
	}
	data = data.join("\n");
	url += encodeURI(data);
	var a = document.createElement("a");
	a.href = url;a.style.display = "none";a.download="interactions.csv";a.target="_blank";
	document.body.appendChild(a);a.click();a.remove();
});

$(document).on("click", "#export-nvis", function(){
	var nvis = browser.getNetwork();
	var str = JSON.stringify(nvis);
	var url = "data:text/plain;charset=utf8," + encodeURIComponent(str);
	var a = document.createElement("a");
	a.href = url;a.style.display = "none";a.download="interactions.nvis";a.target="_blank";
	document.body.appendChild(a);a.click();a.remove();
});

$(document).on("change", "#omics", function () {
	var con = this.value;
	browser.clearOmicsData();
	browser.setOmicsData(con);
});



var InteractionBrowser = InteractionBrowser || function (geneId) {
	this.radius = 1;
	this.spacing = 1;
	this.target;
	this.highlights = [];
	this.nodeColor = "#1976d2";
	this.edgeColor = "#848484";
	this.omicsData;
	this.container = document.getElementById("network-container");

	this.rawData;
	this.data = {
		nodes: new vis.DataSet(),
		edges: new vis.DataSet()
	};
	this.network;

	this.load();

	this.conditions;
}

/**
 * load all conditions and network
 */
InteractionBrowser.prototype.load = function () {
	var self = this;
	ajax.get({
		url: window.location.href,
		headers: {Accept: "application/json"}
	}).done(function(state, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if (state == 200) {
			self.rawData = data;
			self.createData();
		} else {
			SomeLightBox.error("Data not found");
		}
	});
	ajax.get({
		url: "expression/condition",
		headers: {Accept: "application/json"}
	}).done(function(state, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if (state == 200) {
			self.conditions = {};
            for(var i in data){
                self.conditions[data[i].id] = data[i];
			}
			
			var forSelection = {};
			for (var id in data) {
				var type = data[id].type
				if (!(type in forSelection)) {
					forSelection[type] = {};
				}
				forSelection[type][id] = data[id];
			}
			// filters
			new GroupSelect("omics", forSelection);
		} else {
			SomeLightBox.error(data.message);
		}
	})
}

InteractionBrowser.prototype.createData = function () {
	// create network according to the radius
	var self = this;
	var rNodes = {};
	var nodes, edges;
	if ("distances" in self.rawData) {
		for (var id in self.rawData.distances) {
			var distance = self.rawData.distances[id];
			if (distance == 0) {
				self.target = id;
			}
			if (distance <= self.radius) {
				rNodes[id] = true;
			}
		}
		nodes = self.rawData.nodes.filter(function(node){
			return (node.id in rNodes);
		});
	
		edges = self.rawData.edges.filter(function(edge){
			return (edge.from in rNodes) && (edge.to in rNodes);
		});
		$("#display-block").show();
	} else {
		nodes = self.rawData.nodes;
		edges = self.rawData.edges;
	}

	for (var i = 0; i < nodes.length; i++) {
		nodes[i].label = nodes[i].title;
	}

	for (var i = 0; i < edges.length; i++) {
		edges[i].title = edges[i].mode
	}

	self.data.nodes.clear();
	self.data.nodes.update(nodes);
	self.data.edges.clear();
	self.data.edges.update(edges);

	self.createNetwork();

	$("#radius-display").html(self.radius);
	$("#coverage-display").html((self.data.nodes.length / 935 * 100 + "").substr(0,4) + "%");

	$("#gene-display").html(self.data.nodes.get(self.target).label);
}

InteractionBrowser.prototype.createNetwork = function () {
	var self = this;
	var options = {
		nodes: {
			shape: 'dot',
			color: {
				background: self.nodeColor,
				border: self.edgeColor,
				highlight: {
					border: self.edgeColor,
					background: "darkorange"
				}
			},
			size: 22,
			font: {
				size: 22,
				color: "gray"
			},
		},
		edges: {
			smooth: true,
			width: 2,
			color: {
				inherit: "both"
			},
		},
		physics: {
			enabled: true,
			stabilization: false,
			barnesHut: {
				springLength: 200,
				gravitationalConstant: Math.pow(2, self.spacing) * - 2750 * self.data.nodes.length / 10,
			}
		},
		layout: {
			improvedLayout: false,
			randomSeed:55
		}
	};
	$("#loading").hide();

	if (self.network && self.network instanceof vis.Network) {
		self.network.setOptions(options);
	} else {
		self.network = new vis.Network(self.container, self.data, options);
		self.network.moveTo({scale:0.7});

		self.network.on("click", function (ev) {
			$("#popup").hide();
			$(".contextmenu").hide();
			if (ev.nodes.length > 0) {
				// if nodes are selected
				var first = ev.nodes[0];
				if (first.length == 40) {
					self.showPoppup(first);
					self.clearHighlight();
					self.addHighlight({id: first});
				} else if (first[0] = "c") {
					self.network.openCluster(first);
				}
			} else {
				self.clearHighlight();
			}
		});

		self.network.on("oncontext", function(ev){
			$(".contextmenu").hide();
			ev.event.preventDefault();
			var sel = self.network.getNodeAt(ev.pointer.DOM);
			var position = {
				left: (ev.pointer.DOM.x + 200 > document.body.clientWidth ? document.body.clientWidth - 200 : ev.pointer.DOM.x),
				top: ev.pointer.DOM.y,
			}
			if (sel) {
				$("#contextmenu-1").css(position).show();
				$("#go-to-gene").attr("target", sel);
				$("#cluster-gene").attr("target", sel);
			} else {
				var position = {
					left: (ev.pointer.DOM.x + 200 > document.body.clientWidth ? document.body.clientWidth - 200 : ev.pointer.DOM.x),
					top: ev.pointer.DOM.y,
				}
				$("#contextmenu-2").css(position).show();
			}
		});
	}
}

InteractionBrowser.prototype.fade = function () {
	var self = this;
	self.network.setOptions({
		nodes: {
			color: {
				background: "#dedede",
				border: "#efefef"
			},
			font: {
				color: "#dedede"
			}
		}
	});
}

InteractionBrowser.prototype.restore = function () {
	// if omics data
	var self = this;
	self.network.setOptions({
		nodes: {
			color: {
				background: self.nodeColor,
				border: self.edgeColor
			},
			font: {
				color: "gray"
			}
		},
	});
}

InteractionBrowser.prototype.setRadius = function (radius) {
	var self = this;
	self.radius = radius;
	self.clearHighlight();
	self.clearOmicsData();
	$("#transcriptomics").val(-1);
	$("#proteomics").val(-1);
	self.createData();
}

InteractionBrowser.prototype.setSpacing = function (spacing) {
	var self = this;
	self.spacing = spacing;
	self.network.setOptions({
		physics: {
			barnesHut: {
				gravitationalConstant: Math.pow(2, self.spacing) * - 2750 * self.data.nodes.length / 10,
			}
		}
	})
}

InteractionBrowser.prototype.addHighlight = function (gene) {
	var self = this;
	var node = self.data.nodes.get(gene.id);
	if (node) {
		var nodeUpdate = [];
		if (self.highlights.length == 0) {
			self.fade();
		}

		self.highlights.push(gene);

		if (node.state != "omics") {
			node.state = "highlight";
			node.color = {
				background: "darkorange",
				border: "darkorange"
			};
			nodeUpdate.push(node);
		}

		self.network.getConnectedNodes(gene.id).forEach(function(nodeId){
			var node = self.data.nodes.get(nodeId);
			if (!node.state) {
				node.state = "highlight";
				node.color = {
					background: "darkorange",
				}
				nodeUpdate.push(node);
			}
		});

		self.data.nodes.update(nodeUpdate);
	} else {
		SomeLightBox.error("Gene " + gene.title + " is not in the network");
	}
}

InteractionBrowser.prototype.clearHighlight = function () {	
	var self = this;
	var update = [];
	self.highlights = [];
	self.data.nodes.forEach(function(node){
		if (node.state == "highlight") {
			node.color = null;
			node.state = null;
			update.push(node);
		}
	});
	self.data.nodes.update(update);
	self.restore();
}

InteractionBrowser.prototype.setNodeColor = function (color) {
	var self = this;
	self.nodeColor = color;
	var options = {
		nodes: {
			color: {
				background: color
			}
		}
	};
	self.network.setOptions(options);
}

InteractionBrowser.prototype.setEdgeColor = function (color) {
	var self = this;
	self.edgeColor = color;
	var options = {
		nodes: {
			color: {
				border: self.edgeColor
			}
		}
	};
	self.network.setOptions(options);
}

InteractionBrowser.prototype.getOmicsData = function (conditionId) {
	var self = this; var url, data;
	if (self.data.nodes.length > 400) {
		url = "expression?condition=" + conditionId;
		data = "";
	} else {
		var geneIds = [];
		self.data.nodes.forEach(function(node,  id){
			geneIds.push(id);
		});
		url = "expression?condition=" + conditionId;
		data = ajax.serialize({
			genes: geneIds.join(",")
		});
	}
	ajax.bigGet({
		url: url,
		data: data,
		headers: {Accept: "application/json"}
	}).done(function(state, data, error, xhr){
		if (state == 200) {
			self.omicsData = data;
			self.showOmicsData(conditionId);
		} else {
			SomeLightBox.error(data.message);
		}
	})
}

InteractionBrowser.prototype.showOmicsData = function (conditionId) {
	var self = this;
	var update = [];
	
	var con = self.conditions[conditionId];
	var spectrum = new ColorSpectrum(con.title, con.min, con.max, con.type == "protein level (copies per cell)" ? "log": "");
	
	for (var id in self.omicsData) {
		if (self.data.nodes.get(id)) {
			var color = spectrum.getColor(self.omicsData[id]);
			update.push({
				id: id,
				color: {
					background: color
				},
				state: "omics"
			})
		}
	}
	self.data.nodes.update(update);
	$("#legend").html("").append(spectrum.createLegend());
}

InteractionBrowser.prototype.setOmicsData = function (conditionId) {
	if (conditionId > 0) {
		this.clearHighlight();
		this.getOmicsData(conditionId);
	}
}

InteractionBrowser.prototype.clearOmicsData = function () {
	var self = this;
	var update = [];
	self.data.nodes.forEach(function(node){
		if (node.state = "omics") {
			node.color = null;
			node.state = null;
		}
		update.push(node);
	});
	self.data.nodes.update(update);
	$("#legend").html("");
}

InteractionBrowser.prototype.cluster = function (id) {
	var self = this;
	var label = self.data.nodes.get(id).label
	var edges = self.network.getConnectedEdges(id);
	var nodes = [];
	for (var i = 0; i < edges.length; i++) {
		var cn = self.network.getConnectedNodes(edges[i]);
		nodes.push(cn[1]);
	}
	self.network.cluster({
		joinCondition: function(node){
			return (node.id == id) || (nodes.indexOf(node.id) != -1);
		},
		clusterNodeProperties: {
			label: label + " regulon",
			id: "c" + id,
			color: "darkorange"
		},
	});
} 

InteractionBrowser.prototype.showPoppup = function (geneId) {
	ajax.get({
		url: "gene/summary?id=" + geneId,
	}).done(function(state, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to server lost.");
		} else if (state == 200) {
			$("#info-box").html(parseMarkup(data));
			$("#popup").show();
		} else {
			SomeLightBox.error("Gene not found");
		}
	})
}

InteractionBrowser.prototype.getData = function () {
	var self = this;
	var data = [["protein1", "protein2"]];
	self.data.edges.forEach(function(edge){
		var from = self.data.nodes.get(edge.from).label;
		var to = self.data.nodes.get(edge.to).label;
		data.push([from,to]);
	});
	return data;
}

InteractionBrowser.prototype.getNetwork = function () {
	var self = this;
	self.network.storePositions();
	var data = self.data;
	var allNodes = [];
	var allEdges = [];
	var edgeGroups = [];
	var edgeColors = [];
	data.nodes.forEach(function (n) {
		allNodes.push(n);
	});
	data.edges.forEach(function(e) {
		allEdges.push(e);
	});
	allNodes = JSON.parse(JSON.stringify(allNodes)); // clone all
	allEdges = JSON.parse(JSON.stringify(allEdges)); // clone all
	allEdges.forEach(function(e){
		if(data.nodes.get(e.from)) e.from = data.nodes.get(e.from).title;
		if(data.nodes.get(e.to)) e.to = data.nodes.get(e.to).title;
		if (e.title.trim() == "") {
			delete e.color;
			return;
		}
		if (edgeGroups.indexOf(e.title) == -1) {
			edgeGroups.push(e.title);
			edgeColors.push(e.color);
		}
		delete e.description;
		e._group = edgeGroups.indexOf(e.title);
		e._color = e.color;
	});
	allNodes.forEach(function(n){
		n.id = n.title;
	});
	var nvis = {
		nodes: allNodes,
		edges: allEdges,
		edgeGroups: edgeGroups,
		nodeGroups: [],
		editMode: false,
		background: "white",
		configuration: {
			directed: true,
			nodeShape: "dot",
			showNodeLabel: true,
			showEdgeLabel: false,
			nodeColor: "#1967d2",
			nodeColors: ["#1967d2"],
			nodeFontColor: "white",
			edgeFontColor: "white",
			fadeColor: "rgba(160,160,160,0.3)",
			edgeColor: "#848484",
			edgeColors: edgeColors,
			physicsOptions: {
				maxVelocity: 50,
				stabilization: false,
				barnesHut: {
					damping: .3,
					gravitationalConstant: Math.pow(2,self.spacing) * -3750 * data.nodes.length / 10,
					springLength: 200
				}
			},
			improvedLayout: false,
			showStabilization: true,
			physics: true,
		}
	}
	return nvis;
}
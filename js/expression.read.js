function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
    console.log('Query variable %s not found', variable);
}

function ucfirst (str) {
	str += ''
	var f = str.charAt(0)
		.toUpperCase()
	return f + str.substr(1)
}

$(document).ready(function() {
	var gene = getQueryVariable("gene");
	if (("echarts" in window) && gene) {
		$("#hint").hide();
		$("#diagram-t").css("height", "600px");
		$("#diagram-p").css("height", "600px");
		browser = new ExpressionBrowser (gene);
	}
});

$(document).on("submit", "#search", function(ev){
	ev.preventDefault();
	ev.stopPropagation();
	var geneName = this.geneName.value.trim();
	if (geneName.length > 1) {
		$.ajax({
			url: "gene?keyword=" + encodeURIComponent(geneName) + "&mode=title",
			dataType:"json",
			success: function (data) {
				if (data.length == 1) {
					window.location = $("base").attr("href") + "expression?gene=" + data[0].id;
				} else {
					SomeLightBox.error("Gene " + geneName + " is ambigious");
				}
			},
			error: function () {
				SomeLightBox.error("Connection to server lost.");
			}
		})
	}
	return false;
});

$(document).on("submit", ".control", function(ev) {
	ev.preventDefault();
	ev.stopPropagation();
	var geneName = this.geneName.value.trim();
	if (geneName.length > 1) {
		$.ajax({
			url:"gene?keyword=" + encodeURIComponent(geneName),
			dataType:"json",
			success: function (data) {
				if (data.length == 1) {
					var gene = data[0];
					browser.addGene(gene);
				} else {
					SomeLightBox.error("Gene " + geneName + " is ambigious");
				}
			},
			error: function () {
				SomeLightBox.error("Gene " + geneName + " is not found.");
			}
		});
	}
});

var ErrorMessage = ErrorMessage || function (message, container) {
	this.message = message;
	
	this.view;
	this.cancel;

	this.createView();
	$(container).append(this.view);
}

ErrorMessage.prototype.createView = function () {
	var self = this;
	self.view = $("<p></p>");
	self.cancel = $("<span>[x]</span>");
	self.cancel.css({
		"margin-right":"5px",
		cursor: "pointer"
	});

	var messagebox = $("<span></span>");
	messagebox.html(self.message);

	self.view.append(self.cancel, messagebox);
	self.view.css("color", "red");

	self.cancel.on("click", function () {
		self.view.remove();
	});
}

var TickBox = TickBox || function (gene, container) {
	this.view;
	this.gene = gene;

	this.createView();
	$(container).append(this.view);
}

TickBox.prototype.createView = function(){
	var self = this;
	self.view = $("<p></p>");
	var checkbox = $("<input type='checkbox' checked />").addClass(self.gene.id);
	checkbox.on("change", function(){
		if (this.checked) {
			browser.addGene(self.gene);
		} else {
			browser.removeGene(self.gene);
		}
	});

	var label = $("<span></span>");
	label.html(self.gene.title);
	self.view.append(checkbox, label);
}

var ExpressionBrowser = ExpressionBrowser || function (geneId) {
	this.data = {};
	this.genes = {};
	this.charts = {};
	this.containers = {};
	this.conditions;

	// fixed parts of chart options
	// need to set:
	// 		yAxis.name
	// 		series
	// 		legend: 
	this.chartOptions = {
		title: {
			show: true,
		},
		toolbox: {
			show: true,
			feature: {
				restore: {
					show: true,
					title: "Restore",
					color: "black",
				},
				saveAsImage: {
					show: true,
					title: "Save as image",
					type: "png",
					lang: ["Click to save"]
				},
				dataView: {
					show: true,
					title: "Data",
					lang: ["Data", "Close", "Refresh"]
				}
			}
		},
		tooltip: {
			show: true,
			trigger:'axis',
			transitionDuration:0,
		},
		xAxis: {
			name: "Conditions",
			axisLabel: {
				interval: 0,
				rotate: 90,
				margin: 12,
			},
			nameLocation: "middle",
			nameGap: 70,
			nameTextStyle: {
				fontWeight: 'bold'
			},
			data: [],
		},
		grid :{
			left: 50,
			right: 30,
			bottom: 100,
		},
		dataZoom:[
			{
				type:'slider',
				end: 100
			},
			{
	            type: 'inside',
	            yAxisIndex: [0],
	        },
		],
		yAxis: {
			nameLocation: "middle",
			nameGap: 30,
			nameTextStyle: {
				fontWeight: 'bold'
			},
			scale: true
		},
		series: [],
		legend: {
			data: []
		},
	}

	this.chartSerieOption = {
		type: 'line',
		symbol: "circle",
		symbolSize: 10,
		showAllSymbol: true,
		legendHoverLink: true,
		animation: false,
		data: []
	}

	
	var self = this;
	// for subtiwiki, no context browser
	// self.loadConditions(function() {
	// 	self.contextBrowser = new ContextExpressionBrowser($("#omics-position-browser"), self.conditions["tilling array"]);
	// 	self.contextBrowser.showMessage("loading...");
	// 	self.loadGene(geneId, function() {
	// 		$("#data").show();
	// 		self.loadData(geneId, function() {
	// 			self.draw();
	// 		});
	// 		self.loadContext(geneId);
	// 	});
	// });
	self.loadConditions(function() {
		self.loadGene(geneId, function() {
			$("#data").show();
			$("#section").show();
			self.adjustImageSize();
			self.loadData(geneId, function() {
				self.draw();
			});
		});
	});
}

ExpressionBrowser.prototype.loadGene = function (geneId, callback) {
	var self = this;
	$.ajax({
		url: "gene/summary?id=" + geneId,
		success:function(data) {
			$("#gene-summary").html(parseMarkup(data));
			self.genes[geneId] = $("#gene-summary h2 a").html();
			callback();
		}
	})
}

ExpressionBrowser.prototype.loadData = function (geneId, callback) {
	var self = this;
	$.ajax({
		url: "expression?gene=" + geneId,
		dataType:"json",
		success: function (data) {
			var filtered, hasValue;
			for(var type in self.conditions) {
				filtered = {};
				hasValue = false;
				for (var conditionId in self.conditions[type]) {
					if (data[conditionId]) {
						filtered[conditionId] = data[conditionId];
						hasValue = true;
					}
				}
				if (!(type in self.data)) {
					self.data[type] = {
						size: 0
					};
				}
				if (hasValue) {
					self.data[type][geneId] = {
						show: true,
						data: filtered
					}
					self.data[type].size++;
				} else {
					new ErrorMessage("No data for " + self.genes[geneId], $("#error-t"));
				}
			}
			if (callback) callback.apply(self);
		},
		error: function () {
			SomeLightBox.error("No data available");
		}
	});
}

ExpressionBrowser.prototype.loadConditions = function (callback) {
	var self = this;
	$.ajax({
		url: "expression/condition",
		dataType:"json",
		success: function (data) {
			// sort by type
			self.conditions = {};
			data.forEach(function(con){
				if (!(con.type in self.conditions)) {
					self.conditions[con.type] = {};
				}
				self.conditions[con.type][con.id] = con;
			})
			if (callback) callback();
		},
		error: function () {
			SomeLightBox.error("Connection to server lost");
		}
	});
}

ExpressionBrowser.prototype.loadContext = function (geneId, callback) {
	var self = this;
	$.ajax({
		url: "genome/context?gene=" + geneId + "&span=20000",
		dataType:"json",
		success: function (data) {
			for(var i = 0; i < data.length; i++) {
                data[i].label = data[i].title;
			}
			self.contextBrowser.setData(data, genomeLength, function(){
				self.contextBrowser.hideMessage();
			});
			self.contextBrowser.on("click", function(ev){
				if (ev.currentGene) {
					window.open("expression?gene=" + ev.currentGene.id);
				}
			});
			self.contextBrowser.diagram.focus(geneId);
		},
		error: function (data) {
			self.contextBrowser.showMessage(data.message);
		}
	});
}

ExpressionBrowser.prototype.draw = function () {
	var self = this;
	for (var type in self.data) {
		if (self.data[type].size > 0) {
			if (!(type in self.containers)) {
				container = $("<div class='section' style='margin-top: 100px'>"
					+ "<div class='left' style='height:600px'></div>"
					+ "<div class='right'>"
						+ "<form class='control' style='padding:10px;background:#eee'>"
							+ "<p><label>Compare with: </label></p><p><input name='geneName' /><input type='submit' /></p>"
							+ "<div class='data-cache'></div>"
						+ "</form>"
						+ "<div class='description'></div>"
					+ "</div>");
				self.containers[type] = container;
				$("#data").append(container);
			}
			// create container
			self.drawChart(type, self.data[type], self.containers[type].find(".left")[0], self.containers[type].find(".description")[0]);
		}
	}
}

ExpressionBrowser.prototype.addGene = function (gene) {
	var self = this;
	var isLoaded = false;
	for (var type in self.data) {
		if (gene.id in self.data[type]) {
			isLoaded = true;
			self.data[type][gene.id].show = true;
		}
	}
	if (isLoaded) {
		self.filterDataSet();
	} else {
		$(".data-cache").each(function(idx, el){
			new TickBox(gene, el);
		});

		self.genes[gene.id] = gene.title;

		// redraw
		self.loadData(gene.id, function(){
			self.draw();
		});

		// create tickoff box
	}
}

ExpressionBrowser.prototype.removeGene = function (gene) {
	var self = this;
	var isLoaded = false;
	for (var type in self.data) {
		if (gene.id in self.data[type]) {
			isLoaded = true;
			self.data[type][gene.id].show = false;
		}
	}
	if (isLoaded) {
		self.filterDataSet();
	}
}

ExpressionBrowser.prototype.drawChart = function (type, data, container, descriptionContainer) {
	var self = this;
	var chart = echarts.init(container);
	var options = JSON.parse(JSON.stringify(self.chartOptions));
	options.title.text = options.yAxis.name = ucfirst(type);
	if (type == "proteomic data (copies per cell)") options.yAxis.type = "log";

	// add conditions to the xAxis
	var chartConditions = [];
	for(var conditionId in self.conditions[type]) {
		var condition = self.conditions[type][conditionId];
		chartConditions.push(condition);
		options.xAxis.data.push(condition.short || condition.title);
	}
	// add series
	for (var geneId in data) {
		if (geneId != "size") {
			var geneName = self.genes[geneId];
			options.legend.data.push(geneName);
			var serie = JSON.parse(JSON.stringify(self.chartSerieOption));
			serie.name = geneName;
			for (var conditionId in data[geneId].data) {
				var value = data[geneId].data[conditionId];
				serie.data.push(value);
			}
			options.series.push(serie);
		}
	}

	// draw the chart
	chart.setOption(options);
	chart.on("click",function(params){
		var condition = chartConditions[params.dataIndex];
		$(descriptionContainer).html("<h3>" + condition.title + "</h3><p>" + (condition.description || "") + "</p><p><a href='http://www.ncbi.nlm.nih.gov/pubmed/" + condition.pubmed + "' target='_blank'>PUBMED</a></p>");
	});

	self.charts[type] = chart;
}

ExpressionBrowser.prototype.filterDataSet = function () {
	var self = this;
	for (var type in self.data) {
		for (var geneId in self.data[type]) {
			if (geneId != "size") {
				if (self.data[type][geneId].show) {
					self.charts[type].dispatchAction({
						type: "legendSelect",
						name: self.genes[geneId]
					});
					$("." + geneId).prop("checked", true);
				} else {
					self.charts[type].dispatchAction({
						type: "legendUnSelect",
						name: self.genes[geneId]
					});
					$("." + geneId).prop("checked", false);
				}
			}
		}
	}
}

ExpressionBrowser.prototype.adjustImageSize = function () {
	var img1 = $("#exp-img");
	var img2 = $("#exp-legend");
	var width = $(".left").width() - 10;
	var r = img1.width() /(img1.width() + img2.width());
	img1.css({width: (r * width > 992 ? 992 : r*width) + "px"});
	img2.css({width: ((1-r)*width > 228 ? 228 : (1-r)*width) + "px"});
}

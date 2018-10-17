$(document).ready(function() {
	// using google charts lib
	// simulate google lib not correctly loaded
	if ("google" in window) {
		google.charts.load('current', {'packages':['corechart']});
		google.charts.setOnLoadCallback(function(){
			browser = new ExpressionBrowser (getQueryVariable("gene"));
		});
	}
});

ExpressionBrowser.prototype.prepareDataT = function () {
	var self = this;
	var dataTable = new google.visualization.DataTable();
	dataTable.addColumn("string", "Condition");
	dataTable.addColumn({type: "string", role: "tooltip", p: {"html": true}});

	for(var id in self.dataT) {
		if (self.dataT[id].show) dataTable.addColumn("number", self.genes[id]);
	}

	var raw = [];
	order.forEach(function(con){
		var dataRow = [];
		dataRow.push(self.conditions.T[con].short);
		dataRow.push(""); // todo
		for (var id in self.dataT) {
			if (self.dataT[id].show) {
				dataRow.push(self.dataT[id].data["con" + con]);
			}
		}
		raw.push(dataRow);
	});

	dataTable.addRows(raw);
	self.chartDataT = dataTable;
}

ExpressionBrowser.prototype.drawT = function () {
	var self = this;
	if ("{}" != JSON.stringify(self.dataT)) {
		self.prepareDataT();
		self.optionsT = {
			height: 600,
			hAxis:{
				title: "Condition",
				textStyle : {
					fontSize: 9 // or the number you want
				},
				slantedTextAngle: 90,
				showTextEvery: 1, 
				slantedText: true, 
		   	},
			vAxis:{
				title: "Expression Level",
			},
			legend: "top",
			chartArea:{
				top: 50,
				left: 50,
				width: "90%",
				height:"80%"
			},
			pointSize: 10,
		 	tooltip: { isHtml: true },
		 	explorer: {
		 		axis: 'vertical',
				keepInBounds: true,
		 		maxZoomIn: 4.0
		 	}
		};
		self.chartT = new google.visualization.LineChart(document.getElementById("diagram-t"));
		self.chartT.draw(self.chartDataT, self.optionsT);

		google.visualization.events.addListener(self.chartT, 'select', function() {
			var sel = self.chartT.getSelection();
			if (sel.length == 1 && sel[0].row) {
				var condition = self.conditions.T[order[sel[0].row]];
				$("#description-t").html("<h4>"+conditions.title+"</h4><div>" + condition.description + "</div><p><a href='https://www.ncbi.nlm.nih.gov/pubmed/" + condition.pubmed + "'>PubMed</a></p>");
			}
		});
	}
}

ExpressionBrowser.prototype.prepareDataP = function () {
	var self = this;
	var dataTable = new google.visualization.DataTable();
	dataTable.addColumn("string", "Condition");
	dataTable.addColumn({type: "string", role: "tooltip", p: {"html": true}});

	for(var id in self.dataP) {
		if (self.dataP[id].show) {
			dataTable.addColumn("number", self.genes[id]);
		}
	}
	
	var raw = [];

	for (var con in self.conditions.P) {
		var dataRow = [];
		dataRow.push(self.conditions.P[con].title);
		dataRow.push("");
		for(var id in self.dataP) {
			if (self.dataP[id].show) {
				dataRow.push(self.dataP[id].data["con" + con]);
			}
		}
		raw.push(dataRow);
	}

	dataTable.addRows(raw);
	self.chartDataP  = dataTable;
}

ExpressionBrowser.prototype.drawP = function () {
	var self = this;
	if ("{}" != JSON.stringify(self.dataP)) {
		self.prepareDataP();
		self.optionsP = {
			height: 600,
			hAxis:{
				title: "Condition",
				textStyle : {
					fontSize: 14 // or the number you want
				},
				slantedTextAngle: -45,
				slantedText: true,
		   	},
			vAxis:{
				title: "Copies per cell", 
				scaleType: 'log', 
				viewWindowMode: 'explicit',
				viewWindow: {
					max: 1000000,
					min: 1
				}
			},
			legend: "top",
			chartArea:{
				top: 20,
				width: "82%",
				height:"75%"
			},
			pointSize: 10,
		 	explorer: {
		 		axis: 'vertical',
				keepInBounds: true,
		 		maxZoomIn: 4.0
		 	}
		}
		self.chartP = new google.visualization.LineChart(document.getElementById("diagram-p"));
		self.chartP.draw(self.chartDataP, self.optionsP);
		
		google.visualization.events.addListener(self.chartP, 'select', function() {
			var sel = self.chartP.getSelection();
			if (sel.length == 1 && sel[0].row) {
				var condition = self.conditions.P[sel[0].row];
				$("#description-p").html("<h4>"+condition.title+"</h4><div>" + condition.description + "</div><p><a href='https://www.ncbi.nlm.nih.gov/pubmed/" + condition.pubmed + "'>PubMed</a></p>");
			}
		});
	}
}

ExpressionBrowser.prototype.filterDataSet = function () {
	var self = this;
	var dataViewT = new google.visualization.DataView(self.chartDataT);
	var dataViewP = new google.visualization.DataView(self.chartDataP);
	var i = 2;
	var showT = [0,1]; var hideT = [];
	var showP = [0,1]; var hideP = [];
	for (var id in self.dataT) {
		if (self.dataT[id].show) {
			showT.push(i);
		} else hideT.push(i);
		i++;
	}
	var i = 2;
	for (var id in self.dataP) {
		if (self.dataP[id].show) {
			showP.push(i);
		} else hideP.push(i);
		i++;
	}

	dataViewT.setColumns(showT);
	dataViewT.hideColumns(hideT);

	dataViewP.setColumns(showP);
	dataViewT.hideColumns(hideP);

	self.chartT.draw(dataViewT,self.optionsT);
	self.chartP.draw(dataViewP,self.optionsP);
}

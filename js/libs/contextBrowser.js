var Class = Class || function () {
};

Class.extend = function (proto) {
	var __super = Object.create(this.prototype);
	var child = function () {
		this.__super = __super;
		if (proto.__construct) {
			proto.__construct.apply(this, arguments);
		}
	}
	child.prototype = Object.create(this.prototype);
	child.constructor = child;
	for (var key in proto) {
		if (proto.hasOwnProperty(key)) {
			child.prototype[key] = proto[key];
		}
	}

	for (var key in this.constructor) {
		if (this.constructor.hasOwnProperty(key) && key !== "constructor" && key!== "prototype") {
			child[key] = this.constructor[key]
		}
	}
	return child;
}

Class.constructor = Class;

var ContextBrowser = Class.extend({
	__construct: function(container) {
		this.view;
		this.dataSet;
		this.diagram;
		this.diagramContainer;

		this.setZoom;
		this.hideRNA;

		this.createView();
		$(container).append(this.view);
	},
	createView: function () {
		var self = this;
		self.view = $('<div style="margin: 2.5% 0; border: 1px solid #bbb; border-radius: 5px; overflow: hidden; box-shadow: 1px 1px 2px 1px rgba(0,0,0,0.07);"><div style="position: relative; height: 320px"><div style="position: absolute;width: 100%; height: 100%; left:0; top: 0;"><div style="padding: 10px; background: #eee; text-align: right;"><input type="checkbox" id="context-browser-hide-sRNA"></input><label for="context-browser-hide-sRNA" style="cursor: pointer; margin-right:20px">Hide small RNAs</label><label>Zoom: </label><input type="range" min="1" max="20" id="context-browser-set-zoom" value="10"/></div><div id = "context-browser" style="margin: 20px 0; width: 100%; height: 250px; position: relative;"></div><img src="img/br.png" style="position: absolute; bottom: 0; right: 0" /></div><div id="context-browser-loading" style="position: absolute;width: 100%; height: 100%; left:0; top: 0;background: black;opacity: 0.8; z-index: 1; color: white; text-align: center; "><span style="font-weight: bold; line-height: 400px" id="context-browser-message">Loading</span></div></div></div>');

		self.diagramContainer = self.view.find("#context-browser");
		self.setZoom = self.view.find("#context-browser-set-zoom");
		self.hideRNA = self.view.find("#context-browser-hide-sRNA");

		self.setZoom.on("change", function(){
			var resolution = 1 / (21 - this.value);
			self.diagram.setOptions({
				resolution: resolution
			});
		});

		self.hideRNA.on("change", function(ev) {
			if (this.checked) {
				if (self.diagram) self.diagram.forEach(function(el){
					if (el.type == 'gene' && el.title.match(/^S\d+$/i)) {
						el.hide();
					}
				})
			} else {
				if (self.diagram) self.diagram.forEach(function(el){
					if (el.type == 'gene' && el.title.match(/^S\d+$/i)) {
						el.show();
					}
				})
			}
		});

		self.loading = self.view.find("#context-browser-loading");
	},
	setData:function (data, genomeLength) {
		var self = this;
		data.forEach(function(each){
			each.label = each.title;
		});
		self.dataSet = new Genome.dataSet(data, 'cyclic', genomeLength);
		if (self.diagram) {
			self.diagram.setData(dataSet);
		} else {
			self.diagram = new Genome.diagram(self.diagramContainer, self.dataSet, {
				width: '100%',
				height: '100%',
				gene: {
					background: "#8BC34A",
					borderColor: "transparent"
				}
			});
			self.diagram.on("mouseover", function (ev) {
				if (ev.currentGene) {
					ev.currentGene.highlight(true);
				}
			});
			self.diagram.on("mouseout", function (ev) {
				if (ev.currentGene) {
					ev.currentGene.highlight(false);
				}
			});

			var inJob = false;
			
			self.diagram.on('leftedge', function (ev) {
				if (!inJob) {
					self.loading.show();
					inJob = true;
					var min = self.diagram.dataSet.getMin();
					var url = "genome/context?span=30000&position=" + min;
					ajax.get({
						url:url, headers: {Accept: "application/json"}
					}).done(function(status, data, error, xhr){
						if (error) {
							SomeLightBox.error("Connection to server lost.");
						} else if (status == 200) {
							data.forEach(function(each){
								each.label = each.title;
							})
							self.dataSet = new Genome.dataSet(data, 'cyclic', genomeLength);
							self.diagram.setData(self.dataSet);
						}
						self.loading.hide();
						inJob = false;
					});
				}
			});
			
			self.diagram.on('rightedge', function(ev) {
				if (!inJob) {
					self.loading.show();
					inJob = true;
					var min = self.diagram.dataSet.getMax();
					var url = "genome/context?span=30000&position=" + min;
					ajax.get({
						url:url, headers: {Accept: "application/json"}
					}).done(function(status, data, error, xhr){
						if (error) {
							SomeLightBox.error("Connection to server lost.");
						} else if (status == 200) {
							data.forEach(function(each){
								each.label = each.title;
							})
							self.dataSet = new Genome.dataSet(data, 'cyclic', genomeLength);
							self.diagram.setData(self.dataSet);
						}
						self.loading.hide();
						inJob = false;
					});
				}
			});
		}
		self.view.find("#context-browser-loading").hide();
	},
	on:function (type, func) {
		this.diagram.on(type, func);
	},
	off:function (type, func) {
		this.diagram.off(type, func);
	},
	setMessage:function(msg) {
		this.view.find("#context-browser-message").html(msg);
	}
});
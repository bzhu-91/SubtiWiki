window.ContextBrowser = window.ContextBrowser || function (container) {
	this.container = container;
	this.view;
	this.dataSet;
	this.diagram;
	this.diagramContainer;

	this.setZoom;
	this.hideRNA;
	this.messageBox;

	this.createView();

	this.span = 50000;
}

ContextBrowser.prototype.createView = function () {
	var self = this;
	self.view = $("<div></div>").css({
		margin: "2.5% 0",
		border: "1px solid #bbb",
		"border-radius": "5px",
		overflow: "hidden",
		position: "relative"
	});

	$(this.container).append(this.view);

	var toolbar = $("<div></div>").css({
		padding: "5px",
		background: "#eee",
		textAlign: "right"
	});

	self.diagramContainer = $("<div></div>").css({
		margin: "20px 0",
		height: "170px"
	});

	self.hideRNA = $("<input />").attr({
		type: "checkbox",
		id: "--hide-RNAs"
	});

	toolbar.append(self.hideRNA, $("<label>Hide small RNAs<label>").attr("for", "--hide-RNAs"));

	self.setZoom = $("<input />").attr({
		type: "range",
		min: 1,
		max: 20,
		value: 10
	});

	toolbar.append($("<label>Zoom:</label>"), self.setZoom);

	self.messageBox = $("<div></div>").css({
		background: "transparent",
		opacity: 0.8,
		textAlign: "center",
		display: "none",
		lineHeight: "100%",
		position: "absolute",
		top:0,left:0,
		height: "100%",
		width: "100%",
		color: "white",
		zIndex:2
	});

	var legend = $("<img src='img/br.png' />").css({
		display: "block",
		background: "#eee",
		padding: "10px",
		float: "right",
		bottom: 0,
		right: 0
	});

	self.view.append(toolbar, self.diagramContainer,legend, self.messageBox);

	self.setZoom.on("change", function(){
		var resolution = 1 / (21 - this.value);
		self.diagram.setOptions({
			resolution: resolution
		});
	});

	self.hideRNA.on("change", function(ev) {
		var hideRNA = this;
		if (self.diagram) {
			self.diagram.childViews.forEach(function(el){
				if (el.type == 'gene' && el.title.match(/^nc/i)) {
					el.visibility = !hideRNA.checked;
				}
			});
			self.diagram.redraw();
		}
	});

	self.messageBox.on("mousewheel", function(evt){
		evt.preventDefault();
	});

	self.messageBox.on("DOMMouseScroll", function(evt){
		evt.preventDefault();
	});
}

ContextBrowser.prototype.showMessage = function (message) {
	var self = this;
	this.messageBox.html(message);
	this.messageBox.show();
	this.messageBox.css({
		lineHeight: self.messageBox.height() + "px"
	});
	setTimeout(function(){
		self.messageBox.css({
			background: "black"
		})
	}, 50);
	if (this.diagram) this.diagram.disableScroll();
}

ContextBrowser.prototype.hideMessage = function () {
	this.messageBox.hide();
	this.messageBox.css({
		background: "transparent"
	});
	if (this.diagram) this.diagram.enableScroll();
}

ContextBrowser.prototype.setData = function (data, genomeLength, callback) {
	this.genomeLength = genomeLength;
	var self = this;
	data.forEach(function(each){
		each.label = each.title;
	});
	self.dataSet = new Genome.dataSet(data, 'cyclic', genomeLength);
	if (self.diagram) {
		self.diagram.setData(self.dataSet);
	} else {
		self.diagram = new Genome.diagram(self.diagramContainer[0], self.dataSet, {
			gene: {
				color: "#4caf50",
				borderColor: "gray"
			}
		});
		self.configDiagram();
	}
	if (callback) callback.call(this);
}

ContextBrowser.prototype.configDiagram = function () {
	var self = this;
	var inJob = false;
	self.diagram.on('leftedge', function (ev) {
		if (inJob) {
			return;
		}
		inJob = true;
		if (ev.previous < 0) ev.previous += self.genomeLength
		document.body.cursor = "waiting";
		self.showMessage("Loading...");
		var url = "genome/context?span="+self.span+"&position=" + (self.diagram.left - self.span);
		ajax.get({
			url:url, headers: {Accept: "application/json"}
		}).done(function(status, data, error, xhr){
			if (error) {
				SomeLightBox.error("Connection to server lost.");
			} else if (status == 200) {
				data.forEach(function(each){
					each.label = each.title;
				})
				self.setData(data, self.genomeLength, function () {
					self.hideMessage();
					inJob = false;
					document.body.cursor = "auto";
				});
			}
		});
	});
	
	self.diagram.on('rightedge', function(ev) {
		if (inJob) {
			return;
		}
		inJob = true;
		if (ev.next > self.genomeLength) ev.next -= self.genomeLength;
		self.showMessage("Loading...");
		var url = "genome/context?span="+self.span+"&position=" + (self.diagram.left + self.span);
		ajax.get({
			url:url, headers: {Accept: "application/json"}
		}).done(function(status, data, error, xhr){
			if (error) {
				SomeLightBox.error("Connection to server lost.");
			} else if (status == 200) {
				data.forEach(function(each){
					each.label = each.title;
				})
				self.setData(data, self.genomeLength, function (){
					self.hideMessage();
					inJob = false
				});
			}
		});
	});
}

ContextBrowser.prototype.on = function (type, func) {
	this.diagram.on(type, func)
}

ContextBrowser.prototype.off = function (type, func) {
	this.diagram.off(type, func);
}
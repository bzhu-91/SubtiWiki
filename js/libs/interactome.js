(function(){
	var svgNS = "http://www.w3.org/2000/svg";
	var Pi = 3.141592653;

	var Util = {
		deepExtend: function(a, b, allowDel){
			for(var prop in b){
				if (b.hasOwnProperty(prop)) {
					if (b[prop] && b[prop].constructor == Object) {
						if (a[prop] === undefined) a[prop] = {};
						if (a[prop].constructor == Object) Util.deepExtend(a[prop],b [prop]);
					} else if(b[prop] && b[prop] instanceof Array) {
						a[prop] = [];
						for (var i = 0; i < b[prop].length; i++) {
							a[prop].push(b[prop][i]);
						}
					} else if(b[prop] == null && allowDel) {
						delete a[prop];
					} else if(b[prop] != null) {
						a[prop] = b[prop];
					}
				}
			}
			return a;
		},
		attrNS: function(dom, options){
			for(var i in options){
				if (options.hasOwnProperty(i)) dom.setAttributeNS(null, i, options[i]);
			}
		},
		attr: function(dom, options){
			for(var i in options){
				if (options.hasOwnProperty(i)) dom.setAttribute(i, options[i]);
			}
		},
		elNS: function(tag, attrs){
			var d = document.createElementNS(svgNS, tag);
			if (attrs) {
				Util.attrNS(g, attrs);
			}
			return d;
		},
		el: function(tag, attrs){
			var d = document.createElement(tag);
			if (attrs) {
				Util.attr(d, attrs);
			}
			return d;
		},
		ucfirst: function(str) {
			return str[0].toUpperCase() + str.slice(1);
		},
		fireEvent: function(element, type) {
			var event; // The custom event that will be created
			if (document.createEvent) {
				event = document.createEvent("HTMLEvents");
				event.initEvent(type, true, true);
			} else {
				event = document.createEventObject();
				event.eventType = type;
			}

			event.eventName = type;

			if (document.createEvent) {
				element.dispatchEvent(event);
			} else {
				element.fireEvent("on" + event.eventType, event);
			}
		}
	};

	window.Interactome = window.Interactome || {};

	var Element = function(){
		var _default = {};
		var _global = {}
		var _private = {};
		var _actual = {};
		var _evs = {};
		var rootElement;

		this.x = 0, this.y = 0;

		this._setPosition = function(x,y){
			Util.attrNS(rootElement, {
				x:x,y:y
			})
		};

		this.hide = function(){
			Util.attrNS(rootElement, {
				visibility:'hidden'
			});
		}

		this.show = function(){
			Util.attrNS(rootElement, {
				visibility: 'show'
			})
		}

		this.locate = function(x,y){
 			if (x == null) x = this.x;
			if (y == null) y = this.y;
			this.x = x, this.y = y;
			this._setPosition(x,y)
		}

		this.getOptions = function(){
			return _actual;
		}


		this.setDefaultOptions = function(options){
			_default = Util.deepExtend(_default, options, true);
			this.composeOptions();
			this._applyOptions(_actual);
		}

		this.setPrivateOptions = function(options){
			_private = Util.deepExtend(_private, options, true);
			this.composeOptions();
			this._applyOptions(_actual);
		}

		this.setGlobalOptions = function(options){
			_global = Util.deepExtend(_global, options, true);
			this.composeOptions();
			this._applyOptions(_actual);
		}

		this.composeOptions = function(){
			_actual = Util.deepExtend(_actual, _default);
			_actual = Util.deepExtend(_actual, _global);
			_actual = Util.deepExtend(_actual, _private);
		}

		this._applyOptions = function(){};

		this.setRootElement = function(element){
			rootElement = element;
		}

		this.setOptions = function(options) {
			this.setPrivateOptions(options);
		}

		this.on = function(eventType, listener) {
			if (!(eventType in _evs)) {
				_evs[eventType] = [];
			}
			_evs[eventType].push(listener);
			rootElement.addEventListener(eventType, listener);
		}

		this.off = function(eventType, listener){
			if (listener) {
				var idx = (_evs[eventType] || []).indexOf(listener);
				if (idx != -1) {
					_evs[eventType] = _evs[eventType].splice(idx, 1);
					rootElement.removeEventListener(eventType, listener);
				}
			} else {
				(_evs[eventType] || []).forEach(function(listener){
					rootElement.removeEventListener(eventType, listener);
				})
			}
		}

		this.appendTo = function(parent){
			parent.appendChild(rootElement);
		}
		this.getBBox = function(){return rootElement.getBBox();};
	}

	var DefaultOptions = {
		width: "auto",
		height: "auto",
		background: "transparent",
		node: {
			color: "#8BC34A",
			size: 12,
			textColor: "black",
			textSize: "12pt"
		},
		nodeHighlight: {
			color: 'red'
		},
		edge: {
			color: "gray",
			strokeWidth: 1,
		},
		edgeHighlight:{
			color:'red'
		}
	};

	var node = function(_node){
		var g = Util.elNS('g');
		var t = Util.elNS('text');
		var c = Util.elNS('circle');

		g.appendChild(t);
		g.appendChild(c);

		this.setRootElement(g);

		Util.attrNS(g, {
			id: _node.id || '',
			x: 0,
			y: 0,
			class: 'node',
		});

		t.textContent = _node.label || '';


		Util.deepExtend(this, _node);

		this.setDefaultOptions(DefaultOptions.node);

		// @override
		this._applyOptions = function(o){
			c.setAttributeNS(null, 'fill', o.color);
			c.setAttributeNS(null, 'r', o.size);
			t.setAttributeNS(null, 'fill', o.textColor);
			t.setAttributeNS(null, 'font-size', o.textSize);
		}

		var privateOptions = {};
		["color", "size", "textSize", "textColor"].forEach(function(k){
			if (k in _node) {
				privateOptions[k] = _node[k];
			}
		});

		this.setPrivateOptions(privateOptions);

		for(var k in node) {
			if (node.hasOwnProperty(k) && node[k] && (node[k] instanceof Function) && k.startsWith("on")) {
				this.on(k.substr(2), node[k]);
			}
		}

		this.highlight = function(o){
			var highlight = Util.deepExtend(this.getOptions(), DefaultOptions.nodeHighlight);
			this._applyOptions(highlight);
		}

		this.unhighlight = function() {
			this.setOptions({});
		}

		this.parent = Object.getPrototypeOf(this);

		// @override
		this._setPosition = function(x,y, dx, dy){
			Util.attrNS(g, {
				transform: 'translate(' + x + ',' + y + ')'
			});

			Util.attrNS(t, {
				x: dx,
				y: dy
			});
		}

		// @override
		this.locate = function(R, theta){
			this.theta = theta;
			var x = R * Math.cos(theta);
			var y = R * Math.sin(theta);
			this.x = x, this.y = y;

			var textBox = t.getBBox();
			
			var dx = 0,dy = 0;
			if (theta < 2 * Pi && theta >= 1.5 * Pi) {
				dx = dy = 0;
			} else if(theta >= 0 && theta < 0.5 * Pi) {
				dy = textBox.height;
			} else if(theta >= 0.5 * Pi && theta < Pi) {
				dx = - textBox.width;
				dy = textBox.height;
			} else {
				dx = - textBox.width;
			}

			dx += this.getOptions().size * 1.2 * Math.cos(theta);
			dy += this.getOptions().size * 1.2 * Math.sin(theta);

			this._setPosition(x,y, dx, dy);
		}
	}

	var edge = function(_edge){
		var l;
		if (_edge.from == _edge.to) {
			l = Util.elNS("circle");
			Util.attrNS(l, {
				r: 30,
				fill: 'transparent'
			});
		} else {
			l = Util.elNS('line');
		}

		this.setRootElement(l);

		Util.attrNS(l, {
			class: 'edge',
			id: _edge.from.id + '-' + _edge.to.id
		});

		Util.deepExtend(this, _edge);

		// @override
		this._applyOptions = function(o){
			Util.attrNS(l, {
				'stroke-width': o.strokeWidth,
				stroke: o.color
			});
		};

		var privateOptions = {};
		["strokeWidth", "color"].forEach(function(k){
			if (k in _edge) {
				privateOptions[k] = _edge[k];
			}
		});

		this.setPrivateOptions(privateOptions);

		// @override
		this.locate = function(){
			if(_edge.from == _edge.to) {
				Util.attrNS(l, {
					cx: 30 * Math.cos(_edge.from.theta) + _edge.from.x,
					cy: 30 * Math.sin(_edge.from.theta) + _edge.from.y
				});
			} else {
				Util.attrNS(l, {
					x1:_edge.from.x,
					y1:_edge.from.y,
					x2:_edge.to.x,
					y2:_edge.to.y,

				});
			}
		}

		for(var k in edge) {
			if (edge.hasOwnProperty(k) && edge[k] && (edge[k] instanceof Function) && k.startsWith("on")) {
				this.on(k.substr(2), edge[k]);
			}
		}

		this.highlight = function(o){
			var highlight = Util.deepExtend(this.getOptions(), DefaultOptions.nodeHighlight);
			this._applyOptions(highlight);
		}

		this.unhighlight = function() {
			this.setOptions({});
		}
	}

	var createElement = function(t, o){
		switch(t){
			case 'node':
				node.prototype = new Element();
				return new node(o);
			case 'edge':
				edge.prototype = new Element();
				return new edge(o);
			default:
				console.error("Object of type " + type + " can not be handled");
				return null;
		}
	}

	Interactome.dataSet = function(_nodes, _edges){
		var self = this;
		var hash = {};
		this.edges = [];
		this.nodes = [];

		_nodes.forEach(function(n){
			hash[n.id] = createElement('node', n);
			self.nodes.push(hash[n.id]);
		});

		_edges.forEach(function(e){
			e.from = hash[e.from];
			e.to = hash[e.to];
			self.edges.push(createElement('edge', e));
		});

		this.getNodeById = function(id){
			return hash[id];
		}

		this.getEdgeById = function(id) {
			var edge;
			this.edges.forEach(function(e){
				if(id == (e.from.id + '-' + e.to.id)){
					edge = e;
					return;
				}
			})
			return edge;
		}

		this.getConnectedEdges = function(nodeid){
			var edges = [];
			this.edges.forEach(function(e){
				if (e.from.id == nodeid || e.to.id == nodeid) {
					edges.push(e);
				}
			});
			return edges;
		}

		this.getConnectedNodes = function(nodeid){
			var nodes = [];
			this.edges.forEach(function(e){
				if (e.from.id == nodeid) {
					nodes.push(e.to);
				}
				if (e.to.id == nodeid) {
					nodes.push(e.from);
				}
			})
			return nodes;
		}
	}

	Interactome.diagram = function(container, data, options){
		Interactome.diagram.prototype = new Element();

		var svg = Util.elNS('svg');
		var rootGroup = Util.elNS('g');
		this.setRootElement(svg);

		svg.appendChild(rootGroup);
		svg.setAttributeNS(null, "preserveAspectRatio", "none");

		while(container.firstElementChild) container.removeChild(container.firstElementChild);
		container.appendChild(svg);

		data.edges.forEach(function(edge){
			edge.appendTo(rootGroup);
		});

		data.nodes.forEach(function(node) {
			node.appendTo(rootGroup);
		});

		// @override
		this._applyOptions = function(o){
			data.edges.forEach(function(edge){
				edge.setGlobalOptions(o.edge);
			});
			data.nodes.forEach(function(node){
				node.setGlobalOptions(o.node);
			});
			Util.attrNS(svg, {
				fill: o.background
			});
			R = data.nodes.length * o.node.size / Pi * 1.5;
			if (R < 50) R = 50;
			theta = 2 * Pi / data.nodes.length;
			for (var i = 0; i < data.nodes.length; i++) {
				data.nodes[i].locate(R, theta * i);
			}

			for (var i = 0; i < data.edges.length; i++) {
				data.edges[i].locate();
			}

			if (o.width == 'auto') {
				Util.attrNS(svg, {
					width: rootGroup.getBBox().width + 20,
				});
			} else {
				Util.attrNS(svg, {
					width: o.width,
				});
			}

			if (o.height == 'auto') {
				Util.attrNS(svg, {
					height: rootGroup.getBBox().height + 20,
				});
			} else {
				Util.attrNS(svg, {
					height: o.height,
				});
			}

			/* get the scale of the diagram */
			var scale = Math.min((svg.getBoundingClientRect().width-10) / rootGroup.getBBox().width, (svg.getBoundingClientRect().height-10) / rootGroup.getBBox().height);

			Util.attrNS(rootGroup, {
				transform: 'translate(' + (rootGroup.getBBox().width/2*scale + 10) + ',' + (rootGroup.getBBox().height/2* scale + 10) + ')' + 
							' scale('+scale+')'

			});

			if (o.width == 'auto') {
				Util.attrNS(svg, {
					width: rootGroup.getBBox().width * scale + 20,
				});
			}

			if (o.height == 'auto') {
				Util.attrNS(svg, {
					height: rootGroup.getBBox().height * scale + 20,
				});
			}
		}

		this.setDefaultOptions(DefaultOptions);
		this.setGlobalOptions(options);
		this.parent = Object.getPrototypeOf(this);
		this.on = function(eventType, listener) {
			var wrapped = function (ev) {
				if (ev.target) {
					p = ev.target;
					while( p != svg && !p.getAttribute('class')) {
						p = p.parentNode;
					}
					if (p != svg) {
						if (p.getAttribute('class') == 'node') {
							ev.currentNode = data.getNodeById(p.getAttribute('id'));
						}
						if (p.getAttribute('class') == 'edge') {
							ev.currentEdge = data.getEdgeById(p.getAttribute('id'));
						}
					}
				}
				listener.apply(this, [ev]);
			}
			this.parent.on(eventType, wrapped);
		}
	}
	
	Interactome.diagram.prototype = new Element();

})()
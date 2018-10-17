(function(){
	var svgNS = "http://www.w3.org/2000/svg";

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
		},
		getBBox: function(el){
			try {
				return el.getBBox();
			} catch (e) {
				return {
					height:0,width:0,
					x:0,y:0
				}
			}
		}
	}

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
		this.getBBox = function(){return Util.getBBox(rootElement);};
	}

	var DefaultOptions = {
		direction: "LR",
		width: 'auto',
		height: 'auto',
		margin: 20,
		background: 'transparent',
		node:{
			padding: 10,
			textSize: 12,
			cursor:"pointer",
			textColor:'blue',
			background: "transparent",
			border: "black",
			borderWidth: 1,
		},
		edge:{
			textSize: 12,
			cursor:"pointer",
			color: "gray",
			width: 1,
			textColor: "black",
		}
	}

	window.SimpleTree = window.SimpleTree || {};

	var node = function(_node){
		var g = Util.elNS('g');
		var r = Util.elNS('rect');
		var t = Util.elNS('text');

		t.textContent = _node.label || '';

		g.appendChild(r);
		g.appendChild(t);

		Util.attrNS(g, {
			class: 'node',
			id: _node.id || '',
		});

		Util.attrNS(r, {
			rx: 5, ry: 5
		});

		// @override
		this._applyOptions = function(o){
			var bbox = Util.getBBox(t);
			var th = bbox.height;
			var tw = bbox.width;
			var rw = o.padding * 2 + bbox.width;
			var rh = o.padding * 2 + bbox.height;
			Util.attrNS(r, {
				width: rw, height: rh,
				fill: o.background,
				'stroke-width': o.borderWidth,
				stroke: o.border,
				cursor: o.cursor,
				x:0, y: -rh/2
			});
			Util.attrNS(t, {
				'font-size':o.textSize,
				fill: o.textColor,
				cursor: o.cursor,
				x: o.padding,y:(o.padding + th -rh/2)
			})			
		};

		this.setRootElement(g);

		// @override
		this._setPosition = function(x,y) {
			Util.attrNS(g, {
				transform: "translate("+x+","+y+")"
			})
		};

		Util.deepExtend(this, _node);

		var privateOptions = {};
		(["textSize", "padding", "margin", "cursor", "textColor", "background", "border", "borderWidth"]).forEach(function(k){
			if (k in _node) {
				privateOptions[k] = _node[k];
			}
		});

		this.setDefaultOptions(DefaultOptions.node);
		this.setPrivateOptions(privateOptions);

		for(var k in _node) {
			if (_node.hasOwnProperty(k) && _node[k] && (_node[k] instanceof Function) && k.startsWith("on")) {
				this.on(k.substr(2), _node[k]);
			}
		}

		this.setWidth = function(w){
			Util.attrNS(t, {
				width: w
			});
		};
	}

	var edge = function(_edge){
		var self = this;
		var g = Util.elNS('g');
		var t = Util.elNS('text');
		var l = Util.elNS('line');

		g.appendChild(l);
		g.appendChild(t);

		Util.attrNS(g, {
			class: 'edge',
			id: _edge.id || ''
		});

		t.textContent = _edge.label || '';

		Util.deepExtend(this, _edge);

		this.setRootElement(g);

		this.setWidth = function(w) {
			var tw = Util.getBBox(t).width
			l.setAttributeNS(null, 'x2', w);
			t.setAttributeNS(null, 'x', (w - tw) / 2); // sequese the text
		}

		// @override
		this._applyOptions = function(o){
			var bbox = Util.getBBox(t);
			var w = bbox.width + 40;
			if (w < 40) w = 60;
			Util.attrNS(g, {
				cursor: o.cursor
			});
			Util.attrNS(t, {
				cursor: o.cursor,
				fill: o.textColor,
				'font-size': o.textSize,
				x: 20, y:-5,
			});
			Util.attrNS(l, {
				stroke: o.color,
				y1:0,y2:0,x1:0
			});
			self.setWidth(w);
		};

		// @override
		this._setPosition = function(x,y){
			Util.attrNS(g, {
				transform: "translate("+x+","+y+")"
			});
		}

		var privateOptions = {};
		["cursor", "color", "width", "textSize", "textColor"].forEach(function(k){
			if (k in _edge) {
				privateOptions[k] = _edge[k];
			}
		});
		this.setDefaultOptions(DefaultOptions.edge),
		this.setPrivateOptions(privateOptions);

		for(var k in _edge) {
			if (_edge.hasOwnProperty(k) && _edge[k] && (_edge[k] instanceof Function) && k.startsWith("on")) {
				this.on(k.substr(2), _edge[k]);
			}
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

	SimpleTree.dataSet = function(nodes, edges) {
		var self = this;
		var dict = {}; var tree = {};  // validate input
		nodes.forEach(function(n){
			if (!('id' in n)) {
				throw new Error("each node should have an id");
			}
			dict[n.id] = n;
		})
		// children -> parent
		edges.forEach(function(e){
			// handle loops
 			if (e.from == e.to) {
 				dict[e.to + "_copy"] = Util.deepExtend({}, dict[e.to]);
 				dict[e.to + "_copy"].id = e.to + "_copy"
 				e.to = e.to + "_copy";
 			}
 			//  get id
 			e.id = e.from + "-" + e.to;
 			if (!(e.from in tree)) {
 				tree[e.from] = {
 					parent: null,
 					id: e.from,
 					node: createElement('node', dict[e.from]),
 					edgeToParent: null,
 					children: []
 				}
 			}
 			if (!(e.to in tree)) {
 				tree[e.to] = {
 					id: e.to,
 					parent: null,
 					node: createElement('node', dict[e.to]),
 					edgeToParent: null,
 					children:[]
 				}
 			}
 			tree[e.to].parent = tree[e.from];
 			tree[e.from].children.push(tree[e.to]);
 			tree[e.to].edgeToParent = createElement('edge', e);
		});

		var count = 0; var roots = [];var root;
		for(var id in tree){
			if (tree[id].parent == null) roots.push(tree[id]); // get all roots, if it is a forest, then all trees will be joined as a single unrooted tree
		}

		var self = this;

		/* check if loops exits in the trees */
		roots.forEach(function(r){
			var colors = {}; // 1: in queue, 2: transversed
			var queue = [r];
			var hasLoop = false;
			LOOP:
			while(queue.length){
				var n = queue.pop();
				colors[n.id] = 2;
				for (var i = 0; i < n.children.length; i++) {
					var child = n.children[i]
					if (child.id in colors){
						if (colors[child.id] == 2) {
							hasLoop = true;
							break LOOP;
						} else continue;
					} else {
						queue.push(child);
						colors[child.id] = 1;
					}
				}
			}
			if (hasLoop) {
				throw new Error("data given is not a tree");
			}
		})

		// set a pseudo root for unrooted data
		if (roots.length > 1) {
			var virtuelRoot = {
				id: "__ROOT__",
				parent: null,
				node: new node({
					id: "__ROOT__",
				}),
				edgeToParent: null,
				children:roots,
				depth: 0
			}
			tree[virtuelRoot.id] = virtuelRoot;
			roots.forEach(function(r){
				r.parent = virtuelRoot;
				r.edgeToParent = new edge({
					from: r.id,
					to: virtuelRoot.id
				})
			})
			root = virtuelRoot
		} else {
			roots[0].depth = 0;
			root = roots[0];
		}

		// get the depth and maxDepth of each node
		var maxDepth = 0;
		var getDepth = function(r){
			r.children.forEach(function(child){
				child.depth = r.depth+1;
				if (child.depth > maxDepth) {
					maxDepth = child.depth;
				}
				if (child.children.length) {
					getDepth(child);
				}
			});
		}

		getDepth(root);

		// pre-order transverse the tree
		this._preorder = [];
		
		var BSF = function(r){
			self._preorder.push(r);
			if (r.children.length) {
				r.children.forEach(function(child){
					BSF(child);
				})
			}
		}

		BSF(root);

		this._leaves = []; // get all the leaves
		this._internals = [];
		this._preorder.forEach(function(n){
			if (n.children.length == 0) {
				self._leaves.push(n);
			} else self._internals.push(n);
		})

		this.getNodeById = function(nodeid){
			if (nodeid in tree) return tree[nodeid].node;
 			else return null;
		}

		this.getEdgeById = function(edgeid){
			var id = edgeid.split('-')[1];
			if (id in tree) {
				return tree[id].edgeToParent;
			} else return null;
		}

		this.getChildNodes = function(nodeid){
			var nodes = [];
			if (nodeid in tree) {
				tree[nodeid].children.forEach(function(child){
					nodes.push(child.node);
				});
				return nodes;
			} else return null;
		}

		this.getParentNode = function(nodeid){
			if(nodeid in tree) return tree[nodeid].parent.node;
			return null;
		}

		this._dimensions = {
			width: maxDepth + 1,
			height: this._leaves.length
		}
	}

	SimpleTree.diagram = function(container, data, options){
		SimpleTree.diagram.prototype = new Element();


		var svg = Util.elNS('svg');
		var rootGroup = Util.elNS('g');
		this.setRootElement(svg);

		var self = this;
		this.svg = svg;

		svg.appendChild(rootGroup)

		while(container.firstChild) {
			container.firstChild.remove();
		}
		container.appendChild(svg);

		svg.id = "treediagram" + Math.random();

		/* append all elements */
		data._preorder.forEach(function(n){
			n.node.appendTo(rootGroup);
			if (n.edgeToParent) {
				n.edgeToParent.appendTo(rootGroup);
			}
		});

		var GridX = [], GridWidth = []; 
		var GridY = [], GridHeight = [];

		var arrange = function(options){
			assignGrid();
			calculateGridSize(options.margin);
			calculateGridCoord(options.direction);
			locateAll(options.direction);
			joinBranches(options);
		}

		var assignGrid = function(){
			data._preorder.forEach(function(n){
				n.gridX = n.depth;
				if (n.edgeToParent) {
					n.edgeToParent.gridX = n.depth;
				}
			});

			for (var i = data._leaves.length - 1; i >= 0; i--) {
				var n = data._leaves[i];
				n.gridY = i;
				if (n.edgeToParent) {
					n.edgeToParent.gridY = i;
				}

			}

			var max,min;
			for (var i = data._internals.length - 1; i >= 0; i--) {
				var n = data._internals[i];
				var min = n.children[0], max = n.children[0];
				n.children.forEach(function(c){
					if (c.gridY > max.gridY) max = c;
					if (c.gridY < min.gridY) min = c;
				})
				n.firstChild = min;
				n.lastChild = max;
				n.gridY = (max.gridY + min.gridY) / 2;
				if (n.edgeToParent) {
					n.edgeToParent.gridY = (max.gridY + min.gridY) / 2;
				}
			}
		}

		var calculateGridSize = function(margin){
			for (var i = 0; i < data._dimensions.width; i++) {
				GridWidth[i] = 0;
			}
			for (var i = 0; i < data._leaves.length; i++) {
				GridHeight[i] = data._leaves[i].node.getBBox().height + margin;
			}
			data._preorder.forEach(function(n){
				var d = n.depth;
				var w = n.node.getBBox().width;
				if (n.edgeToParent) {
					w += n.edgeToParent.getBBox().width;
				}
				if (w > GridWidth[d]) {
					GridWidth[d] = w;
				}
			});
		}

		var calculateGridCoord = function(direction){
			if (direction == "LR") {
				GridX[0] = 0;
				for (var i = 1; i < GridWidth.length + 1; i++) {
					GridX[i] = GridX[i-1] + GridWidth[i-1] + 20;
				}
			} else if (direction == "RL") {
				GridX[GridWidth.length - 1] = 0;
				for (var i = GridWidth.length - 2; i >= 0; i--) {
					GridX[i] = GridX[i+1] + GridWidth[i+1] + 20;
				}
			}
			GridY[0] = 0;
			for (var i = 1; i < GridHeight.length; i++) {
				GridY[i] = GridY[i-1] + GridHeight[i-1];
			}
		}

		var locateAll = function(direction){
			if (direction == "LR") {
				data._preorder.forEach(function(n){
					var nx = 0,ny = 0,ex = 0,ey = 0;
					var w = GridWidth[n.depth];
					nx = w - n.node.getBBox().width + GridX[n.gridX];
					ny = GridY[Math.floor(n.gridY)] + (n.gridY - Math.floor(n.gridY)) * (GridY[Math.ceil(n.gridY)] - GridY[Math.floor(n.gridY)]);
					n.node.locate(nx, ny);
					if (n.edgeToParent) {
						ex = GridX[n.edgeToParent.gridX];
						ey = GridY[Math.floor(n.edgeToParent.gridY)] + (n.edgeToParent.gridY - Math.floor(n.edgeToParent.gridY)) * (GridY[Math.ceil(n.edgeToParent.gridY)] - GridY[Math.floor(n.edgeToParent.gridY)]);
						n.edgeToParent.locate(ex,ey);
						n.edgeToParent.setWidth(w-n.node.getBBox().width)
					}
				})
			} else if (direction == "RL") {
				data._preorder.forEach(function(n){
					var nx = 0,ny = 0,ex = 0,ey = 0;
					var w = GridWidth[n.depth];
					nx = GridX[n.gridX];
					ny = GridY[Math.floor(n.gridY)] + (n.gridY - Math.floor(n.gridY)) * (GridY[Math.ceil(n.gridY)] - GridY[Math.floor(n.gridY)]);
					n.node.locate(nx, ny);
					if (n.edgeToParent) {
						ex = n.node.getBBox().width + GridX[n.edgeToParent.gridX];
						ey = GridY[Math.floor(n.edgeToParent.gridY)] + (n.edgeToParent.gridY - Math.floor(n.edgeToParent.gridY)) * (GridY[Math.ceil(n.edgeToParent.gridY)] - GridY[Math.floor(n.edgeToParent.gridY)]);
						n.edgeToParent.locate(ex,ey);
						n.edgeToParent.setWidth(w-n.node.getBBox().width)
					}
				})
			}
		}

		var joinBranches = function(options){
			// join all the edges and nodes
			for (var i = data._internals.length - 1; i >= 0; i--) {
				var n = data._internals[i];
				if (!n.verticalLine) {
					n.verticalLine = document.createElementNS(svgNS, "line");
 					rootGroup.appendChild(n.verticalLine);
				}
				if (!n.horizontalLine) {
					n.horizontalLine = document.createElementNS(svgNS, "line");
 					rootGroup.appendChild(n.horizontalLine);
				}

 				var x1 = 0,x2 = 0,y1 = 0,y2 = 0;
 				y1 = n.firstChild.node.y; y2 = n.lastChild.node.y;
 				if (options.direction == "LR") {
 					x2 = n.node.x + n.node.getBBox().width;
 					x1 = x2 + 20;
 				} else if (options.direction = "RL") {
 					x2 = n.node.x; x1 = x2 - 20;
 				}

				/*
								(x1,y1)
									|
									|
									|
					(x1, (y1+y1)/2)	|-------------- (x2, (y1+y1)/2)
									|
									|
									|
									|
								(x1,y2)
				*/
				Util.attrNS(n.verticalLine, {
					stroke: options.edge.color,
					'stroke-width': options.edge.width,
					x1: x1, y1: y1,
					x2: x1, y2: y2 
				})
				Util.attrNS(n.horizontalLine, {
					stroke: options.edge.color,
					'stroke-width': options.edge.width,
					x1: x1, y1: (y1+y2)/2,
					x2: x2, y2: (y1+y2)/2,
				})
			}
		}

		// @override
		this._applyOptions = function(options){
			data._preorder.forEach(function(n){
				n.node.setGlobalOptions(options.node);
				if (n.edgeToParent) {
					n.edgeToParent.setGlobalOptions(options.edge);
				}
			});
			arrange(options);

			var w = Util.getBBox(svg).width;
			var h = Util.getBBox(svg).height;
			var x = Util.getBBox(svg).x;
			var y = Util.getBBox(svg).y;

			if (options.width != 'auto') w = options.width;
			if (options.height != 'auto') h = options.height;

			Util.attrNS(svg, {
				width: w, height: h,
				background:options.background,
			});

			/* get the scale of the diagram */
			var scale = Math.min((svg.getBoundingClientRect().width) / rootGroup.getBBox().width, (svg.getBoundingClientRect().height) / rootGroup.getBBox().height);
			if (!isNaN(scale)) {
				var transforms = rootGroup.getAttribute("transform");
				if (transforms) {
					transforms = transforms.split(" ");
				} else transforms = [];
				if (Math.abs(x) > 1e-3 || Math.abs(y) > 1e-3) {
					var replace = false;
					for (var i = 0; i < transforms.length; i++) {
						if(transforms[i].indexOf("translate") != -1) {
							transforms[i] = "translate("+(0-x)+","+(0-y)+")";
							replace = true;
						}
					}
					if (!replace) {
						transforms.push("translate("+(0-x)+","+(0-y)+")");
					}
					Util.attrNS(rootGroup, {
						transform: transforms.join(" ")
					})
				} else {
					var replace = false;
					for (var i = 0; i < transforms.length; i++) {
						if(transforms[i].indexOf("scale") != -1) {
							transforms[i] = "scale("+scale+")";
							replace = true;
						}
					}
					if (!replace) {
						transforms.push("scale("+scale+")");
					}
					Util.attrNS(rootGroup, {
						transform: transforms.join(" ")
					})
				}
			}
		}

		this.setDefaultOptions(DefaultOptions);
		this.setGlobalOptions(options);

		var containerBox = {
			width: container.clientWidth,
			height: container.clientHeight
		}

		var rootGroupBox = Util.getBBox(rootGroup);

		this.zoom = function(scale){
			rootGroupBox = Util.getBBox(rootGroup);
			if (scale == 'auto') {
				scale = Math.max(rootGroupBox.width / containerBox.width, rootGroupBox.height / containerBox.height) * 1.2;
			} else if (isNaN(scale)) {
				console.error("scale need to be a number or 'auto' in SimpleTree.diagram.zoom(scale) method");
				return;
			}
			rootGroup.setAttributeNS(null, 'transform', 'scale(' + (1/scale) + ')');
		}

		this.parent = Object.getPrototypeOf(this);

		this.on = function(eventType, listener) {
			var wrapped = function(ev){
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

	SimpleTree.diagram.prototype = new Element();
})()
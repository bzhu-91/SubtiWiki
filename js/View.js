const svgNS = "http://www.w3.org/2000/svg";
const htmlNS = "http://www.w3.org/1999/xhtml";

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
		if (options instanceof Object) {
			for(var i in options){
				if (options.hasOwnProperty(i)) {
					if (options[i] == null) {
						dom.removeAttribute(i);
					} else {
						dom.setAttributeNS(null, i, options[i]);
					}
				}
			}
		} else if (typeof options == "string") {
			return dom.getAttribute(options);
		}
	},
	attr: function(dom, options){
		if (options instanceof Object) {
			for(var i in options){
				if (options.hasOwnProperty(i)) {
					if (options[i] == null) {
						dom.removeAttribute(i);
					} else {
						dom.setAttribute(i, options[i]);
					}
				}
			}
		} else if (typeof options == "string") {
			return dom.getAttribute(options);
		}
	},
	elNS: function(tag, attrs){
		var d = document.createElementNS(svgNS, tag);
		if (attrs) {
			Util.attrNS(d, attrs);
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
	},
	appendAll: function(parent, children) {
		for (var i = 0; i < children.length; i++) {
			parent.appendChild(children[i]);
		}
	},
	getTransformMatrix: function(dom) {
		var transform = dom.getAttribute("transform").replace(/ /g,"");
		var vector = transform.substring(transform.indexOf("(") + 1,transform.indexOf(")")).split(",");
		for (var i = 0; i < vector.length; i++) {
			vector[i] = Number(vector[i]);
		}
		return vector;
	},
	getGUID: function() {
    	var S4 = function() {
       		return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
    	};
    	return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
	},
	addClassNS: function (dom, className) {
		var oldClass = dom.getAttribute("class").trim().replace(/\s{2,}/i, " ");
		if (oldClass.trim()) {
			oldClass = oldClass.split(" ");
			if (oldClass.indexOf(className) == -1) {
				oldClass.push(className);
				Util.attrNS(dom, oldClass.join(" "));
			}
		}
	},
	removeClassNS: function (dom, className) {
		var oldClass = dom.getAttribute("class").trim().replace(/\s{2,}/i, " ");
		if (oldClass.trim()) {
			oldClass = oldClass.split(" ");
			var idx = oldClass.indexOf(className);
			if (idx != -1) {
				oldClass.splice(idx, 1);
				Util.attrNS(dom, oldClass.join(" "));
			}
		}
	}
}

function View () {
	this.listeners = [];
	this.links = [];
	this.uuid = Util.getGUID();
	this.x = this.y = 0;
	this.parent = null;
}

View.prototype.createView = function() {
	var self = this;
	this.view = this._createView();
	this.view.ctrl = this;
	Util.attrNS(this.view, {
		uuid: self.uuid
	});
	delete View.prototype.setView;
}

View.prototype._createView = function() {}

View.prototype.on = function(eventType, listener) {
	if (!(eventType in this.listeners)) {
		this.listeners[eventType] = [];
	}
	this.listeners[eventType].push(listener);
	this.view.addEventListener(eventType, listener);
}

View.prototype.off = function(eventType, listener) {
	if (listener) {
		var idx = (this.listeners[eventType] || []).indexOf(listener);
		if (idx != -1) {
			this.listeners[eventType] = this.listeners[eventType].splice(idx, 1);
			this.view.removeEventListener(eventType, listener);
		}
	} else {
		(this.listeners[eventType] || []).forEach(function(listener){
			this.view.removeEventListener(eventType, listener);
		})
	}
}

View.prototype.setView = function(v){
	this.view = v;
	v.ctrl = this;
	this.uuid = v.getAttribute("uuid");
}

View.prototype.layout = function() {
	this._layout();
}

View.prototype._layout = function() {}

View.prototype.position = function(x,y,anchor) {
	// translate the position to center position
	var bbox = this.getBBox();
	if (anchor) {
		if (anchor.substr(0, 4) == "left") {
			x -= bbox.x;
		} else if (anchor.substr(0,5) == "right") {
			x -= bbox.width + bbox.x; 
		}
		if (anchor.substr(-5) == "upper") {
			y -= bbox.y;
		} else if (anchor.substr(-5) == "lower") {
			y -= bbox.height + bbox.y;
		}
	}
	this.x = x; this.y = y;
	this._position(x,y);

	// update links;
	this.links.forEach(function(l){
		l.layout();
	});
}

View.prototype._position = function(x,y) {
	switch (this.view.tagName) {
		case "g":
			var vector = Util.getTransformMatrix(this.view);
			vector[4] = x, vector[5] = y;
			this.attr({
				transform: "matrix(" + vector.join(",") + ")"
			});
			break;
		case "ellipse":
			this.attr({
				cx:x,cy:y
			});
			break;
		default:
			this.attr({
				x:x,y:y
			});
	}
}

View.prototype.move = function(dx, dy, sync){
	this.x += dx; this.y += dy;
	this._position(this.x, this.y);

	if (arguments.length == 2) {
		sync = true;
	}

	// lock the locked elements
	
	if (this.enableLock) {
		var allLocked = this.view.querySelectorAll("[type='locked']");
		if (allLocked.length) {
			for (var i = 0; i < allLocked.length; i++) {
				allLocked[i].ctrl.move(-dx,-dy, false);
			}
		}
	}

	if (sync && this.syncGroup) {
		this.syncGroup.forEach(function(m){
			m.move(dx,dy,false);
		})
	}

	// update links;
	this.links.forEach(function(l){
		l.layout();
	});
}

View.prototype.appendTo = function(p) {
	if (p instanceof View) {
		// createView
		if (!this.view) this.createView();
		this._appendTo();
		if (p.getChildViewContainer) {
			p.getChildViewContainer().appendChild(this.view);
		} else p.view.appendChild(this.view);
		this.parent = p;
	} else console.error("can only appendTo to View instance");
	this.layout();
}

View.prototype.getBBox = function() {
	return Util.getBBox(this.view);
}

View.prototype.hide = function() {
	Util.attrNS(this.view, {
		visibility:'hidden'
	});
}

View.prototype.show = function() {
	Util.attrNS(this.view, {
		visibility: 'show'
	})
}

View.prototype.remove = function() {
	if (this.view.remove) {
		this.view.remove();
	} else {
		this.parent.view.removeChild(view);
	}
	this.parent = null;
}

View.prototype.attr = function(obj) {
	return Util.attrNS(this.view, obj);
}

View.prototype.setState = function(state) {
	if (this._setState) {
		this._setState(state);
	}
}
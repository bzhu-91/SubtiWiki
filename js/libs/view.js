/*const*/ var svgNS = "http://www.w3.org/2000/svg";
/*const*/ var htmlNS = "http://www.w3.org/1999/xhtml";

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
	setTransformMatrix: function(dom,arr) {
		Util.attrNS(dom, {
			transform: "matrix(" + arr.join(",") + ")"
		});
	},
	getGUID: function() {
    	var S4 = function() {
       		return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
    	};
    	return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
	},
	addClassNS: function (dom, className) {
		var oldClass;
		if (dom.getAttribute("class")){
			oldClass = dom.getAttribute("class").trim().replace(/\s{2,}/i, " ");
			if (oldClass.trim()) {
				oldClass = oldClass.split(" ");
			}
		} else oldClass = [];
		if (oldClass.indexOf(className) == -1) {
			oldClass.push(className);
			Util.attrNS(dom, {
				class: oldClass.join(" ")
			});
		}
	},
	removeClassNS: function (dom, className) {
		if (dom.getAttribute("class")) {
			var oldClass = dom.getAttribute("class").trim().replace(/\s{2,}/i, " ");
			if (oldClass.trim()) {
				oldClass = oldClass.split(" ");
				var idx = oldClass.indexOf(className);
				if (idx != -1) {
					oldClass.splice(idx, 1);
					Util.attrNS(dom, {
						class: oldClass.join(" ")
					});
				}
			}
		}
	}
}

/**
 * _name: protected or private attributes
 * __method: abstract method
 */
var View = View || function () {
	this._listeners = [];
	this._links = [];
	this._locked = [];
	this._synced = [];
	this._state = null;

	this.visibility = true;
	this.uuid = Util.getGUID();
	this.x = this.y = 0;
	this.parent = null;
	this.enableLock = true;
}

View.prototype.createView = function () {
	var self = this;
	// to call method in the child class, this should be used instead of self!!!!
	self.view = this.__createView();
	self.view.wrapper = self;

	// set the transform before the view is located
	Util.attrNS(self.view, {
		uuid: self.uuid,
	});
	Util.setTransformMatrix(self.view, [1,0,0,1,0,0]); // set all the init transformation
}

View.prototype.on = function (eventType, listener) {
	var self = this;
	if (!(eventType in self._listeners)) {
		self._listeners[eventType] = [];
	}
	self._listeners[eventType].push(listener);
	self.view.addEventListener(eventType, listener);
}

View.prototype.off = function (eventType, listener) {
	var self = this;
	if (listener) {
		var idx = (self._listeners[eventType] || []).indexOf(listener);
		if (idx > -1) {
			self._listeners[eventType].splice(idx, 1);
			self.view.removeEventListener(eventType, listener);
		}
	} else {
		(self._listeners[eventType] || []).forEach(function(l){
			self.view.removeEventListener(eventType, l);
		});
	}
}

View.prototype.layout = function () {
	this.__layout();
}

View.prototype.__layout = function () {}

View.prototype.position = function (x,y,anchor) {
	var self = this;
	var bbox = self.getBBox();
	if (anchor) {
		if (anchor.substr(0, 4) == "left") {
			x -= bbox.x;
		} else if (anchor.substr(0,5) == "right") {
			x -= bbox.width + bbox.x; 
		}
		if (anchor.substr(-3) == "top") {
			y -= bbox.y;
		} else if (anchor.substr(-6) == "bottom") {
			y -= bbox.height + bbox.y;
		}
	}
	self.x = x; self.y = y;
	// call a method which could be overriden by the child class
	this.__position (x,y);

	self._links.forEach(function(link){
		link.layout();
	});
}

View.prototype.__position = function (x,y){
	var self = this;
	switch (self.view.tagName) {
		case "g":
			var vector = Util.getTransformMatrix(self.view);
			vector[4] = x, vector[5] = y;
			Util.attrNS(self.view, {
				transform: "matrix(" + vector.join(",") + ")"
			});
			break;
		case "ellipse":
			Util.attrNS(self.view,{
				cx: x, cy: y
			})
			break;
		default:
			Util.attrNS(self.view, {
				x: x, y: y
			});
	}
}

View.prototype.move = function (dx,dy, sync) {
	var self = this;
	this.__position(self.x + dx, self.y + dy);
	
	if (arguments.length == 2) {
		sync = true;
	}

	self.x += dx; self.y += dy;

	// lock the locked elements
	if (self.enableLock) {
		if (self._locked.length) {
			for (var i = 0; i < self._locked.length; i++) {
				self._locked[i].move(-dx,-dy, false);
			}
		}
	}

	if (sync && self._synced.length) {
		self._synced.forEach(function(m){
			m.move(dx,dy,false);
		})
	}

	// update links;
	self._links.forEach(function(l){
		l.layout();
	});
}

View.prototype.addToSyncGroup = function (view) {
	var self = this;
	if (self._synced.indexOf(view) == -1) {
		self._synced.pusH(view);
		// need to write to the view
		var uuids = (self.attr("sync") || "").split(",");
		uuids.push(view.uuid);
		self.attr({
			sync: uuids.join(",")
		});
	}
}

View.prototype.removeFromSyncGroup = function (view) {
	var self = this;
	var idx = self._synced.indexOf(view); 
	if (idx > -1){
		self._synced.splice(idx, 1);
		// rewrite to the view
		var uuids = self.attr("sync") ? self.attr("sync").split(",") : [];
		uuids.splice(uuids.indexOf(view),1);
		self.attr({
			sync: uuids.join(",")
		});
	}
}

View.prototype.getSyncGroup = function () {
	return this._synced;
}

View.prototype.clearSyncGroup = function () {
	this._synced = [];
	this.attr({
		sync: null
	});
}

View.prototype.setSyncGroup = function (views) {
	var uuids = [];
	views.forEach(function(view){
		uuids.push(view.uuid);
	});
	this.attr({
		sync: uuids.join(",")
	});
	this._synced = views;
}

View.prototype.addToLockGroup = function (view) {
	if (this._locked.indexOf(view) == -1){
		this._locked.push(view);
		var uuids = this.attr("lock") ? this.attr("lock").split(",") : [];
		uuids.push(view.uuid);
		this.attr({
			lock: uuids.join(",")
		});
	}
}

View.prototype.removeFromLockGroup = function (view) {
	var idx = this._locked.indexOf(view);
	if (idx != -1){
		this._locked.splice(idx,1);
		var uuids = this.attr("lock") ? this.attr("lock").split(",") : [];
		var idx1 = uuids.indexOf(view.uuid);
		if (idx1 != -1) {
			uuids.splice(idx1,1);
		}
		this.attr({
			lock: uuids.length ? uuids.join(",") : null
		})
	}
}

View.prototype.getLockGroup = function () {
	return this._locked;
}

View.prototype.setLockGroup = function (views) {
	var uuids = [];
	views.forEach(function(view){
		uuids.push(view.uuid);
	});
	this.attr({
		lock: uuids.join(",")
	});
	this._locked = views;
}

View.prototype.clearLockGroup = function () {
	this._locked = [];
	this.attr({
		lock: null
	});
}

View.prototype.isInLockGroup = function (view) {
	return this._locked.indexOf(view) != -1;
}

View.prototype.addLink = function (view) {
	var self = this;
	if (self._links.indexOf(view) == -1){
		self._links.push(view);
	}
} 

View.prototype.removeLink = function (view) {
	var self = this;
	var idx = self._links.indexOf(view);
	if (idx > -1){
		self._links.splice(idx, 1);
	}
}

View.prototype.appendTo = function (parent) {
	var self = this;
	if (parent instanceof View) {
		if (!self.view) self.createView();
		if (parent.getChildViewContainer) {
			parent.getChildViewContainer().appendChild(self.view);
		} else parent.view.appendChild(self.view);
		self.parent = parent;
		// call child class method
		this.__assemble();
	} else console.error(parent, "is not a View instance");
	self.layout();
}

View.prototype.__assemble = function () {}

View.prototype.getBBox = function () {
	var self = this;
	return self.view.getBBox() || {
		x: 0, y: 0, height: 0, width: 0,
		left: 0, top: 0
	};
}

View.prototype.hide = function () {
	var self = this;
	Util.attrNS(self.view, {
		visibility: "hidden"
	});
	self.visibility = false;
}

View.prototype.show = function () {
	var self = this;
	Util.attrNS(self.view, {
		visibility: "show"
	});
	self.visibility = true;
}

View.prototype.remove = function () {
	var self = this;
	if (self.view.remove) {
		self.view.remove();
	} else if (self.parent && self.parent.view.removeChild) {
		self.parent.view.removeChild(self.view);
	}
	self.parent = null;
}

View.prototype.setState = function (state) {
	var self = this;
	self._state = state;
	Util.attrNS(self.view, {
		state: state
	});
	self.__setState(state);
}

View.prototype.getSate = function () {
	return this._state;
}

View.prototype.attr = function (obj){
	return Util.attrNS(this.view, obj);
}
(function(){
	window.SomeLightBox = window.SomeLightBox || function (options) {
		this.options = options;
		this.frame;
		this.background;
		this.closeButton;
		this.view;
		this.dismissListeners = [];
		this.firstFocus;
		this.dismissEvent;
		this._overflow;
	
		this.createView();
	}
	
	SomeLightBox.extend = function (a, b, allowDel){
		for(var prop in b){
			if (b.hasOwnProperty(prop)) {
				if (b[prop] && b[prop].constructor == Object) {
					if (a[prop] === undefined) a[prop] = {};
					if (a[prop].constructor == Object) extend(a[prop],b [prop]);
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
	}
		
	SomeLightBox.prototype.getOptions = function () {
		var style = {};
		style.frame = {
			position:"absolute",
			display:"block",
			left:"0px",
			top:"0px",
			height:"100%",
			width:"100%",
			zIndex:"600",
		};
		style.background = {
			position:'absolute',
			display:'block',
			width:'100%',
			height:'100%',
			background:'black',
			opacity:'0.8',
			filter:'alpha(opacity:80)',
			MozOpacity:'0.8',
			KhtmlOpacity:'0.8',
			left:'0px',
			top:'0px'
		}
		style.foreground = {
			position: "absolute",
			display: "block",
			overflow: "auto"
		}
		style.closeButton = {
			width : "20px",
			height : "20px",
			background : "#dedede",
			opacity : '0.4',
			filter : 'alpha(opacity:40)',
			MozOpacity : '0.4',
			KhtmlOpacity : '0.4',
			textAlign : "center",
			color : "#333",
			position : "absolute",
			cursor : "pointer"
		}
		var defaultOptions = {
			style: style,
			dismissOnBkgClick: true,
			closeButton: true,
			width: "auto",
			height: "auto",
			background: "white",
			maxHeight: "80%",
			autoForm: false,
			animation: true
		}
		return SomeLightBox.extend(defaultOptions, this.options);
	}
	
	SomeLightBox.prototype.createView = function () {
		var o = this.getOptions(), self = this;
		self.frame = document.createElement("div");
		for(var i in o.style.frame) {
			if (o.style.frame.hasOwnProperty(i)) {
				self.frame.style[i] = o.style.frame[i]
			}
		}
		self.background = document.createElement("div");
		for(var i in o.style.background) {
			if (o.style.background.hasOwnProperty(i)) {
				self.background.style[i] = o.style.background[i]
			}
		}
		self.foreground = document.createElement("div");
		for(var i in o.style.foreground) {
			if (o.style.foreground.hasOwnProperty(i)) {
				self.foreground.style[i] = o.style.foreground[i]
			}
		}
		self.foreground.style.background = o.background;
		self.closeButton = document.createElement("div");
		with(self.closeButton) {
			innerHTML = "x";
			title = "close"
		}
		for(var i in o.style.closeButton) {
			self.closeButton.style[i] = o.style.closeButton[i];
		}
		self.closeButton.onclick = function() {
			self.dismiss();
		}
		self.closeButton.onmouseover = function() {
			with(self.closeButton.style) {
				opacity = '0.8';
				filter = 'alpha(opacity:80)';
				MozOpacity = '0.8';
				KhtmlOpacity = '0.8';
			}
		}
		self.closeButton.onmouseout = function() {
			with(self.closeButton.style) {
				opacity = '0.4';
				filter = 'alpha(opacity:40)';
				MozOpacity = '0.4';
				KhtmlOpacity = '0.4';
			}
		}
		self.background.onclick = function () {
			if(o.dismissOnBkgClick) self.dismiss();
		};
		self.frame.appendChild(self.background);
		self.frame.appendChild(self.foreground);
		self.frame.style.display = "none";
		document.body.appendChild(self.frame);
		if(o.closeButton && o.dismissOnBkgClick) self.frame.appendChild(self.closeButton);
	}
	
	// in chrome 73, the scroll function is not working
	// so use the js to patch it up
	SomeLightBox.prototype.patchScroll = function () {
		var self = this;
		if (self.foreground.clientHeight >= self.foreground.scrollHeight) {
			return;
		}
		self._ = {}
		var measure = self._.measure || function (dom) {
			return {
				height: dom.clientHeight,
				contentHeight: dom.scrollHeight
			};
		}
		self._.measure = measure;
		var createScrollBar = self._createScrollBar || function (height) {
			var bar = document.createElement("div");
			bar.style = "width: 13px; background: #aaa; position: abosolute;";
			bar.style.height = height + "px";
			var box = document.createElement("div");
			box.style = "width: 8px; height: 40px; background: #555; border-radius: 3px; position: absolute; top:0;left:3px";
			var wrapper = document.createElement("div");
			wrapper.style = "position: absolute; right:0; top:0";
			wrapper.appendChild(bar);
			wrapper.appendChild(box);
	
			var dragStart, top;
			wrapper.addEventListener("mousedown", function(e){
				dragStart = e;
				top = parseInt(window.getComputedStyle(box, null).getPropertyValue("top"));
			});
			document.body.addEventListener("mousemove", function(e){
				if (dragStart) {
					var dy = e.clientY - dragStart.clientY;
					if (top + dy < 0) {
						box.style.top = "0px";
					} else if (top + dy + 40 > height) {
						box.style.top = height - 40 + "px";
					} else {
						box.style.top = top + dy + "px";
					}
					if (wrapper.onchange) wrapper.onchange(parseInt(window.getComputedStyle(box, null).getPropertyValue("top"))/(height-40));
				}
			});
			wrapper.set = function (percentage) {
				box.style.top = (height - 40) * percentage + "px";
			}
			document.body.addEventListener("mouseup", function(e){
				dragStart = null;
			})
			return wrapper;
		}
		self._.createScrollBar = createScrollBar;
		
		var patch_el = self._.patch_el || function (dom) {
			var mm = measure(dom);
			var wrapper = createScrollBar(mm.height);
			dom.appendChild(wrapper)
			wrapper.onchange = function (pos) {
				self.view.style.top = - pos * (mm.contentHeight - mm.height) + "px";
			}
			var current = 0;
			var scrollEvent = function (e) {
				var dir = Math.sign(e.deltaY);
				var top = current + dir * 100;
				top = Math.max(0,top);
				top = Math.min(top, mm.contentHeight - mm.height);
				current = top;
				wrapper.set(top/(mm.contentHeight - mm.height));
				self.view.style.top = - top + "px";
	
			}
			window.addEventListener("wheel", scrollEvent);
	
			self.ondismiss(function(){
				window.removeEventListener("wheel", scrollEvent);
				return true;
			})
		}
		self._.patch_el = patch_el;
		
		self.view.style.position = "relative";
		self.foreground.style.paddingRight = "30px"; // add extra padding for the scrollbar
		self.foreground.style.overflow = "hidden";
	
		var pos = self.resize();
		self.position(pos.left, pos.top, false, null);
		patch_el(self.foreground);
	
		
	}
	
	SomeLightBox.prototype.resize = function () {
		var o = this.getOptions(), self = this;
		// measure the frame and view
		var h,w,h1,w1;
		if (self.frame.getBoundingClientRect) {
			h = self.frame.getBoundingClientRect().height;
			w = self.frame.getBoundingClientRect().width;
		} else {
			h = self.frame.offsetHeight, w = self.frame.offsetWidth;
		}
		if (o.width != "fitToContent") {
			if (!isNaN(o.width)) o.width += "px";
			if ((o.width + "").endsWith("px")) {
				self.foreground.style.width = o.width;
			}
			if ((o.width + "").endsWith("%")) {
				self.foreground.style.width = w * parseInt(o.width) / 100.0 + "px"
			}
		}
		if (o.maxWidth) {
			if (!isNaN(o.maxWidth)) o.maxWidth += "px";
			if ((o.maxWidth + "").endsWith("px")) {
				self.foreground.style.maxWidth = o.maxWidth;
			}
			if ((o.maxWidth + "").endsWith("%")) {
				self.foreground.style.maxWidth = w * parseInt(o.maxWidth) / 100.0 + "px"
			}
		}
		if (o.height != "fitToContent") {
			if (!isNaN(o.height)) o.height += "px";
			if ((o.height + "").endsWith("px")) {
				self.foreground.style.height = o.height;
			}
			if ((o.height + "").endsWith("%")) {
				self.foreground.style.height = w * parseInt(o.height) / 100.0 + "px"
			}
		}
		if (o.maxHeight) {
			if (!isNaN(o.maxHeight)) o.maxHeight += "px";
			if ((o.maxHeight + "").endsWith("px")) {
				self.foreground.style.maxHeight = o.maxHeight;
			}
			if ((o.maxHeight + "").endsWith("%")) {
				self.foreground.style.maxHeight = h * parseInt(o.maxHeight) / 100.0 + "px"
			}
		}
		if (self.foreground.getBoundingClientRect) {
			h1 = self.foreground.getBoundingClientRect().height;
			w1 = self.foreground.getBoundingClientRect().width;
		} else {
			h1 = self.foreground.offsetHeight, w1 = self.foreground.offsetWidth;
		}
		var top = ((h - h1) >> 1) * 0.7;
		var left = (w - w1) >> 1;
		return {
			top: top,
			left: left
		}	
	}
	
	SomeLightBox.prototype.position = function (left, top, animation, callback) {
		var o = this.getOptions(), self = this;
		if (!animation) {
			self.foreground.style.left = left + "px";
			self.foreground.style.top = top + "px";
			self.foreground.style.opacity  = 1;
			if (o.closeButton) {
				self.closeButton.style.top = top + "px";
				self.closeButton.style.left = left + self.foreground.clientWidth + "px";
			}
			if (callback) callback();
			return;
		}
		var pos = top + 50;
		var op = 0;
		self.foreground.style.left = left + "px";
		self.foreground.style.top = pos + "px";
		
		self.foreground.style.opacity = op;
		if (o.closeButton) {
			self.closeButton.style.top = pos + "px";
			self.closeButton.style.left = left + self.foreground.clientWidth + "px";
		}
		var duration = 20; // 100 ms
		/* animation type, default enter from bottom with opacity change */
		var move = function(){
			if (pos <= top) {
				clearInterval(slideIn);
				if (callback) callback();
			} else {
				var v = 50 / duration;
				pos -= v;
				var vop = 1 / duration;
				op += vop;
				self.foreground.style.top = pos + "px";
				self.foreground.style.opacity = op;
				if (o.closeButton) {
					self.closeButton.style.top = pos + "px";
				}
			}
		}
		var slideIn = window.setInterval(move, 1);
	}
	
	SomeLightBox.prototype.ondismiss = function(func){
		this.dismissListeners.push(func);
		return this;
	}
	
	SomeLightBox.prototype.setFocus = function (el) {
		this.firstFocus = el;
	}
	
	SomeLightBox.prototype.dismiss = function (callback) {
		var allowDismiss = true, self = this, o = this.getOptions();
		if (self.dismissListeners.length > 0) {
			self.dismissListeners.forEach(function(f){
				if(f.call(self, self.dismissEvent) === false){
					allowDismiss = false;
				}
			})
		}
		if (allowDismiss) {
			/* dissovle out animation */
			if (!o.animation) {
				self.frame.style.display = "none";
				document.body.style["overflow"] = self._overflow;
				return true;
			}
			var op = 1;
			var move = function(){
				if (op <= 1e-3) {
					clearInterval(animation);
					self.frame.style.display = "none";
					self.frame.style.opacity = 1;
					document.body.style["overflow"] = self._overflow;
					if (callback) callback();
				} else {
					op -= op * 0.2;
					self.frame.style.opacity = op;
				}
			}
			var animation = setInterval(move, 2);
			return true;
		}
		return false;
	}
	
	SomeLightBox.prototype.show = function (arg1, arg2) {
		var o = this.getOptions(), self = this;
		var animation, callback;
		/* set dismiss hot key */
		document.body.onkeydown = function (event) {
			var keyCode = ("which" in event) ? event.which : event.keyCode;
			if(keyCode == 27 && o.dismissOnBkgClick) self.dismiss();
		};
		
		if (!self.firstFocus) {
			var inputs = self.view.getElementsByTagName("input");
			if (inputs.length) inputs[0].focus();
			else {
				var btns = self.view.getElementsByTagName("button");
				if (btns.length) btns[0].focus();
			}
		} else {
			self.firstFocus.focus();
		}
		/* freeze the body element */
		self.frame.style.top = window.scrollY + "px";
		if ("addEventListener" in self.frame) {
			self.frame.addEventListener("DOMMouseScroll", function(ev){
				ev.preventDefault();
			},false);
			self.frame.addEventListener("mousewheel", function(ev){
				ev.preventDefault();
			},false);		
		}
		if ("attachEvent" in self.frame) {
			self.frame.attachEvent("onmousewheel", function(ev){
				ev.preventDefault();
			});
		}
		if (arguments.length == 0) {
			animation = o.animation;
		} else if (arguments.length == 1) {
			if (arg1 === true || arg1 == false) animation = arg;
			if (arg1 instanceof Function) {
				animation = o.animation;
				callback = arg1;
			}
		} else {
			animation = arg1;
			callback = arg2;
		}
		
		self.frame.style.display = "block";
		if (!animation) {
			var pos = self.resize();
			self.position(pos.left, pos.top, animation, callback);
			if (callback) callback();
			return;
		}
		/* dissovle in animation */
		var op = 0;
		self.frame.style.opacity = op;
		self.foreground.style.opacity = 0;
		
		var dissolveIn = setInterval(move, 1);
		function move(){
			if (op >= 1) {
				clearInterval(dissolveIn);
				var pos = self.resize();
				self.position(pos.left, pos.top, animation, callback);
			} else {
				op += 0.05;
				self.frame.style.opacity = op;
			}
		}
	}
	
	SomeLightBox.prototype.loadById = function (id) {
		var origin = document.getElementById(id);
		if (origin) {
			this.load(origin);
		} else {
			console.error("DOM element not found");
		}
		return this;
	}
	
	SomeLightBox.prototype.replaceById = function (id) {
		this.view.remove();
		this.loadById(id);
		return this;
	};
	
	SomeLightBox.prototype.load = function (content) {
		var self = this, o = self.getOptions();
		self.view = content;
		if (window.getComputedStyle(self.view, null).getPropertyValue("display") == "none") {
			self.view.style.display = "block";
		}
		self.foreground.appendChild(self.view);
		if(o.autoForm) {
			var form = self.foreground.getElementsByTagName("form");
			if (form.length == 1) {
				form = form[0];
				form.addEventListener("submit", function(ev) {
					ev.preventDefault();
					var data = {};
					for (var i = 0; i < form.elements.length; i++) {
						var el = form.elements[i]
						var name = el.name;
						switch (el.type) {
							case "checkbox":
								data[name] = el.checked;
								break;
							case "submit":
								continue;
							default:
								data[name] = el.value;								
						}
					}
					self.dismissEvent = {
						formData: data,
						formElements: form.elements
					};
					if(self.dismiss() !== false) form.reset();
					self.dismissEvent = null;
				})
			} else console.info("autoForm can only handle one form");
		}
		return this;
	};
	
	SomeLightBox.prototype.replace = function (content) {
		if (this.view) {
			this.view.remove();
		}
		this.load(content);
		return this;
	};
	
	SomeLightBox.prototype.destroy = function () {
		document.body.removeChild(this.frame);
	}
	
	SomeLightBox.alert = function (titleItem, msgItem, itemConfirm, itemCancel, themeColor, animation) {
		if (animation == undefined) {
			animation = true;
		}
		if (arguments.length == 1) {
			var obj = titleItem;
			titleItem = obj.title;
			msgItem = obj.message;
			itemConfirm = obj.confirm;
			itemCancel = obj.cancel;
			themeColor = obj.theme;
			animation = obj.animation;
		}
		var l = new SomeLightBox({
			width: "400px",
			height: "fitToContent",
			closeButton: false,
			animation: !animation
		});
		l.dismiss = l.destroy;
		themeColor = themeColor || '#1976d2'
		itemConfirm = itemConfirm || 1;
		var dialog = document.createElement("div");
		with(dialog.style) {
			background = "#fff";
			padding = "0 1em";
			borderRadius = "3px";
			width = "400px";
			textAlign = "left";
		}
		dialog.innerHTML ="" ;
		if (titleItem) {
			if (typeof titleItem === "string") {
				dialog.innerHTML += "<div style='color: " + themeColor + ";font-weight: bold; margin:1em 0; display: inline-block; padding: 0.1px; font-size: 1.15em'>" + titleItem + "</div><hr style='margin:0'/>";
			} else if (titleItem instanceof Object) {
				var color = titleItem.color || themeColor;
				var title = titleItem.title;
				dialog.innerHTML += "<div style='color: " + color + ";font-weight: bold; margin:1em 0; display: inline-block; padding: 0.1px; font-size: 1.15em'>" + title + "</div><hr style='margin:0'/>";
			}
		}
		if (msgItem) {
			if (typeof msgItem === "string") {
				dialog.innerHTML += "<div style='margin: 1em 0; padding: 0.1px; display: inline-block'>" + msgItem + "</div><br />";
			} else if (msgItem instanceof Object) {
				var color = msgItem.color || "#333";
				var msg = msgItem.message;
				dialog.innerHTML += "<div style='color:" + color + "margin: 1em 0; padding: 0.1px; display: inline-block'>" + msg + "</div><br />";
			}
			
		}
		if (itemConfirm || itemCancel) {
			var buttonHolder = document.createElement("div");
			with(buttonHolder.style) {
				padding = "0.1px";
				margin = "1em 0";
				display = "inline-block";
				float = "right";
			}
			dialog.appendChild(buttonHolder);
			if (itemConfirm) {
				var btConfirm = document.createElement("button");
				with(btConfirm.style){
					color = "white";
					background = themeColor;
					padding = "5px 10px";
					borderRadius = "3px";
					border = "none";
					marginLeft = "10px";
					clear = "both";
				}
				if (itemConfirm instanceof Function || !isNaN(itemConfirm)) {
					btConfirm.innerHTML = "OKAY";
				} else {
					if ("title" in itemConfirm) btConfirm.innerHTML = itemConfirm.title.toUpperCase();
					else btConfirm.innerHTML = "OKAY";
					if ("color" in itemConfirm) btConfirm.style.background = itemConfirm.color;
				}
				btConfirm.addEventListener("click",function(ev) {
					if(itemConfirm instanceof Function) itemConfirm(ev);
					else if (itemConfirm instanceof Object && ("onclick" in itemConfirm) && (itemConfirm.onclick instanceof Function)) itemConfirm.onclick();
					l.dismiss();
				});
				buttonHolder.appendChild(btConfirm);	
			}
			if (itemCancel) {
				var btCancel = document.createElement("button");
				buttonHolder.appendChild(btCancel);
				with(btCancel.style){
					color = "white";
					background = themeColor;
					padding = "5px 10px";
					borderRadius = "3px";
					border = "none";
					marginLeft = "10px";
				}
				if (itemCancel instanceof Function || !isNaN(itemCancel)) {
					btCancel.innerHTML = "CANCEL";
				} else if(itemCancel instanceof Object) {
					if("title" in itemCancel) btCancel.innerHTML = itemCancel.title.toUpperCase();
					else btCancel.innerHTML = "CANCEL";
					if ("color" in itemCancel) btCancel.style.background = itemCancel.color;
				}
				btCancel.addEventListener("click",function(ev) {
					if(itemCancel instanceof Function) itemCancel(ev);
					else if (itemCancel instanceof Object && ("onclick" in itemCancel) && (itemCancel.onclick instanceof Function)) itemCancel.onclick();
					l.dismiss();
				});
			}
			l.setFocus(btCancel);
		}
		l.load(dialog);
		l.show();
		return l;
	};
	
	SomeLightBox.error = function(msg, animation) {
		return SomeLightBox.alert({
			title: "Error",
			color: "red",
		}, msg, {
			color: "gray"
		}, null, null, null, !animation);
	};
	
	SomeLightBox.prompt = function(title, val, callback, themeColor) {
		var l = new SomeLightBox({
			width: "400px",
			height: "fitToContent",
			closeButton: false,
			dismissOnBkgClick: false
		});
		themeColor = themeColor || '#1976d2'
		var div = document.createElement("div");
		with(div.style) {
			background = "#ddd";
			padding = "15px";
			borderRadius = "3px";
		}
		l.load(div);
		
		div.innerHTML = "<p style='margin: 5px 0; color: " + themeColor + "; font-weight: bold;'>" + title + "</p>";
		var input = document.createElement("input");
		with(input){
			type = "text";
			value = val || "";			
		}
		with(input.style) {
			margin = "5px 0";
			width = "370px";
			display = "block";
			padding = "5px";
		}
		div.appendChild(input)
		var buttonHolder = document.createElement("p");
		buttonHolder.style = "padding: 0; margin: 0; text-align:right;";
		div.appendChild(buttonHolder);
		var btConfirm = document.createElement("button");
		buttonHolder.appendChild(btConfirm);
		with(btConfirm){
			style = "color: white; background:" + themeColor + "; padding: 5px 10px; border-radius:3px; border:none; font-size: 0.85em; margin: 5px";
			innerHTML = "Okay";
		}
		btConfirm.addEventListener("click", function(ev){
			callback(input.value);
			l.dismiss();
		})
		var btCancel = document.createElement("button");
		buttonHolder.appendChild(btCancel);	
		with(btCancel) {
			style = "color: white; background:" + themeColor + "; padding: 5px 10px; border-radius:3px; border:none; font-size: 0.85em; margin: 5px";
			innerHTML = "Cancel";
		}
		btCancel.addEventListener("click",function(ev) {
			l.dismiss();
		});
		l.setFocus(input);
		l.show();
	}
})();

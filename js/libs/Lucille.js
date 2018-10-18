/*
	Some UI elements
	1. SomeLightBox 
		public properties :
			style : {
				frame: {--styles--},
				background: {--styles--},
				foreground: {--styles--}
			}
			dismissOnBkgClick = true
		public methods:
			resize();
			setFocus(domElement); // set the first focus when light box is loaded
			dismiss();
			show();
			loadContent(domElement) // load a dom element
			loadContentById(id); // load a dom element to display in the light box by id
			replaceContent(domElement) // replace the dome element to be displayed
			replaceContentById(id); // replace the dom element to be displayed
			alert(title, msg, itemConfirm, itemCancel) // show a short alert msg
*/

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
		width: "400px",
		height: "400px",
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
	self.frame.style.display = "none";
	document.body.appendChild(self.frame);
	if(o.closeButton && o.dismissOnBkgClick) self.frame.appendChild(self.closeButton);
}

SomeLightBox.prototype.resize = function () {
	var o = this.getOptions(), self = this;
	self.view.style.position = "absolute";
			
	if (o.width != "fitToContent") {
		self.view.style.width = o.width;
	}
	if (o.height != "fitToContent") {
		self.view.style.height = o.height;
	}
	var h,w,h1,w1;
	if (self.frame.getBoundingClientRect) {
		h = self.frame.getBoundingClientRect().height;
		w = self.frame.getBoundingClientRect().width;
	} else {
		h = self.frame.offsetHeight, w = self.frame.offsetWidth;
	}
	if (self.view.getBoundingClientRect) {
		h1 = self.view.getBoundingClientRect().height;
		w1 = self.view.getBoundingClientRect().width;
	} else {
		h1 = self.view.offsetHeight, w1 = self.view.offsetWidth;
	}
	var top = ((h - h1) >> 1) * 0.7;
	var left = (w - w1) >> 1;
	if (!o.animation) {
		self.view.style.left = left + "px";
		self.view.style.top = top + "px";
		self.view.style.opacity  = 1;
		if (o.closeButton) {
			self.closeButton.style.top = top + "px";
			self.closeButton.style.left = left + w1 + "px";
		}
		return;
	}
	var pos = top + 50;
	var op = 0;
	self.view.style.left = left + "px";
	self.view.style.top = pos + "px";
	
	self.view.style.opacity = op;
	if (o.closeButton) {
		self.closeButton.style.top = pos + "px";
		self.closeButton.style.left = left + w1 + "px";
	}
	var duration = 20; // 100 ms
	/* animation type, default enter from bottom with opacity change */
	var move = function(){
		if (pos <= top) {
			clearInterval(animation);
		} else {
			var v = 50 / duration;
			pos -= v;
			var vop = 1 / duration;
			op += vop;
			self.view.style.top = pos + "px";
			self.view.style.opacity = op;
			if (o.closeButton) {
				self.closeButton.style.top = pos + "px";
			}
		}
	}
	var animation = window.setInterval(move, 1);
}

SomeLightBox.prototype.ondismiss = function(func){
	this.dismissListeners.push(func);
	return this;
}

SomeLightBox.prototype.setFocus = function (el) {
	this.firstFocus = el;
}

SomeLightBox.prototype.dismiss = function () {
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
				document.body.style["overflow"] = self._overflow;
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

SomeLightBox.prototype.show = function (animation) {
	var o = this.getOptions(), self = this;
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
	}
	self.frame.style.display = "block";
	if (!animation) {
		self.resize();
		return;
	}
	/* dissovle in animation */
	var op = 0;
	self.frame.style.opacity = op;
	self.view.style.opacity = 0;
	
	var animation = setInterval(move, 1);
	function move(callback){
		if (op >= 1) {
			clearInterval(animation);
			self.resize();
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
	self.frame.appendChild(self.view);
	if(o.autoForm) {
		var form = self.frame.getElementsByTagName("form");
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
};

var SomeInput = SomeInput || function () {
	var self = this;
	this.view = Node.inflate("<div style='display:inline-block;'></div>");
	this.value = [];
	this.selectionMode = "multiple";
	var input = Node.inflate("<input type='text' />");
	this.firstFocus = input;
	var CheckedItem = function (pos) {
		var position = pos;
		var checkedItemSelf = this;
		var name = self.value[pos];
		this.view = Node.inflate("<div style='padding:0; margin:5px; display:inline-block; background:#aaa'></div>");
		var label = Node.inflate("<div style='color:white;display:inline-block; padding:0 10px'>" + name + "</div>");
		label.onclick = function () {
			this.contentEditable = true;
		};
		label.onblur = function () {
			this.contentEditable = false;
			self.value[position] = this.innerText;
			self.onchange();
		};
		var btnDelete = Node.inflate("<button style='padding:2px 5px;background:white;color:#777; margin: 5px 10px; float:right'>x</button>");
		this.view.appendChildren([label, btnDelete]);
		btnDelete.onclick = function () {
			checkedItemSelf.view.remove();
			self.value.splice(position, 1);
		};
	};
	this.view.appendChild(input);
	input.onkeyup = function () {
		var keyCode = ("which" in event) ? event.which : event.keyCode;
		if (keyCode == 13) {
			if (this.value.trim().length > 0){
				autoAppend(this.value.trim());
			}
			this.value = "";
			self.onchange();
		}
	};
	input.onblur = function () {
		if(this.value.trim().length > 0){
			autoAppend(this.value.trim());
			this.value = "";
		}
		if(self.onblur) self.onblur();
	};
	input.onfocus = function () {
		if(self.onfocus) self.onfocus();
	}
	this.focus = function(){
		input.focus();
	};
	var autoAppend = function (value) {
		if (self.selectionMode == "single") {
			if (self.value.length == 1) {
				self.view.removeChild(self.view.childNodes[0]);
			}
			self.value[0] = value;
		} else {
			self.value.push(value);
		}
		var itemWidget = new CheckedItem(self.value.length - 1);
		self.view.insertBefore(itemWidget.view, input);
	};
	this.setInitValue = function (initValue){
		if(this.selectionMode == "single") {
			this.value = [];
			this.value[0] = initValue;
		} else {
			this.value = initValue;
		}
		this.view.removeAllChildren();
		this.view.appendChild(input);
		for(var i = 0; i < this.value.length; ++ i){
			var itemWidget = new CheckedItem(i);
			this.view.insertBefore(itemWidget.view, input);
		}
	}
};
var SomePopUpBox = SomePopUpBox || function () {
	var div = Node.inflate("<div style='position:absolute; background:red; z-index:100; padding:0 10px; color:white '></div>");
	this.showAt = function(msg, anchor){
		var top = anchor.offsetTop;
		var left = anchor.offsetLeft;
		var width = anchor.offsetWidth;
		left = left + width;
		var parent = anchor.parentNode;
		div.innerHTML = msg;
		div.style.left = left;
		div.style.top = top;
		parent.appendChild(div);
	}
	this.dismiss = function(){
		div.remove();
	}
};
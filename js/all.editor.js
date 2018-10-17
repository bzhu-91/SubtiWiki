$(document).on("submit", "form[type=ajax]", function(ev) {
	var self = this;
	var container = $(self).parents(".form-container");
	if (!container.length) {
		container = $(self).parent();
	}
	ev.preventDefault(ev);
	ev.stopPropagation();
	// avoid multiple submission by hitting enter
	$(self).find("input, textarea, button, a").blur();
	var data;
	try {
		// injected by Editor.js, can throw
		if (self.serialize) {
			// throws when monkey parse not successful
			data = self.serialize();
		} else {
			data = ajax.serialize(self);
		}
	} catch (e) {
		console.log(e);
		// monkey decode error
		var errdis = new ErrorDisplay();
		errdis.show(self, e.line, e.text);
		return false
	}
	var func = self.getAttribute("method") ? ajax[self.getAttribute("method").toLowerCase()] : ajax.get;
	func({
		headers: {
			Accept: "application/json"
		},
		url: self.action,
		data: data
	}).done(function(status, data, error, xhr){
		// if the form has a done function defined
		if (self.done) {
			self.done.call(self, status, data, error, xhr);
		} else {
			if (error && status != 204) {
				SomeLightBox.error("Connection to server lost.");
			} else {
				if (status >= 200 && status < 300) {
					if (data && data.uri) {
						if (self.getAttribute("mode")) {
							switch (self.getAttribute("mode")) {
								case "replace":
									ajax.get({
										url:data.uri,
										headers: {Accept: "text/html_partial"}
									}).done(function(status, data, error, xhr){
										if (!error && status == 200) {
											var view = $(data);
											if (window.Editor) {
												window.Editor.init(view.find(".editor"));
											}
											view.addClass("box");
											view.insertAfter(container);
											container.remove();
										}
									});
									break;
								case "redirect":
									window.location = data.uri
									break;
								case "alert":
									SomeLightBox.alert("Success", "Operation is successful");
							}
						} else {
							window.location = data.uri
						}
					} else if (data && data.message) {
						SomeLightBox.alert("Success", data.message);
					} else {
						SomeLightBox.alert("Success", "Operation is successful");
					}
				} else if (status >= 400) {
					SomeLightBox.error(data.message);
				}
			}
		}
	});
	return false;
});

$(document).on("blur", "input[type=gene], input[type=protein]",function(){
	var self = this;
	var form = $(this).parents("form");
	var geneName = this.value.trim();
	if (geneName.length < 2) {
		$(self).css({
			background:"#FFEBEE"
		});
	} else {
		ajax.get({
			url:"gene?mode=title&keyword="+geneName,
			headers:{Accept:"application/json"}
		}).done(function(status, data, error, xhr){
			if (error) {
				SomeLightBox.error("Connection to server lost");
			} else if (status == 200 && data.length == 1) {
				self.clone.val(data[0].id);
				$(self).css({
					background: "#E8F5E9"
				});
			} else {
				$(self).css({
					background:"#FFEBEE"
				});
			}
		})
	}
});

$(document).on("focus", "input[type=gene], input[type=protein]",function(){
	var self = this;
	var form = $(this).parents("form");
	$(self).css({
		background: "transparent"
	});
	if (!self.clone) {
		var clone = $(self).clone();
		self.clone = clone;
		clone.attr("type", "hidden");
		$(self).removeAttr("name");
		form.append(clone);
	}
});


$(document).on("click", ".toggle-editor", function(){
	var form = $(this).parents(".form-container").find("form");
	if (form.attr("display") == "on") {
		form.hide();
		form.attr("display", "off");
		this.innerHTML = "Edit";
	} else {
		form.show();
		form.attr("display", "on");
		this.innerHTML = "Collapse";
	}
	if (window.patch_textarea) {
		window.patch_textarea();
	}
});

(function(){
	window.ErrorDisplay = window.ErrorDisplay || function(){
		var div = document.createElement("div");
		with(div.style){
			background = "white";
			textAlign = "left";
			padding = "10px"
		}
		var tb = document.createElement("table");
		div.innerHTML = "<h3>Error in the input</h3><br/>";
		div.appendChild(tb);
		tb.setAttribute("cellspacing", 0);
		with(tb.style){
			width = "100%";
			lineHeight = 0;
		}
		var p = document.createElement("p");
		p.style = "text-align:center";
		var btn = document.createElement("button");
		btn.innerHTML = "Okay";
		p.appendChild(btn);
		div.appendChild(p);
		var lightbox = new SomeLightBox({
			height: "fitToContent"
		});
		lightbox.load(div);
		btn.onclick = function(){
			lightbox.dismiss();
		}
		var createLine = function(lineNumber, line) {
			var tr = document.createElement("tr");
			if (lineNumber % 2) {
				tr.style = "background: #ddd";
			}
			var td1 = document.createElement("td");
			var td2 = document.createElement("td");
			tr.appendChild(td1);
			tr.appendChild(td2);
			td1.innerHTML = lineNumber;
			td2.innerHTML = line;
			return tr;
		}
		this.show = function(form, lineNumber, text) {
			tb.innerHTML = "";
			lines = text.split("\n");
			var s = lineNumber - 5 < 0 ? 0 : lineNumber - 5;
			var e = lineNumber + 5 > lines.length - 1 ? lines.length - 1: lineNumber + 5;
			for (var i = s; i < e; i++) {
				var tr = createLine(i, lines[i]);
				if (i == lineNumber) {
					tr.style.color = "red";
				}
				tb.appendChild(tr);
			}
			lightbox.show();
		}
	};
})()
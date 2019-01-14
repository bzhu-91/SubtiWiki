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

$(document).on("focus", "input[type=gene], input[type=protein]",function(){
	// select operon with the gene in it
	var self = this;
	var form = $(self).parents("form");
	$(self).css({borderColor: "#999"});
	if (!self.clone) {
		var clone = $(self).clone();
		self.clone = clone;
		clone.attr("type", "hidden");
		$(self).removeAttr("name");
		form.append(clone);
		var restore = function () {
			$(self).attr("name", clone.attr("name"));
			clone.remove();
			return self;
		};
		var del = function () {
			$(self).remove();
			clone.remove();
		}
		clone[0].restore = restore;
		clone[0].delete = del;
		self.restore = restore;
		self.delete = del;
		// set up auto complete
		var suggestions = [];
		$(self).autocomplete({
			source: function (request, response) {
				ajax.get({
					url:"gene?mode=title&keyword="+request.term,
					headers:{Accept:"application/json"}
				}).done(function(status, data, error, xhr){
					if (error) {
						SomeLightBox.error("Connection to server lost");
					} else if (status == 200 && data.length > 0) {
						data.forEach(function(gene){
							gene.type = "gene";
							gene.value = gene.label = gene.title;
							suggestions.push(gene);
						});
						response(suggestions)
					}
				});
			},
			minLength: 2,
			select: function(event, ui) {
				clone.attr("value", ui.item.id);
			}
		});
	}
});

$(document).on("blur", "input[type=gene], input[type=protein]",function(){
	if (!this.clone || this.clone.val().trim().length == 0) {
		$(this).css({
			borderColor: "red"
		})
	}
});

$(document).on("focus", "input[type=DNA], input[type=RNA]", function () {
	// select operon with the gene in it
	var self = this;
	var form = $(self).parents("form");
	$(self).css({borderColor: "#999"});
	if (!self.clone) {
		var clone = $(self).clone();
		self.clone = clone;
		clone.attr("type", "hidden");
		$(self).removeAttr("name");
		form.append(clone);
		var restore = function () {
			$(self).attr("name", clone.attr("name"));
			clone.remove();
			return self;
		};
		var del = function () {
			$(self).remove();
			clone.remove();
		}
		clone[0].restore = restore;
		clone[0].delete = del;
		self.restore = restore;
		self.delete = del;
		// set up auto complete
		var suggestions = [];
		$(self).autocomplete({
			source: function (request, response) {
				ajax.get({
					url:"gene?mode=title&keyword="+request.term,
					headers:{Accept:"application/json"}
				}).done(function(status, data, error, xhr){
					if (error) {
						SomeLightBox.error("Connection to server lost");
					} else if (status == 200) {
						if (data.length == 1) {
							var gene = data[0];
							gene.type = "gene";
							gene.value = gene.label = "gene: " + gene.title;
							suggestions.push(gene);
							ajax.get({
								url: "operon?gene=" + encodeURIComponent(data[0].id),
								headers: {Accept: "application/json"}
							}).done(function(status, operons, error, xhr){
								if (!error && status == 200 && operons.length) {
									for(var i = 0; i < operons.length; i++) {
										operons[i].value = operons[i].label = "operon:" + operons[i].title.replace(/\[gene\|.+?\|(.+?)\]/gi, "$1");
										operons[i].type = "operon";
									}
									suggestions = suggestions.concat(operons);
									response(suggestions)
								}
							})
						} else {
							data.forEach(function(gene){
								gene.type = "gene";
								gene.value = gene.label = "gene: " + gene.title;
								suggestions.push(gene);
							});
						}
					}
				});
			},
			minLength: 2,
			select: function(event, ui) {
				clone.attr("value", ui.item.type + ":" + ui.item.id);
			}
		});
	}
});

$(document).on("blur", "input[type=DNA], input[type=RNA]", function(){
	if (!this.clone || this.clone.val().trim().length == 0) {
		$(this).css({
			borderColor: "red"
		})
	}
});

$(document).on("focus", "input[type=metabolite], input[type=complex]", function () {
	var self = this;
	var type = $(self).attr("type");
	console.log(type);
	var form = $(self).parents("form");
	$(self).css({
		borderColor: "#999"
	})
	if (!self.clone) {
		var name = $(self).attr("name");

		// shadow input
		var clone = $(self).clone();
		self.clone = clone;
		clone.attr("type", "hidden");
		$(self).removeAttr("name");
		form.append(clone);

		// shadow check
		var check = $("<input/>").attr({
			name: name + "_validated",
			type: "hidden"
		}).val("false");
		self.check = check;
		form.append(check);

		var restore = function () {
			$(self).attr("name", name);
			clone.remove();
			check.remove();
			return self;
		};
		var del = function () {
			$(self).remove();
			clone.remove();
			check.remove();
		}
		clone[0].restore = restore;
		clone[0].delete = del;
		self.restore = restore;
		self.delete = del;
		
		// set up auto complete
		$(self).autocomplete({
			source: function (request, response) {
				ajax.get({
					url: $(self).attr("type") + "?keyword=" + encodeURIComponent(request.term),
					headers: {Accept: "application/json"}
				}).done(function(status, data, error, xhr){
					if (!error && status == 200 && data.length) {
						for(var i = 0; i < data.length; i++) {
							data[i].label = data[i].title;
							data[i].value = data[i].title;
						}
						response(data)
					}
				})
			},
			minLength: 2,
			select: function(event, ui) {
				check.val(true);
				clone.attr("value", ui.item.id);
				$(this).css({
					borderColor: "green"
				});
			}
		});
	}
});

$(document).on("blur", "input[type=metabolite], input[type=complex]", function () {
	if (this.value.trim().length) {
		if (this.check.val() == "false") {
			this.clone.val(this.value.trim());
			$(this).css({
				borderColor: "saddlebrown"
			});
		}
	} else {
		$(this).css({
			borderColor: "red"
		});
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
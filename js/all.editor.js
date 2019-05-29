var handleResponse = function (jqXHR, mode, container) {
	var data = jQuery.parseJSON(jqXHR.responseText || "{}");
	var status = jqXHR.status;
	if (self.done) {
		self.done(jqXHR.status, data,null, jqXHR);
	} else {
		if (status >= 200 && status < 300) {
			if (status == 200) {
				var l = SomeLightBox.alert("Success", "Operation is successful");
				setTimeout(function(){
					l.dismiss()
				}, 400);
			} else if (status == 204) {
				if (mode == "reload") {
					window.location.reload();
				} else if (mode == "back") {
					window.location.back();
				} else if (mode.startsWith("goto:")) {
					window.location = mode.replace("goto:","");
				}
			} else if (status == 201) {
				if (data && data.uri) {
					if (mode == "replace") {
						$.ajax({
							type: "get",
							url: data.uri,
							headers: {Accept: "text/html_partial"},
							success: function (html) {
								view = $(html).addClass("box").insertAfter(container);
								view.find("textarea").monkey();
								container.remove();
							}
						});
					} else {
						window.location = data.uri
					}
				} else if (data && data.message) {
					var l = SomeLightBox.alert("Success", data.message);
					setTimeout(function(){
						l.dismiss
					}, 400);
				} else {
					var l = SomeLightBox.alert("Success", "Operation is successful");
					setTimeout(function(){
						l.dismiss()
					}, 400);
				}
			} else {
				var l = SomeLightBox.alert("Success", "Operation is successful");
				setTimeout(function(){
					l.dismiss()
				}, 400);
			}
		} else if (status >= 400) {
			SomeLightBox.error(data.message);
		}
	}
}

$(document).on("submit", "form[type=ajax]", function(ev) {
	var self = this;
	var container = $(self).parents(".form-container");
	if (!container.length) {
		container = $(self).parent();
	}
	ev.preventDefault(ev);
	var l = SomeLightBox.alert("Waiting", "Data is being sent to server...")

	var data = $(self).serializeArray();
	$(self).find("input[type=checkbox]:not(:checked)").each(function(){
		var $checkbox = $(this);
		if ($checkbox.attr("name")) {
			data.push({
				name: $checkbox.attr("name"),
				value: "off"
			});
		}
	});
	$(self).find("input, textarea, button, a").blur();
	$.ajax({
		url: $(self).attr("action"),
		type: $(self).attr("method"),
		dataType: "json",
		data: data,
		complete: function(jqXHR) {
			l.dismiss();
			handleResponse(jqXHR, $(self).attr("mode"), container);
		}
	});
	return false;
});

var validateGene = function (keyword, callback) {
	$.ajax({
		url: "gene?mode=title&keyword="+ encodeURIComponent(keyword),
		dataType: "json",
		success: function (data) {
			if (data.length == 1) {
				callback(data[0]);
			} else callback()
		},
		error: function () {
			callback();
		}
	})
}

var searchOperon = function (gene, callback) {
	$.ajax({
		url: "operon?gene=" + encodeURIComponent(gene.id),
		dataType: "json",
		success: function (operons) {
			var suggestions = [];
			if (operons.length) {
				for(var i = 0; i < operons.length; i++) {
					operons[i].value = operons[i].label = "operon:" + operons[i].title.replace(/\[gene\|.+?\|(.+?)\]/gi, "$1");
					operons[i].type = "operon";
				}
				suggestions = suggestions.concat(operons);
				callback(suggestions)
			}
		},
		error: function () {
			callback([]);
		}
	});
}

var search = function (type, keyword, callback) {
	$.ajax({
		url: type + "?keyword=" + encodeURIComponent(keyword),
		dataType: "json",
		success: function (data) {
			for(var i = 0; i < data.length; i++) {
				data[i].label = data[i].title;
				data[i].value = data[i].title;
			}
			callback(data)
		},
		error: function () {
			callback([]);
		}
	});
}

$(document).on("focus", "input[type=gene], input[type=protein]",function(){
	// select operon with the gene in it
	var self = this;
	var $form = $(self).parents("form, .form");
	$(self).css({borderColor: "#999"});
	if (!self.$clone) {
		var $clone = $(self).clone().attr("type", "hidden");
		$(self).removeAttr("name").prop("$clone", $clone);
		$form.append($clone);
		// set up auto complete
		$(self).autocomplete({
			source: function (request, response) {
				search("gene", request.term, response);
			},
			minLength: 2,
			select: function(event, ui) {
				$clone.attr("value", ui.item.id);
			}
		});
	}
});

$(document).on("blur", "input[type=gene], input[type=protein]",function(){
	var geneTitle = this.value.trim();
	var self = this;
	if (self.$clone) {
		if (self.$clone.val().trim().length == 0) {
			validateGene(geneTitle, function(gene){
				if (gene) {
					self.$clone.val(gene.id);
				} else {
					$(self).css({
						borderColor: "red"
					});
				}
			})
		}
	} else {
		$(self).css({
			borderColor: "red"
		});
	}
});

$(document).on("focus", "input[type=DNA], input[type=RNA]", function () {
	// select operon with the gene in it
	var self = this;
	var $form = $(self).parents("form, .form");
	$(self).css({borderColor: "#999"});
	if (!self.$clone) {
		var $clone = $(self).clone().attr("type", "hidden");
		$(self).removeAttr("name").prop("$clone", $clone);
		$form.append($clone);
		// set up auto complete
		$(self).autocomplete({
			source: function (request, response) {
				search("gene", request.term, function(genes){
					genes.forEach(function(g){
						g.label = "gene: " + g.label;
						g.type = "gene"
					});
					if (genes.length == 1) {
						searchOperon(genes[0], function(operons){
							operons.forEach(function(o){
								o.label = "operon: " +  o.title.replace(/\[gene\|.+?\|(.+?)\]/gi, "$1");
								o.type = "operon"
							});
							var suggestions = genes.concat(operons);
							response(suggestions);
						})
					} else {
						response(genes);
					}
				})
			},
			minLength: 2,
			select: function(event, ui) {
				$clone.attr("value", ui.item.type + ":" + ui.item.id);
			}
		});
	}
});

$(document).on("blur", "input[type=DNA], input[type=RNA]", function(){
	var geneTitle = this.value.trim();
	var self = this;
	if (self.$clone) {
		if (self.$clone.val().trim().length == 0) {
			validateGene(geneTitle, function(gene){
				if (gene) {
					self.$clone.val(gene.id);
				} else {
					$(self).css({
						borderColor: "red"
					});
				}
			})
		}
	} else {
		$(self).css({
			borderColor: "red"
		});
	}
});

$(document).on("focus", "input[type=metabolite], input[type=complex]", function () {
	var self = this;
	var $form = $(self).parents("form, .form");
	$(self).css({
		borderColor: "#999"
	})
	if (!self.$clone) {
		var name = $(self).attr("name");

		// shadow input
		var $clone = $(self).clone().attr("type", "hidden");
		$(self).removeAttr("name").prop("$clone", $clone);
		$form.append($clone);

		// shadow check
		var $check = $("<input/>").attr({
			name: name + "_validated",
			type: "hidden"
		}).val("false");
		self.$check = $check;
		$form.append($check);

		// set up auto complete
		$(self).autocomplete({
			source: function (request, response) {
				search($(self).attr("type"), request.term, response);
			},
			minLength: 2,
			select: function(event, ui) {
				$check.val(true);
				$clone.attr("value", ui.item.id);
				$(this).css({
					borderColor: "green"
				});
			}
		});
	}
});

$(document).on("blur", "input[type=metabolite], input[type=complex]", function () {
	if (this.value.trim().length) {
		if (this.$check.val() == "false") {
			this.$clone.val(this.value.trim());
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

$(document).on("click", ".form [type=submit]", function (){
	var $self = $(this);
	// handle form-ignore class
	if ($self.parentsUntil(".form").filter(".form-ignore").length) {
		return;
	}
	var l = SomeLightBox.alert("Waiting", "Data is being sent to server...")
	// fake form submission
	$formEl = $($self.parents(".form").get(0));
	var data = [];
	// serialize the form
	$formEl.find("[name]").each(function(){
		var $el = $(this);
		if ($el.parentsUntil($formEl).filter(".form-ignore").length) {
			return;
		}
		data.push({
			name: $el.attr("name"),
			value: $el.val()
		});
	});
	$formEl.find("[name][type=checkbox]:not(:checked)").each(function(){
		var $el = $(this);
		if ($el.parentsUntil($formEl).filter(".form-ignore").length) {
			return;
		}
		data.push({
			name: $el.attr("name"),
			value: "off"
		});
	});
	$.ajax({
		url: $formEl.attr("action"),
		dataType: "json",
		type: $formEl.attr("method"),
		data: data,
		complete: function(jqXHR) {
			l.dismiss();
			handleResponse(jqXHR, $formEl.attr("mode"), $formEl.parent());
		}
	})
});
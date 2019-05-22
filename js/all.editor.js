$(document).on("submit", "form[type=ajax]", function(ev) {
	var self = this;
	var container = $(self).parents(".form-container");
	if (!container.length) {
		container = $(self).parent();
	}
	ev.preventDefault(ev);
	var l = SomeLightBox.alert("Waiting", "Data is being sent to server...")

	$(self).find("input, textarea, button, a").blur();
	$.ajax({
		url: $(self).attr("action"),
		type: $(self).attr("method"),
		dataType: "json",
		data: $(self).serialize(),
		complete: function(jqXHR) {
			l.dismiss();
			var data = jQuery.parseJSON(jqXHR.responseText || "{}");
			var status = jqXHR.status;
			var mode = $(self).attr("mode");
			if (self.done) {
				self.done(jqXHR.status, data,null, jqXHR);
			} else {
				if (status >= 200 && status < 300) {
					if (data && data.uri) {
						switch (mode) {
							case "replace":
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
								break;
							case "alert":
								SomeLightBox.alert("Success", "Operation is successful");
								break;
							default:
								window.location = data.uri
								break;
							
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
})

$(document).on("focus", "input[type=gene], input[type=protein]",function(){
	// select operon with the gene in it
	var self = this;
	var $form = $(self).parents("form");
	$(self).css({borderColor: "#999"});
	if (!self.clone) {
		var clone = $(self).clone().attr("type", "hidden");
		$(self).removeAttr("name").prop("clone", clone);
		$form.append(clone);
		// set up auto complete
		var suggestions = [];
		$(self).autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "gene?keyword="+ encodeURIComponent(request.term),
					dataType: "json",
					success: function (data) {
						data.forEach(function(gene){
							gene.type = "gene";
							gene.value = gene.label = gene.title;
							suggestions.push(gene);
						});
						response(suggestions)
					},
					error: function () {
						response([]);
					}
				})
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
	var $form = $(self).parents("form");
	$(self).css({borderColor: "#999"});
	if (!self.clone) {
		var clone = $(self).clone().attr("type", "hidden");
		$(self).removeAttr("name").prop("clone", clone);
		$form.append(clone);
		// set up auto complete
		var suggestions = [];
		$(self).autocomplete({
			source: function (request, response) {
				$.ajax({
					url:"gene?mode=title&keyword=" + encodeURIComponent(request.term),
					dataType: "json",
					success: function (data) {
						if (data.length == 1) {
							var gene = data[0];
							gene.type = "gene";
							gene.value = gene.label = "gene: " + gene.title;
							suggestions.push(gene);
							$.ajax({
								url: "operon?gene=" + encodeURIComponent(data[0].id),
								dataType: "json",
								success: function (data) {
									if (data.length) {
										for(var i = 0; i < operons.length; i++) {
											operons[i].value = operons[i].label = "operon:" + operons[i].title.replace(/\[gene\|.+?\|(.+?)\]/gi, "$1");
											operons[i].type = "operon";
										}
										suggestions = suggestions.concat(operons);
										response(suggestions)
									}
								},
								error: function () {
									response([]);
								}
							});
						} else {
							data.forEach(function(gene){
								gene.type = "gene";
								gene.value = gene.label = "gene: " + gene.title;
								suggestions.push(gene);
							});
						}
					},
					error: function () {
						response([]);
					}
				})
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
	var $form = $(self).parents("form");
	$(self).css({
		borderColor: "#999"
	})
	if (!self.clone) {
		var name = $(self).attr("name");

		// shadow input
		var clone = $(self).clone().attr("type", "hidden");
		$(self).removeAttr("name").prop("clone", clone);
		$form.append(clone);

		// shadow check
		var check = $("<input/>").attr({
			name: name + "_validated",
			type: "hidden"
		}).val("false");
		self.check = check;
		$form.append(check);

		// set up auto complete
		$(self).autocomplete({
			source: function (request, response) {
				$.ajax({
					url: $(self).attr("type") + "?keyword=" + encodeURIComponent(request.term),
					dataType: "json",
					success: function (data) {
						for(var i = 0; i < data.length; i++) {
							data[i].label = data[i].title;
							data[i].value = data[i].title;
						}
						response(data)
					},
					error: function () {
						response([]);
					}
				});
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
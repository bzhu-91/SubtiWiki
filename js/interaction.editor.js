$(document).ready(function(){
	$("textarea").monkey();
});

$(document).on("click", ".delBtn[target=interaction]", function(){
	var self = this;
	var id = self.id;
	var container = $(self).parents(".form-container");
	if (container.length == 0) {
		container = $(self).parent();
	}
	if (id) {
		var mode = $(self).attr("mode");
		SomeLightBox.alert({
			title: "Delete",
			message: "Do you want to remove this interaction?",
			confirm: {
				title: "Delete",
				color: "red",
				onclick : function () {
					$.ajax({
						type: "delete",
						url: "interaction?id=" + id,
						dataType: "json",
						statusCode: {
							204: function () {
								if (mode == "redirect") {
									SomeLightBox.alert("Success", "Deletion is succcessful");
									setTimeout(function(){
										window.location = "interaction";
									}, 300);
								} else {
									container.remove();
								}
							},
							500: function (error) {
								SomeLightBox.error(error.message);
							}
						}
					});
				}
			},
			cancel: {
				title: "Cancel"
			},
			theme: "red"
		})
	} else {
		container.remove();
	}
	
});

$(document).on("click", ".toggle-interaction-editor", function(){
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
});


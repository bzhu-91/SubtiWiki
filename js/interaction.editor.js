$(document).ready(function(){
	window.Editor.init(".editor");
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
			title: {
				title: "Delete",
				color: "red"
			},
			message: "Do you want to remove this interaction?",
			confirm: {
				title: "Delete",
				color: "red",
				onclick : function () {
					ajax.delete({
						url: "interaction?id=" + id,
						headers: {Accept: "application/json"}
					}).done(function(status, data, error, xhr){
						if (status == 204) {
							if (mode == "redirect") {
								SomeLightBox.alert("Success", "Deletion is succcessful");
								setTimeout(function(){
									window.location = "interaction";
								}, 300);
							} else {
								container.remove();
							}
						} else if (error) {
							SomeLightBox.error("Server connection is lost.");
						} else {
							SomeLightBox.error(data.message);
						}
					});
				}
			},
			cancel: {
				title: "Cancel"
			}
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


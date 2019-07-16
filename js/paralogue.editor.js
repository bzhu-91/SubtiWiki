$(document).ready(function(){
	$("textarea").monkey();
});

$(document).on("click", ".delBtn[target=paralogue]", function(){
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
			message: "Do you want to remove this paralogue?",
			confirm: {
				title: "Delete",
				color: "red",
				onclick : function () {
					$.ajax({
						type: "delete",
						url: "protein/paralogue?id=" + id,
						dataType:"json",
						statusCode: {
							204: function () {
								if (mode == "redirect") {
									SomeLightBox.alert("Success", "Deletion is succcessful");
									setTimeout(function(){
										window.location = "paralogue";
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
				title: "Cancel",
				color: "gray"
			},
			theme: "red"
		})
	} else {
		container.remove();
	}
	
});



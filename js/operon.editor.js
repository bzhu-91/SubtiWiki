$(document).ready(function(){
	window.Editor.init(".editor");
	if (!window.showDelBtn) $(".delBtn").hide();
});

$(document).on("click", ".delBtn[target=operon]", function(){
	var id = this.id;
	var container = $(this).parents(".form-container");
	if (container.length == 0) {
		container = $(this).parent();
	}
	var mode = $(this).attr("mode") || "redirect";
	SomeLightBox.alert({
		title: {
			title: "Delete",
			color: "red"
		},
		message: "Do you want to remove this operon?",
		confirm: {
			title: "Delete",
			color: "red",
			onclick : function () {
				ajax.delete({
					url: "operon?id=" + id,
					headers: {Accept: "application/json"}
				}).done(function(status, data, error, xhr){
					if (status == 204) {
						if (mode == "redirect") {
							SomeLightBox.alert("Success", "Deletion is succcessful");
							setTimeout(function(){
								window.location = "operon";
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
});


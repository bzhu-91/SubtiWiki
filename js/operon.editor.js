$(document).ready(function(){
	$("textarea[name=data]").monkey();
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
				$.ajax({
					type: "delete",
					dataType: "json",
					url: "operon?id=" + id,
					statusCode:{
						204: function () {
							SomeLightBox.alert("Success", "Deletion is succcessful");
							setTimeout(function(){
								window.location = "operon";
							}, 300);
						},
						500: function (error) {
							SomeLightBox.error(error.message);
						},
						400: function (error) {
							SomeLightBox.error(error.message);
						}
					}
				})
			}
		},
		cancel: {
			title: "Cancel"
		}
	})
});
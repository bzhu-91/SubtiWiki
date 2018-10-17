(function(){
	window.geneTranslationalRegulation = window.geneTranslationalRegulation || {
		remove: function(sender){
			SomeLightBox.alert({
				title:"Warning",
				color: "red"}
			, "Do you want to delete this regulation?", {
				title: "delete",
				color: "red",
				onclick: function(){
					var form = $(sender).parents("form")[0];
					var id = form.elements["id"].value.trim();
					$.ajax({
						url: "geneTranslationalRegulation/remove/" + id,
						type: "get",
						success: function(response){
							try {
								var o = JSON.parse(response);
								if (o.error) {
									SomeLightBox.alert("Error", o.error, function(){});
								} else if (o.redirect) {
									window.location = o.redirect;
								} else {
									SomeLightBox.alert("Success", "This regulation has been successfully removed from database.", function(){});
									$(form).parents(".box").remove();
								}
							} catch (e) {
								SomeLightBox.alert("Error", "Connection to server lost, please try again.", function(){});
							}
						}
					})
				}
			}, {
				color: "gray"
			});
		}
	}
})()
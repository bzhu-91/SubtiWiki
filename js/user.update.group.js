$(document).on("click", ".submit", function(){
	var tr = $(this).parents("tr");
	var name = tr.find("input[name=name]").val();
	var privilege = tr.find("select[name=privilege]").val();
	$.ajax({
		type:"put",
		url: "user?name=" + encodeURIComponent(name) + "&privilege=" + privilege,
		dataType:"json",
		success: function () {
			SomeLightBox.alert("Success", "Update is successfull");
		},
		error: function (data) {
			SomeLightBox.error(data.message);
		}
	});
})
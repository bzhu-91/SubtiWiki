$(document).on("click", ".submit", function(){
	var tr = $(this).parents("tr");
	var name = tr.find("input[name=name]").val();
	var privilege = tr.find("select[name=privilege]").val();
	ajax.put({
		url: "user?name=" + encodeURIComponent(name) + "&privilege=" + privilege,
		headers: {Accept: "application/json"}
	}).done(function(status, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to data lost");
		} else if (status == 200){
			SomeLightBox.alert("Success", "Update is successfull");
		} else {
			SomeLightBox.error(data.message);
		}
	});
})
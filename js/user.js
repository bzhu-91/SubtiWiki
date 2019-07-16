var user = user || {};
user.login = function(){
	$.ajax({
		type:"GET",
		url:"user/login",
		headers: {Accept: "text/html_partial"},
		success:function(response){
			var lightbox = new SomeLightBox({
				height: "fitToContent",
			});
			var container = document.createElement("div");
			container.style = "background: white; padding: 20px";
			container.innerHTML = response;
			lightbox.load(container);
			lightbox.show();
		}
	})
}

user.logout = function (){
	SomeLightBox.alert("Log out", "Do you want to log out?", function(){
		$.ajax({
			url: "user/logout",
			dataType:"json",
			success: function (data) {
				SomeLightBox.alert("Log out", "Log out successfull", null, null);
				setTimeout(function(){
					location.reload();
				}, 600)
			}
		});
	}, function(){});
}

$(document).on("submit", "#login", function(ev){
	ev.preventDefault();
	var form = ev.target;
	var data = {};
	data.name = form['name'].value.trim();
	data.password = md5(form['password'].value);
	$.ajax({
		type:"post",
		url: "user/login",
		dataType:"json",
		data: data,
		success: function (data) {
			SomeLightBox.alert("Log in", "Log in successfull");
			setTimeout(function(){
				location.reload();
			}, 600)
		},
		error: function (data) {
			SomeLightBox.error(data.message);
		}
	});
	return false;
});
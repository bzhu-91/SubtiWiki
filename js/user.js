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
		ajax.get({
			url: "user/logout",
			headers: {Accept: "application/json"}
		}).done(function(status, data, error, xhr){
			if (status == 200) {
				SomeLightBox.alert("Log out", "Log out successfull", null, null)
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
	ajax.post({
		url: "user/login",
		headers: {Accept: "application/json"},
		data: ajax.serialize(data)
	}).done(function(status, data, error, xhr) {
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if (status == 200) {
			SomeLightBox.alert("Log in", "Log in successfull");
			setTimeout(function(){
				location.reload();
			}, 600)
		} else if (data) {
			SomeLightBox.error(data.message);
		}
	});
	return false;
});
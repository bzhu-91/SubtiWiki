var user = user || {};
user.set = function(callback){
	$("#login").on("submit", function(ev){
		ev.preventDefault();
		var self = ev.target;
		self['user_password'].value = md5(self['user_password'].value);
		$.ajax({
			type: "POST",
			url:"user/login",
			data: $(self).serialize(),
			success:function(response){
				console.log(response)
				try {
					var ob = JSON.parse(response);
					var title = "";
					var msg = ""	;
					if (ob.error) {
						title = "Error";
						msg = ob.error;
					} else {
						title = "Log in Successful";
						msg = "Welcome, " + self.elements['user_name'].value;
					}
				} catch(e) {
					title = "Error";
					msg = "Sorry, An Error has happened";
				}
				if(callback) callback();
				SomeLightBox.alert(title, msg)
				setTimeout(function(){
					location.reload();
				}, 600)
			}
		})
		return false;
	});
}

user.login = function(){
	$.ajax({
		type:"GET",
		url:"user/login/fromajax",
		success:function(response){
			var lightbox = new SomeLightBox({
				animation:false,
				height: "fitToContent",
			});
			var container = $.element("div",{style:"background:#0099cc; overflow:hidden"})[0];
			container.innerHTML = response;
			lightbox.load(container);
			lightbox.show();
			user.set(function(){
				lightbox.dismiss();
			});
		}
	})
}

user.logout = function (){
	SomeLightBox.alert("Log out", "Do you want to log out?", function(){
		$.ajax({
			type:"GET",
			url:"user/logout",
			success: function (){
				SomeLightBox.alert("Log out", "Log out successfull", null, null)
				setTimeout(function(){
					location.reload();
				}, 600)
			}
		})
	},null,null,true);
}

$(document).ready(function(){
	if(document.getElementById("login")){
		user.set();
	}
})

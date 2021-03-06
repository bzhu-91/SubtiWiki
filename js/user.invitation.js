var invitation = invitation || {};

$(document).ready(function() {
	invitation.template = $("#emailContent").val();
	invitation.lightBox = new SomeLightBox({
		width: "60%",
		height: "auto"
	});
	invitation.lightBox.loadById("email");
});

$(document).on("submit", "#invitation", function(ev) {
	ev.preventDefault(ev);
	ev.stopPropagation();

	var name = this.name.value.trim();
	var email = this.email.value.trim();
	var admin = this.admin.checked;

	

	if (name !== "" && email !== "") {
		invitation.name = name;
		invitation.email = email;
		invitation.type = admin ? "admin" : "normal";

		var token = "";
		var pool = "0123456789abcde";
		for(var i = 0; i < 32; i ++){
			var rand = Math.floor(Math.random() * 15);
			token += pool[rand];
		}

		invitation.token = token;

		var emailContent = invitation.template;

		emailContent = emailContent.replace("__name__", name);
		emailContent = emailContent.replace("__location__", location.host + $("base").attr("href"));
		emailContent = emailContent.replace("__token__", token);

		if (admin) {
			emailContent = emailContent.replace("__adminExtra__", "Your account type will be: Administration. You can further invite other users by following the link: http://" + location.host + $("base").attr("href") + "user/invitation\n\n");
		} else {
			emailContent = emailContent.replace("__adminExtra__", "");
		}

		invitation.lightBox.show();
		patch_textarea();
		$("#emailContent").val(emailContent);

	}
});

$(document).on("submit", "#email", function(ev) {
	ev.preventDefault(ev);
	ev.stopPropagation();

	invitation.body = this.body.value.trim();
	invitation.sendEmail = !this.sendEmail.checked;

	var keys = ["body", "email", "token", "type", "name"];
	var data = {};
	for (let i = 0; i < keys.length; i++) {
		const key = keys[i];
		data[key] = invitation[key];
		
	}

	$.ajax({
		type:"post",
		url: "user/invitation",
		dataType:"json",
		data: data,
		statusCode: {
			201: function (data) {
				SomeLightBox.alert("Success", data.message);
			},
			500: function (data) {
				SomeLightBox.error(data.message);
			}
		}
	});
})
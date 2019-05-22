$(document).on("submit", "#registration", function(ev){
	ev.preventDefault();
	ev.stopPropagation(ev);

	var self = this;
	if (self.password_1.value != self.password_2.value) {
		SomeLightBox.error("Passwords are not consistent");
	} else {
		// md5 encrypt the password on the client side
		var password = self.password_1.value;
		password = md5(password);

		var data = {
			name: self.name.value.trim(),
			password: password,
			realName: self.realName.value.trim(),
			invitation: ("invitation" in self) ? self.invitation.value.trim() : "",
			email: ("email" in self) ? self.email.value.trim(): ""
		};

		$.ajax({
			type:"post",
			url: "user",
			data: ajax.serialize(data),
			dataType:"json",
			statusCode: {
				201: function () {
					SomeLightBox.alert("Success", "Your account is ready");
				},
				500: function (data) {
					SomeLightBox.error(data.message);
				}
			}
		});
	}
});

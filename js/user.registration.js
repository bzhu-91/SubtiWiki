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

		ajax.post({
			url: "user",
			data: ajax.serialize(data),
			headers: {Accept: "application/json"},
		}).done(function(status, data, error, xhr) {
			if (error) {
				SomeLightBox.error("Connection with server lost");
			} else if (status == 201) {
				SomeLightBox.alert("Success", "Your account is ready");
			} else {
				SomeLightBox.error(data.message);
			}
		});
	}
});

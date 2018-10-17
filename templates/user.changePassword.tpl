<form method="user/changePassword" id="form-change-password" class="box">
	<input type="hidden" name="name" value="{{:name}}" />
	<input type="hidden" name="token" value="{{:token}}" />
	<p>
		<label>New password: </label>
		<input name="p1" type="password" />
	</p>
	<p>
		<label>Repeat password: </label>
		<input name="p2" type="password" />
	</p>
	<p style="text-align:right">
		<input type="submit" />
	</p>
</form>
<script type="text/javascript" >
	$(document).on("submit", "#form-change-password", function(ev){
		ev.preventDefault();
		ev.stopPropagation();
		
		var name = this.name.value;
		var p1 = this.p1.value;
		var p2 = this.p2.value;
		var token = this.token.value;

		

		if (p1 != p2) {
			SomeLightBox.error("Password is not consistent");
		} else {
			if (p1.length < 5) {
				SomeLightBox.error("Password should be longer than 5 characters.");
			} else {
				$.ajax({
					url: "user?name=" + encodeURIComponent(name) + "&password=" + md5(p1) + "&token="+token,
					type: "put",
					headers: {Accept: "application/json"},
					success: function (data, xhr) {
	
					},
					error: function (data, xhr) {
	
					}
				});
			}
		}
		return false;
	});
</script>
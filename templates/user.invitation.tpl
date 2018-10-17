<form id="invitation" class="box" method="post">
	<p><label>Name (including title): </label><input type="text" name="name" required /></p>
	<p><label>Email: </label><input type="email" name="email" /></p>
	<p><input type="checkbox" name="admin" id="__id1"><label for="__id1">Invite him/her as an admin</label></p>
	<p style="text-align: right"><input type="submit" value="Preview invitation email"></p>
</form>
<form id="email" style="display: none; background: white; padding: 20px; outline: none;" method="post">
	<p><label>Invitation Email:</label></p>
	<textarea name="body" style="width: 100%; padding: 10px; line-height: 2em" id="emailContent">Dear __name__:

Greetings from <?php echo $GLOBALS["SITE_NAME"]; ?>! Your invitation token for <?php echo $GLOBALS["SITE_NAME"]; ?> is: __token__.

To create an account for <?php echo $GLOBALS["SITE_NAME"]; ?>, please follow this link: http://__location__user/registration.

__adminExtra__Best regards,
Your <?php echo $GLOBALS["SITE_NAME"]; ?> team</textarea>
<p style="text-align: right;">
	<input type="checkbox" name="sendEmail" id="__id2"><label for="__id2">Save the invitation token but do not send an email</label>
	<input type="submit">
</p>
</form>
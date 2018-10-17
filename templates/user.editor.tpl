<form action="user" method="put" type="ajax">
	<input type="hidden" name="name" value="{{:name}}">
	<p>
		<label>Email: </label>
		<input type="email" name="email" value="{{:email}}" />
	</p>
	<p>
		<label>Real name: </label>
		<input type="text" name="realName" value="{{:realName}}" />
	</p>
	<div class="editor"><textarea name="description">{{:description}}</textarea></div>
	<p style="text-align: right;">
		<input type="submit" />
	</p>
</form>
<form action="user/password" method="post" type="ajax" style="float:left;position:relative;top:-43px;left:-5px">
	<input type="hidden" name="name" value="{{:name}}" />
	<input type="submit" value="Change password" style="background: orange"/>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		Editor.init(".editor");
	});
</script>
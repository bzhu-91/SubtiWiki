<div class="form-container">
	<form action="regulon" type="ajax" method="{{:method}}" mode="{{:mode}}">
		<p><label>{{:title}}</label></p>
		<div class="editor"><textarea name="data" type="monkey">{{::rest}}</textarea></div>
		<input type="hidden" name="id" value="{{:id}}">
		<p style="text-align: right;"><input type="submit" /></p>
	</form>
	<div class="footnote" style="margin-top: 50px; display: {{:showFootNote}}">
		<p style="display: none;">{{:bank_id}}</p>
		<p style="display: none;">{{:id}}</p>
		<p><b>Page visits: </b>{{:count}}</p>
		<p><b>Time of last update: </b>{{:lastUpdate}}</p>
		<p><b>Author of last update: </b>{{:lastAuthor}}</p>
	</div>
	<script type="text/javascript">
		window.Editor.init(".editor");
	</script>
</div>


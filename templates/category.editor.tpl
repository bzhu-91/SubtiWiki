<div>
	<div rewrite= "true" class="box">
		<p><a href="category"><< ALL CATEGORIES</a></p>
		{{categoryTree:parents}}
		{{categoryTree:self}}
		{{categoryTree:children}}
		{{:addNewButton}}
	</div>
	<div rewrite="true">
		{{relationGeneTableEdit:genes}}
	</div>
	<br/>
	<form action="category" type="ajax" method="put" class="box">
		<input type="hidden" name="id" value="{{:id}}">
		<p><label>Title: </label><input type="text" name="title" value="{{:title}}" style="width: 300px"></p>
		<p><div class="editor"><textarea type="monkey" name="data">{{::rest}}</textarea></div></p>
		<p style="text-align: right;"><a class="button delBtn" target="category" id="{{:id}}" style="float:left; display: {{:showDelBtn}}">Delete</a><input type="submit"></p>
		<p style="clear: both;"></p>
		<div class="footnote" style="margin-top: 50px;">
			<p style="display: none;">{{:bank_id}}</p>
			<p style="display: none;">{{:id}}</p>
			<p style="display: none;">{{:equalTo}}</p>
			<p><b>Page visits: </b>{{:count}}</p>
			<p><b>Time of last update: </b>{{:lastUpdate}}</p>
			<p><b>Author of last update: </b>{{:lastAuthor}}</p>
		</div>
	</form>
</div>


<form action="category" type="ajax" method="post" id="blank" style="background: white; padding: 15px">
	<input type="hidden" name="parentId" value="{{:id}}">
	<p><label>Title: </label><input type="text" name="title" /><input type="submit" /></p>
</form>

<script type="text/javascript">
/* @const */ var categoryId = "{{:id}}";
/* @const */ var newCategory = {{:new}};
</script>
<div class="form-container">
	<form action="protein/paralogue" type="ajax" method="post" mode="{{:updateMode}}">
	<p><label>Protein: </label><input type="protein" name="prot1"> - <label>Protein:</label><input type="protein" name="prot2"></p>
	<div class="editor"><textarea name="data" type="monkey">* description
insert text here</textarea></div>
		<p style="text-align: right;">
			<a target="paralogue" class="button delBtn">Delete</a>
			<input type="submit">
		</p>
	</form>
</div>
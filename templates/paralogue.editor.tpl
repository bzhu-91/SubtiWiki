<div class="form-container">
	<p>
		<label>Protein: </label>{{:prot1->title}} - <label>Protein: </label>{{:prot2->title}}
		<button class="toggle-editor" style="float: right;">Edit</button>
	</p>
	<p style="clear: both;"></p>
	<form action="protein/paralogue" method="put" type="ajax" mode="alert" display="off" style="display: none;">
		<input type="hidden" name="id" value="{{:id}}">
		<input type="hidden" name="prot1" value="{{:prot1->id}}">
		<input type="hidden" name="prot2" value="{{:prot2->id}}">
		<div class="editor"><textarea type="monkey" name="data">{{::rest}}</textarea></div>
		<p style="text-align: right;">
			<a target="paralogue" id="{{:id}}" class="button delBtn" style="float: left;" mode="{{:delMode}}">Delete</a>
			<input type="submit" />
		</p>
		<p style="clear: both;"></p>
		<div class="footnote" style="margin-top: 50px;">
			<p><b>Time of last update: </b>{{:lastUpdate}}</p>
			<p><b>Author of last update: </b>{{:lastAuthor}}</p>
		</div>
	</form>
</div>
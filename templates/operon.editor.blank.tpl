<div class="form-container">
	<p><i>To add regulations, please submit this form first</i></p>
	<form action="operon" method="post" type="ajax" mode="{{:updateMode}}">
		<div class="editor"><textarea type="monkey" name="data">* genes: insert text here
* title: insert text here
* description: insert text here

* regulation
insert text here

* additional information
insert text here
</textarea></div>
		<p style="text-align: right;">
			<input type="submit" />
		</p>
		<div class="footnote" style="display: none;">
			<p><b>Page visits: </b>{{:count}}</p>
			<p><b>Time of last update: </b>{{:lastUpdate}}</p>
			<p><b>Author of last update: </b>{{:lastAuthor}}</p>
			<p style="display: none">{{:hash}}s</p>
		</div>
	</form>
</div>

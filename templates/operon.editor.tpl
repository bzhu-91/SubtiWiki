<div class="form-container">
	<div>{{regulationTableEdit:regulations}}</div>
	<br>
	<form action="operon" method="put" type="ajax" mode="{{:updateMode}}">
		<input type="hidden" name="id" value="{{:id}}" />
		<textarea type="monkey" name="data">{{::rest}}</textarea>
		<p style="text-align: right;">
			<a target="operon" class="button delBtn" id="{{:id}}" style="float: left;" mode="{{:delMode}}">Delete</a>
			<input type="submit" />
		</p>
		<div class="footnote">
			<p><b>Page visits: </b>{{:count}}</p>
			<p><b>Time of last update: </b>{{:lastUpdate}}</p>
			<p><b>Author of last update: </b>{{:lastAuthor}}</p>
			<p style="display: none">{{:hash}}</p>
		</div>
	</form>
	{{jsvars:vars}}
</div>

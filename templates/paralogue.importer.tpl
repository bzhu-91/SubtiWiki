<div style="color: tomato">{{list:errors}}</div>
<div>
	<h3>File format:</h3>
	<ul>
		<li>Tabular file, with "\t" as field delimiter and "\n" as line delimiter</li>
		<li>Headers are required</li>
	</ul>
	<h3>Required columns:</h3>
	<ul>
		<li>prot1: the locus of the paralogous protein</li>
		<li>prot2: the locus of the paralogous protein</li>
	</ul>
	<h3>Optional columns:</h3>
	<li>description: description of the interaction, can include citation etc.</li>
	<li>Kd: the Kd value of the interaction</li>
	<h3>Modes:</h3>
	<ul>
		<li>replace: the content of the interaction table will be replace by the content of the uploaded file</li>
		<li>append: add new rows to the interaction table with the content of the uploaded file</li>
	</ul>
</div>
<br>
<form class="box" method="post" enctype="multipart/form-data">
	<p>
		<label>File: </label>
		<input type="file" name="file" /><span>Max. 2MB</span>
	</p>
	<p>
		<label>Mode: </label>
		<input type="radio" name="mode" value="replace" />
		<span>Replace</span>
		<input type="radio" name="mode" value="append" checked/>
		<span>Append</span>
	</p>
	<p style="text-align:right">
		<input type="submit" />
	</p>
</form>
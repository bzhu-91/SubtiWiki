<div style="color:tomato">{{list:errors}}</div>
<div>
	<h3>File format</h3>
	<ul>
		<li>A tabular file</li>
		<li>"\t" as field delimiter, "\n" as line delimiter</li>
		<li>Headers are requireed</li>
		<li>Max. 2MB</li>
		<li>Always has the "locus" column</li>
	</ul>
	<h3>Modes</h3>
	<ul>
		<li>Replace: the content of the Gene table will be replaced by the uploaded file. Please use this mode before import of any other data.</li>
		<li>Patch: the content of the Gene table will be updated by the uploaded file. The uploaded file should have only two columns and the first column should be "locus"</li>
	</ul>
	<h3>Input data type (only for patch mode)</h3>
	<ul>
		<li>Scalar: a scalar value</li>
		<li>Array: a list of text or numbers, seperated by ";"</li>
	</ul>
	<h3>Header of the table</h3>
	<ul>
		<li>Simple key, as "locus" or "title"</li>
		<li>Complex key path, as "the protein->paralogous protein(s)" or "gene->the coordinates"</li>
		<li>It is recommended to use lower case letter in the headers.</li>
	</ul>
	<h3>Other</h3>
	<ul>
		<li>Use "null" for missing values</li>
	</ul>
</div>
<form method="post" class="box">
	<p><label>File: </label><input type="file" name="file" /></p>
	<p><label>Mode: </label>
		<input type="radio" name="mode" value="replace" /><span>Replace</span>
		<input type="radio" name="mode" value="patch" /><span>Patch</span>
	</p>
	<p style="text-align: right">
		<input type="submit" />
	</p>
</form>
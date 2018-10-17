<!-- @author Marie-Theres Thieme <marietheres.thieme@stud.uni-goettingen.de> -->
<form method="POST" enctype="multipart/form-data">
How to use the uploade page:<br>
<ul>
<li>Use import type 'Replace' if you want to add genes to <i>Listi</i>Wiki. The file has to contain the column names in the first row, selected by tabs. The rows are separated by line breaks.<img src="img/replace.jpg"><br>
	
<li>Use import type 'Merge' if you want to add information to genes in <i>Listi</i>Wiki. The file has to contain the column name 'locus' as a reference and the column names to which data should be added, separated by tabs. If you want to add several values to one field, separate them by semicolon and select the insert data type 'Array'. If you want to insert the data after a specific value, for example in the array "The protein", select the correct array for 'insert after'. The function will add the information at the end if no value is selected.<img src="img/merge.png"><br>
</ul>
<input type="file" name="File" id="File">
<input type="submit" value="Update file">
<br>
<br>
<label>Import type: </label>
<input type="radio" id="replace" name="check" value=replace>
<label for="replace">Replace</label>
<input type="radio" id="merge" name="check" value=merge>
<label for="merge">Merge</label>
<br><br>
<label>Insert data type</label>
<select id="type" name="insertType">
	<option value="">Select...</option>
	<option value="array">Array</option>
	<option value="scalar">Scalar</option>
</select>
<br><br>
<label>Choose table</label>
	<select name="tableName">
		<option value="Gene">Gene</option>
	</select>
<br><br>
<label>Insert after</label>
<select id="after" name ="after">
	<option value="The protein => Paralogous protein">The protein => paralogous protein</option>	
	.....
</select>
<!--<input type="text" name="after" /></li>-->
</form>
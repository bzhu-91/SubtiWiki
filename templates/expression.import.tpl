<!-- @author Marie-Theres Thieme <marietheres.thieme@stud.uni-goettingen.de> -->
<form method="POST" enctype="multipart/form-data">
How to use the upload page:<br>
<ul>
<li>The file has to be in JSON format like in the picture. Characteristic of the conditions have to be put in the first array, the values for the genes in the second. Choose the 'type: "T"' for transcriptomic data and "P" for proteomic data.<img src="img/expression.jpg"><br>
</ul>
<input type="file" name="File" id="File">
<input type="submit" value="Update file">

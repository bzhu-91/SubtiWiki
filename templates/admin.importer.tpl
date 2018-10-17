<div style="color:red">{{list:errors}}</div>
<div>
    This interface is for database administrators for general data import.
    <h3>File format:</h3>
    <ul>
        <li>A tabular file</li>
        <li>"\t" as field delimiter and "\n" as line delimiter</li>
        <li>Headers are required</li>
    </ul>
    <h3>Modes: </h3>
    <ul>
        <li>replace: the content of the table table will be replaced by the content of uploaded file</li>
        <li>append: add new rows to the table table with the content of the uploaded file</li>
    </ul>
</div>
<form method="post" enctype="multipart/form-data" class="box">
    <p><label>Table name: </label><input type="text" name="tableName" /></p>
    <p><label>File: </label><input type="file" name="file" /><span>Max. 2MB</span></p>
    <p><label>Mode: </label>
        <input type="radio" name="mode" value="replace" /><span>Replace</span>
        <input type="radio" name="mode" value="append" checked/><span>Append</span>
    </p>
    <p style="text-align:right">
        <input type="submit" />
    </p>
</form>
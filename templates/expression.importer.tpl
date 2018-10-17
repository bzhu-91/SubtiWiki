<div style="padding:10px;background:red;color:white;display:{{:showError}}">Error: {{:errorMsg}}</div>
<div style="padding:10px;background:green;color:white;display:{{:showMsg}}">Message: {{:msg}}</div>
<br>
<form action="expression" method="post" class="box" enctype="multipart/form-data">
    <p><label>Data set name:</label><input name="title" value="{{:title}}" /></p>
    <p><label>Data set description: </label></p>
    <textarea name="description" style="width:100%;padding:10px" rows="10">{{:description}}</textarea>
    <p>
        <label>Data set type:</label>
        <select name="type" id="type">
            <option value="">Please select ... </option>
        </select>
    </p>
    <p>
        <label>Data set citation (pubmed id)</label>
        <input type="text" name="pubmed" value="{{:pubmed}}" />
    </p>
    <p>
        <label>Data set file: </label>
        <input type="file" name="dataset" /><span id="max-size">Max. file size: 2MB</span>
    </p>
    <div style="padding:10px; background: #eee">
        <p><b>Required file format: </b></p>
        <table>
            <tr><td>locus</td><td>value</td></tr>
            <tr><td>BSU00001</td><td>5.000</td></tr>
            <tr><td>BSU00002</td><td>4.000</td></tr>
        </table>
        <p>
            <b>Or:</b>
        </p>
        <table>
            <tr><td>position</td><td>value</td></tr>
            <tr><td>1</td><td>5.000</td></tr>
            <tr><td>2</td><td>3.000</td></tr>
        </table>

        <p>(please use "\t" as field delimiter and "\n" as line delimiter)</p>
    </div>
    <p style="text-align:right">
        <input type="submit" />
    </p>
</form>
{{jsvars:vars}}
<script type="text/javascript">
    $(document).ready(function(){
        types.forEach(function(name){
            $("select#type").append($("<option></option>").html(name).val(name));
        });
        $("select#type").val(type);
        $("textarea[name=description]").summernote();
    });
    
</script>
<div style="padding:10px;background:red;color:white;display:{{:showError}}">Error: {{:errorMsg}}</div>
<div style="padding:10px;background:green;color:white;display:{{:showMsg}}">Message: {{:msg}}</div>
<br>
<form action="expression" method="put" class="box" enctype="multipart/form-data" type="ajax">
    <input type="hidden" name="id" value="{{:id}}" />
    <p><label>Data set name:</label><input name="title" value="{{:title}}" style="width:500px"/></p>
    <p><label>Data set description: </label></p>
    <textarea name="description" style="width:100%;padding:10px" rows="10">{{:description}}</textarea>
    <p>
        <label>Data set citation (pubmed id)</label>
        <input type="text" name="pubmed" value="{{:pubmed}}" />
        <a href="https://www.ncbi.nlm.nih.gov/pubmed/{{:pubmed}}" target="_blank" id="pubmed-link">Link</a>
    </p>
    <p style="text-align:right">
        <input type="submit" />
    </p>
</form>
<form action="expression" method="delete" type="ajax" style="position:relative; float: left;top: -60px; left:10px">
    <input type="hidden" name="id" value="{{:id}}" />
    <input type="submit" value="Delete" style="background:red" />
</form>
{{jsvars:vars}}
<script type="text/javascript">
    $(document).ready(function(){
        $("textarea[name=description]").summernote();
    });
    $(document).on("change", "input[name=pubmed]", function() {
        $("#pubmed-link").prop("href", "https://www.ncbi.nlm.nih.gov/pubmed/" + this.value);
    });
</script>
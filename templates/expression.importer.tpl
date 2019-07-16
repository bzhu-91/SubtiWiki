<div class="box">
    Hint: to upload data, please submit this form first.
</div>
<form action="expression" method="post" class="box" enctype="multipart/form-data" type="ajax">
    <p class="table-row"><label>Data set name:</label><input name="title"/></p>
    <div class="table-row"><label>Data set description: </label>
        <div>
            <textarea name="description" style="width:100%;padding:10px" rows="10"></textarea>
        </div>
    </div>
    <p class="table-row">
        <label>Data set type:</label>
        <select name="type" id="type">
            <option value="">Please select ... </option>
        </select>
    </p>
    <div class="table-row">
        <label>Data set citation (pubmed id)</label>
        <div>
            <input type="text" name="pubmed" value="{{:pubmed}}" />
            <a href="https://www.ncbi.nlm.nih.gov/pubmed/{{:pubmed}}" target="_blank" id="pubmed-link">Link</a>
        </div>
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
    $(document).on("change", "input[name=pubmed]", function() {
        $("#pubmed-link").prop("href", "https://www.ncbi.nlm.nih.gov/pubmed/" + this.value);
    });
</script>
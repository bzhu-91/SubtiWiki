<div style="padding:10px;background:red;color:white;display:{{:showError}}">Error: {{:errorMsg}}</div>
<div style="padding:10px;background:green;color:white;display:{{:showMsg}}">Message: {{:msg}}</div>
<br>
<div action="expression" method="put" class="box form" enctype="multipart/form-data" type="ajax">
    <input type="hidden" name="id" value="{{:id}}" />
    <p class="table-row">
        <label>Data set name:</label>
        <input name="title" value="{{:title}}"/>
    </p>
    <div class="table-row">
        <label>Type</label>
        <span>{{:type}}</span>
    </div>
    <div class="table-row"><label>Data set description: </label>
        <div>
            <textarea name="description" style="width:100%;padding:10px" rows="10">{{:description}}</textarea>
        </div>
    </div>
    <div class="table-row">
        <label>Data set citation (pubmed id)</label>
        <div>
            <input type="text" name="pubmed" value="{{:pubmed}}" />
            <a href="https://www.ncbi.nlm.nih.gov/pubmed/{{:pubmed}}" target="_blank" id="pubmed-link">Link</a>
        </div>
    </div>

    <br/>
    <div style="text-align:right">
        <input type="submit" />
        <form action="expression" method="delete" type="ajax" style="float: left;" class="form-ignore">
            <input type="hidden" name="id" value="{{:id}}" />
            <input type="submit" value="Delete" style="background:red" />
        </form>
    </div>
    <p style="clear: both"></p>
</div>
<form class="box" id="upload-form">
    <input type="hidden" value="{{:id}}" name="condition" />
    <div class="table-row">
        <label>Update data</label>
        <div>
            <p>
                <input type="file" name="file" />
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
        </div>
    </div>
    <p style="text-align: right">
        <input type="submit" value="Upload" />
    </p>
</form>
{{jsvars:vars}}
<script type="text/javascript">
    $(document).ready(function(){
        $("textarea[name=description]").summernote();
    });
    $(document).on("change", "input[name=pubmed]", function() {
        $("#pubmed-link").prop("href", "https://www.ncbi.nlm.nih.gov/pubmed/" + this.value);
    });
    $(document).on("submit", "#upload-form", function (ev){
        ev.preventDefault();
        var data = new FormData(this);
        $.ajax({
            url: "expression",
            type: "post",
            dataType: "json",
            data: data,
            processData: false,
            contentType: false,
            success: function (data) {
                SomeLightBox.alert("Success", "Upload is successful");
            },
            error: function (xhr) {
                SomeLightBox.error(xhr.responseJSON.message);
            }
        });
    });
</script>
<p style="text-align: right">
    <a href="reaction">List of all reactions</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a href="reaction/editor">Add new reaction</a>
</p>
<div class="box">
    <h3>Reaction</h3>
    <p>{{:message}}</p>
    <form action="reaction" method="{{:method}}" type="ajax" style="background:#eee; padding:10px">
        <input type="hidden" name="id" value="{{:id}}" />
        <p><label>Equation: </label>{{:equation}}<i> (This is automatic generated)</i></p>
        <p>
            <label>This reaction is: </label>
            <input type="checkbox" name="reversible" id="AA-CE" {{:checkReversible}}/><label for="AA-CE">Reversible reaction</label>
            <input type="checkbox" name="novel" id="AA-BD" {{:checkNovel}}/><label for="AA-BD">Novel reaction</label>
        </p>
        <p>
            <label>KEGG reaction id: </label><input type="text" name="KEGG" value="{{:KEGG}}" />(optional)
        </p>
        <p>
            <label>E.C.: </label>
            <input type="text" name="EC" value="{{:EC}}"/>(optional)
        </p>
        <p style="text-align: right">
            <input type="submit" />
        </p>
    </form>
    <form action="reaction" method="delete" type="ajax" style="position: relative; float:left; top: -45px; left: 5px">
        <input type="hidden" name="id" value="{{:id}}" />
        <input type="submit" value="Delete" class="delBtn" style="background: red"/>
    </form>
    {{:reactantsEditor}}
    {{:productsEditor}}
    {{:catalystsEditor}}
</div>
{{jsvars:vars}}
<script type="text/javascript">
    $(document).ready(function(){
        $.ajax({
            type: "get",
            url: "pathway",
            headers: {Accept: "application/json"},
            success: function (data, status, xhr) {
                // json object
                data.forEach(function(pathway){
                    $("#select-pathway").append($("<option></option>").html(pathway.title).attr("value", pathway.id));
                });
                if (pathway) {
                    $("#select-pathway").val(pathway);
                }
            }
        });
    });
</script>
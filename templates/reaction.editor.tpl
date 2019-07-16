<p style="text-align: right">
    <a href="reaction">List of all reactions</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a href="reaction/editor">Add new reaction</a>
</p>
<div class="box">
    <h3>Reaction</h3>
    <p>{{:message}}</p>
    <div class="form" action="reaction" method="{{:method}}" type="ajax" style="background:#eee; padding:10px" mode="redirect">
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
        <div style="text-align: right">
            <form action="reaction" method="delete" type="ajax" style="float:left; display: inline-block" class="form-ignore">
                <input type="hidden" name="id" value="{{:id}}" />
                <input type="submit" value="Delete" class="delBtn" style="background: red"/>
            </form>
            <input type="submit" />
            <p style="clear: both"></p>
        </div>
    </div>
    {{:reactantsEditor}}
    {{:productsEditor}}
    {{:catalystsEditor}}
</div>
{{jsvars:vars}}
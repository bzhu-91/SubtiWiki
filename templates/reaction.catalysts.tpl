<h3>Catalysts</h3>
<p><i>Hint: you can edit the complex member after submit the complex name.</i></p>
<div style="background: #eee; padding: 10px">
    {{reactionCatalysts:catalysts}}
    <form action="reaction/catalyst" method="post" type="ajax">
        <input type="hidden" name="reaction" value="{{:id}}" />
        <label>Catalyst type: </label><select name="type" style='width:200px; margin-right: 5px'>
            <option value="-1">Please select ...</option>
            <option value="protein">Protein</option>
            <option value="complex">Protein Complex</option>
            <option value="object">Unspecified</option>
        </select>
        <label>Catalyst: </label><input type="text" name="title" style='width:300px' />
        <label>Modification: </label><input type="text" name="modification" style="width:100px" />
        <input type="submit" />
    </form>
</div>
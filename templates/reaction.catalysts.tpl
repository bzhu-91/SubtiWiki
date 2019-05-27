<br/>
<h3>Catalysts</h3>
<p><i>Hint: you can edit the complex member after submit the complex name.</i></p>
<table class="common">
    <tr>
        <th>Type</th>
        <th>Name</th>
        <th>Modification</th>
        <th>Operations</th>
    </tr>
    {{reactionCatalysts:catalysts}}
    <tr action="reaction/catalyst" method="post" type="ajax" class="form">
        <td>
            <input type="hidden" name="reaction" value="{{:id}}" />
            <select name="type" style='width:200px; margin-right: 5px'>
                <option value="-1">Please select ...</option>
                <option value="protein">Protein</option>
                <option value="complex">Protein Complex</option>
                <option value="object">Unspecified</option>
            </select>
        </td>
        <td>
            <input type="text" name="title" style='width:300px' />
        </td>
        <td>
            <input type="text" name="modification" style="width:100px" />
        </td>
        <td>
            <button type="submit">Add</button>
        </td>
    </tr>
</table>
<br/>
<h3>Reactants</h3>
<table class="common">
    <tr>
        <th>Coefficient</th>
        <th>Type</th>
        <th>Name</th>
        <th>Modification</th>
        <th>Operations</th>
    </tr>
    {{reactionMetabolites:reactants}}
    <tr action="reaction/metabolite" method='post' class="form" mode="reload">
        <td>
            <input type="hidden" name="side" value="L" />
            <input type="hidden" name="reaction" value="{{:id}}" />
            <input type="number" name="coefficient" value="1"/>
        </td>
        <td>
           <select name="type" style="width:120px; margin-right: 5px">
                <option value="metabolite">Metabolite</option>
                <option value="protein">Protein</option>
                <option value="DNA">DNA</option>
                <option value="RNA">RNA</option>
                <option value="complex">Complex</option>
            </select>
        </td>
        <td>
            <div class="metabolite-type-select-options">
                <input type="metabolite" name="metabolite" style="width: 300px;" placeholder="metabolite name"/>
                <input type="protein" style="width: 300px; display: none" placeholder="protein name" />
                <input type="DNA" style="width: 300px; display: none" placeholder="gene name" />
                <input type="RNA" style="width: 300px; display: none" placeholder="gene name" />
                <input type="complex" style="width: 300px; display: none" placeholder="complex name" />
            </div>
        </td>
        <td>
            <input type="text" name="modification" value="{{:modification}}" />
        </td>
        <td>
            <button type="submit">Add</button>
        </td>
    </tr>
</table>
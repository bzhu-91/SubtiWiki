<h3>Reactants</h3>
<div style="padding:10px;background: #eee">
    {{reactionMetabolites:reactants}}
    <form action="reaction/metabolite" method='post' type='ajax'>
        <input type="hidden" name="side" value="L" />
        <input type="hidden" name="reaction" value="{{:id}}" />
        <label>Coefficient:</label><input type="number" name="coefficient" />
        <label>Type: </label><select name="type" style="width:120px; margin-right: 5px">
            <option value="metabolite">Metabolite</option>
            <option value="protein">Protein</option>
            <option value="DNA">DNA</option>
            <option value="RNA">RNA</option>
            <option value="complex">Complex</option>
        </select>
        <label>Name: </label>
        <div style="display: inline" class="metabolite-type-select-options">
            <input type="metabolite" name="metabolite" style="width: 300px;" placeholder="metabolite name"/>
            <input type="protein" style="width: 300px; display: none" placeholder="protein name" />
            <input type="DNA" style="width: 300px; display: none" placeholder="gene name" />
            <input type="RNA" style="width: 300px; display: none" placeholder="gene name" />
            <input type="complex" style="width: 300px; display: none" placeholder="complex name" />
        </div>
        <input type="submit"/>
    </form>
</div>
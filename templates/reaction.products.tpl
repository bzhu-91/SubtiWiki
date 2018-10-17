<h3>Products</h3>
<div style="padding:10px;background: #eee">
    {{reactionMetabolites:products}}
    <form action="reaction/metabolite" method='post' type='ajax'>
        <input type="hidden" name="side" value="R" />
        <input type="hidden" name="reaction" value="{{:id}}" />
        <label>Coefficient: </label><input type="number" name="coefficient" />
        <label>Metabolite: </label><input type="metabolite" name="metabolite" style="width: 300px;"/>
        <input type="submit"/>
    </form>
</div>
<p style="text-align: right"><a href='metabolite'>All metabolites</a></p>
{{metabolite.searchbox.tpl}}
<div class="box">
    <form action="metabolite" method="{{:method}}" type="ajax">
        <input type="hidden" name="id" value="{{:id}}" />
        <p>
            <label>Name</label>
            <input type="text" name="title" value="{{:title}}" style="width: 75%"/>
        </p>
        <p>
            <label>Synonym</label>
            <input type="text" name="synonym" value="{{:synonym}}" style="width: 75%"/>
        </p>
        <p>
            <label>PubChem</label>
            <input type="number" name="pubchem" value="{{:pubchem}}" style="width: 75%"/>
        </p>
        <p style="text-align: right">
            <input type="submit" />
        </p>
    </form>
    <form action="metabolite" method="delete" type="ajax" style="float: left; position: relative; top:-2em">
        <input type="hidden" name="id" value="{{:id}}" />
        <input type="submit" class="delBtn" value="delete" style="background:red"/>
    </form>
</div>
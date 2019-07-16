<p style="text-align: right"><a href='metabolite'>All metabolites</a></p>
{{metabolite.searchbox.tpl}}
<div class="box">
    <div class="form" action="metabolite" method="{{:method}}" type="ajax">
        <input type="hidden" name="id" value="{{:id}}" />
        <p class="table-row">
            <label>Name</label>
            <input type="text" name="title" value="{{:title}}" style="width: 75%"/>
        </p>
        <p class="table-row">
            <label>Synonym</label>
            <input type="text" name="synonym" value="{{:synonym}}" style="width: 75%"/>
        </p>
        <p class="table-row">
            <label>PubChem</label>
            <input type="number" name="pubchem" value="{{:pubchem}}" style="width: 75%"/>
        </p>
        <br/>
        <div style="text-align: right">
            <form class="form-ignore" action="metabolite" method="delete" type="ajax" style="float: left;display: inline-block">
                <input type="hidden" name="id" value="{{:id}}" />
                <input type="submit" class="delBtn" value="delete" style="background:red"/>
            </form>
            <input type="submit" />
            <p style="clear: both"></p>
        </div>
    </div>
</div>
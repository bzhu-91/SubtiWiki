<div>
    <form action="complex/member" type="ajax" method="put" style="display: inline-block">
        <p>
            <label>Coefficient: </label>
            <input type="number" name="coefficient" value="{{:coefficient}}" />
            <label>Member: </label>
            <input type="text" value="{{:member->title}}" readonly/>
            <input type="submit" />
        </p>
        <input type="hidden" name="member" value="{{:memberMarkup}}" />
        <input type="hidden" name="complex" value="{{:complex->id}}" />
    </form>
    <form action="complex/member" type="ajax" method="delete" style="display: inline-block">
        <input type="hidden" name="member" value="{{:memberMarkup}}" />
        <input type="hidden" name="complex" value="{{:complex->id}}" />
        <input type="submit" value="Delete" style="background: red" />
    </form>
</div>
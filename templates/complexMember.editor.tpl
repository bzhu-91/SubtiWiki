<tr action="complex/member" type="ajax" method="put" class="form">
    <td>
        <input type="number" name="coefficient" value="{{:coefficient}}" />
    </td>
    <td>
        <input type="text" value="{{:member->type}}" readonly/>
    </td>
    <td>
        <input type="text" value="{{:member->title}}" readonly/>
    </td>
    <td>
        <input type="text" value="{{:modification}}" readonly/>
    </td>
    <td>
        <p>
            <input type="submit" />
        </p>
        <input type="hidden" name="member" value="{{:memberMarkup}}" />
        <input type="hidden" name="complex" value="{{:complex->id}}" />
        <form action="complex/member" type="ajax" method="delete" class="form-ignore">
            <input type="hidden" name="member" value="{{:memberMarkup}}" />
            <input type="hidden" name="complex" value="{{:complex->id}}" />
            <input type="submit" value="Delete" style="background: red" />
        </form>
    </td>
</tr>
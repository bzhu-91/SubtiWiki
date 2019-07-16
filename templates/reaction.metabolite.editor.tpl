<tr action="reaction/metabolite" method="put" mode="reload" class="form">
	<td>
		<input type="hidden" name="hasMetabolite" value="{{:id}}" />
		<input type="hidden" name="reaction" value="{{:reaction->id}}" />
		<input name="coefficient" value="{{:coefficient}}" />
	</td>
	<td>
		<span>{{:metabolite->type}}</span>
	</td>
	<td>
		<span>{{:metabolite->title}}</span>
	</td>
	<td>
		<input name="modification" value="{{:modification}}"/>
	</td>
	<td>
		<p>
			<button type="submit">Update</button>
			{{:complexEditBtn}}
		</p>
		<form class="form-ignore" action="reaction/metabolite" method="delete" type="ajax" mode="reload">
			<input type="hidden" name="hasMetabolite" value="{{:id}}" />
			<input type="hidden" name="reaction" value="{{:reaction->id}}" />
			<button style="background: red">Delete</button>
		</form>
	</td>
</tr>
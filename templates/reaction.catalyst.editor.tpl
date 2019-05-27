<tr action="reaction/catalyst" method="delete" class="form">
	<td>
		<input type="hidden" name="hasCatalyst" value="{{:id}}" />
		<span>{{:catalyst->type}}</span>
	</td>
	<td>
		<span>{{:catalyst->title}}</span>
	</td>
	<td>
		<span>{{:catalyst->modification}}</span>
	</td>
	<td>
		{{:complexEditBtn}}
		<button style="background: red">Delete</button>
	</td>
</tr>
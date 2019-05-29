<tr action="reaction/catalyst" method="delete" class="form" mode="reload">
	<td>
		<input type="hidden" name="hasCatalyst" value="{{:id}}" />
		<input type="hidden" name="reaction" value="{{:reaction->id}}" />
		<span>{{:catalyst->type}}</span>
	</td>
	<td>
		<span>{{:catalyst->title}}</span>
	</td>
	<td>
		<span>{{:modification}}</span>
	</td>
	<td>
		<span>{{:isNovel}}</span>
	</td>
	<td>
		{{:complexEditBtn}}
		<button style="background: red" type="submit">Delete</button>
	</td>
</tr>
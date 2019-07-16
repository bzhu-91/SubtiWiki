<tr class="form" action="regulation" method="put">
	<td>
		<p>
			<label>Type: </label><span>{{:type}}</span>
			<br>
			<label>Name: </label><span>{{:regulator->title}}</span>
		</p>
	</td>
	<td>
		<input type='text' name='mode' value="{{:mode}}" />
	</td>
	<td>
		<textarea name='description'>{{:description}}</textarea>
	</td>
	<td>
		<p>
			<input type="hidden" name="id" value="{{:id}}" />
			<button type="submit">Update</button>
		</p>
		<form action="regulation" method="delete" class="form-ignore" type="ajax">
			<input type="hidden" name="id" value="{{:id}}" />
			<button style="background: red">Delete</button>
		</form>
	</td>
</tr>
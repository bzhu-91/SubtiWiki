<tr class='regulation' id="{{:id}}">
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
		<button class="updateBtn" target="regulation" id="{{:id}}">Update</button>
		<button class="delBtn" target="regulation" id="{{:id}}">Delete</button>
	</td>
</tr>
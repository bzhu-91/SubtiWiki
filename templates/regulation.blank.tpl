<tr action="regulation" method="post" class="form" mode="reload">
	<td>
		<p>
			<label>Type: </label>
			<select name='type'>
				<option value="protein" selected>Protein</option>
				<option value="riboswitch" >Riboswitch</option>
			</select>
		</p>
		<p>
			<label>Name: </label>
			<input type='protein' name='regulator' required />
		</p>
	</td>
	<td>
		<input type='text' name='mode' />
	</td>
	<td>
		<textarea name='description'>[pubmed|]</textarea>
	</td>
	<td>
		<input type="hidden" name="regulated" value="{{:regulated}}">
		<button type="submit">Add</button>
	</td>
</tr>
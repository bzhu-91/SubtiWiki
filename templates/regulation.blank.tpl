<tr class='regulation blank' regulated="{{:regulated}}">
	<td>
		<p>
			<label>Type: </label>
			<select name='_regulatorType'>
				<option value="protein" selected>Protein</option>
				<option value="riboswitch" >Riboswitch</option>
			</select>
		</p>
		<p>
			<label>Name: </label>
			<input type='text' name='_regulatorName' required />
		</p>
	</td>
	<td>
		<input type='text' name='mode' />
	</td>
	<td>
		<textarea name='description'>[pubmed|]</textarea>
	</td>
	<td>
		<button class="addBtn" target="regulation">Add</button>
	</td>
</tr>
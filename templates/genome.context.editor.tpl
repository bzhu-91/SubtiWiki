<form action="genome" method="put" type="ajax">
	<input type="hidden" name="object" value="{{:object}}" />
	<label>Start</label><input type="number" name="start" value="{{:start}}" />
	<label>Stop</label><input type="number" name="stop" value="{{:stop}}" />
	<label>Strand</label><select name="strand">
		<option value="1">+ strand</option>
		<option value="0">- strand</option>
	</select>
	<button>Send</button>
</form>
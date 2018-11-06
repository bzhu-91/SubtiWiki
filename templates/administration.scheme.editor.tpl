<div style="max-width: 800px">
	<div class="box">
			Hint: drag and drop to re-order the key paths
	</div>
	<p><button id="add-new">+ Add new key path</button></p>
	<div id="keypaths"></div>
	<form method="{{:method}}" type="ajax" id="form-scheme">
		<textarea name="scheme" style="width: 100%; display:none">{{:scheme}}</textarea>
		<p style="text-align: right;"><input type="submit" /></p>
	</form>
	<form style="padding:20px; background: white" id="form-new-keypath">
		<p>
			<label>Key path</label>
			<input name="keypath" />
		</p>
		<p>
			<label>Data type</label>
			<select name="type">
				<option value="a">Scalar</option>
				<option value="b">Array</option>
				<option value="ab">Mixed</option>
			</select>
		</p>
		<p><input type="checkbox" name="ignore"/><label>Ignore in editor</label></p>
		<p style="text-align:right">
			<input type="submit" />
		</p>
		</form>
</div>
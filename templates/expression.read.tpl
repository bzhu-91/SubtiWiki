<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<div style="min-width: 1200px">
	<div style="width: 100%; background: #eee; padding: 20px">
		<form id="search">
			<input type="text" name="geneName" placeholder="gene name">
			<input type="submit">
		</form>
	</div>
	<br>
	<div id="data" style="display: none">
		<div class="section">
			<div class="left">
				<img src="img/legend_expression.png" style="display: inline-block; height: 500px" 	class="img" />
				<img style='margin: 0 auto; height: 500px; display: inline-block;' src="http://genome.jouy.inra.fr/seb/images/details/{{:bsupath}}_map.png"	class="img" />
			</div>
			<div class="right" id="gene-summary"></div>
		</div>
		<br>
		<div class="section" id="section-t">
			<h3>Transcript level</h3>
			<div id="error-t"></div>
			<br>
			<p id="hint"><i>Hint: use mouse wheel to zoom in or out, right click to reset!</i></p>
			<div class="left">
				<div id="diagram-t"></div>
			</div>
			<div class="right">
				<div style="padding: 10px; background: #eee">
					<form id="control-t">
						<p><label>Compare with: </label></p>
						<input type="text" name="geneName" />
						<input type="submit" />
					</form>
					<div id="data-cache-t"></div>
				</div>
				<br/>
				<div id="description-t"></div>
			</div>
		</div>
		<br>
		<div class="section" id="section-p">
			<h3>Protein level</h3>
			<div id="error-p"></div>
			<br>
			<div class="left">
				<div id="diagram-p"></div>
			</div>
			<div class="right">
				<div style="padding: 10px; background: #eee">
					<form id="control-p">
						<p><label>Compare with: </label></p>
						<input type="text" name="geneName" />
						<input type="submit" />
					</form>
					<div id="data-cache-p"></div>
				</div>
				<br/>
				<div id="description-p"></div>
			</div>
		</div>
	</div>
</div>
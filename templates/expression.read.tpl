<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<div style="min-width: 1200px">
	<div style="width: 100%; background: #eee; padding: 20px">
		<form id="search">
			<input type="text" name="geneName" placeholder="gene name">
			<input type="submit">
		</form>
	</div>
	<br>
	<div id="section" style="display: none">
		<div class="left" id="omics-position-browser">
			<img id="exp-legend" src="img/legend_expression.png" style="display: inline-block; max-height: 600px">
			<img id="exp-img" style="margin: 0 auto; max-height: 600px; display: inline-block;" src="http://genome.jouy.inra.fr/seb/images/details/{{:bsupath}}_map.png">
		</div>
		<div class="right" id="gene-summary"></div>
	</div>
	<div id="data" style="display: none"></div>
</div>
{{jsvars:vars}}
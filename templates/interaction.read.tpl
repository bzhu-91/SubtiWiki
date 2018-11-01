<div id="browser-container">
	<div id="network-container"></div>
	<div id="search-block">
		<form id="search">
			<p><input type="text" name="geneName" placeholder="gene name" /><input type="submit" /></p>
		</form>
	</div>
	<div id="display-block" style="display:none">
		<p><label>Target: </label><span id="gene-display"></span></p>
		<p><label>Radius: </label><span id="radius-display"></span></p>
		<p><label>Coverage: </label><span id="coverage-display"></span></p>
	</div>
	<div id="loading">{{:message}}</div>
	<div id="control-block">
		<img src="img/settings_1.png" id="collapsed"/>
		<div id="full">
	 		<p style="margin-top: 20px">
	 			<form id="highlight">
	 				<label>Highlight: </label><input type="text" name="geneName" /><input type="submit" />
	 			</form>
	 		</p>
			<p style="margin-top: 10px">
				<label class="inline">Radius: </label>
					<img src="img/zoomIn.svg" id="increase-radius"  class="icon" />
					<img src="img/zoomOut.svg" id="decrease-radius" class="icon" />
				<label class="inline" id="spacingDis" style="margin-left: 10px">Spacing: </label>
					<img src="img/zoomIn.svg" id="increase-spacing"  class="icon" />
					<img src="img/zoomOut.svg" id="decrease-spacing" class="icon" />
				</p>
			<p id="omics-data-select-container" style="line-height: 2em"></p>
			<p>
				<div id="open-settings" class="button" style="display:inline-block;margin-left:0">
					<img src="img/settings_0.png" class="icon" /> 
					<span>Settings</span>
				</div>
				<img src="img/close.png" id="control-collapse" class="icon" style="float:right"/>
			</p>
			<p style="clear:both"></p>
		 	</div>
	</div>
	<div id="legend"></div>
	<div id="settings">
		<div style="padding: 0 10px">
			<p><h3 style="color: #333">Select color scheme</h3></p>
			<p><input id="node-color" class="jscolor" value="1976d2" /><label style="margin-left: 20px">Nodes</label></p>
			<p><input id="edge-color" class="jscolor" value="848484" /><label style="margin-left: 20px">Edges</label></p>
		</div>
	</div>
	<div id="popup">
		<div id="info-box"></div>
		<img src="img/close.png" id="popup-cancel" class="icon" />
	</div> <!-- popup -->
	<div id="contextmenu-1" class="contextmenu">
		<ul>
			<li id="go-to-gene">Go to this protein</li>
			<li id="cluster-gene">Cluster neighouring proteins</li>
		</ul>
	</div>

	<div id="contextmenu-2" class="contextmenu">
		<ul>
			<li id="export-image">Export image</li>
			<li id="export-csv">Export data (.csv)</li>
			<li id="export-nvis">Export network (.nvis)</li>
		</ul>
	</div>
</div>
{{jsvars:vars}}

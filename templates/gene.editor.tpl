<div>
	<div id="side-bar">
		<a href="javascript:void(0);" class="tab is-active">Gene</a>
		<a href="javascript:void(0);" class="tab">Category</a>
		<a href="javascript:void(0);" class="tab">Paralogues protein(s)</a>
		<a href="javascript:void(0);" class="tab">Interactions</a>
		<a href="javascript:void(0);" class="tab">Translational Regulations</a>
		<a href="javascript:void(0);" class="tab">Operons</a>
		<!-- <a href="javascript:void(0);" class="tab">Protein Complexes</a> -->
		<a href="javascript:void(0);" class="tab">Additional information on regulon</a>
	</div>
	<div id="main">
		<div class="tab-content is-active">
			<div class="box">
				<h3>Hint:</h3>
				<p>Please do NOT delete the [[this]] tag. This is simply a place holder required for the system to properly place the information of the certain section. More detail please refer to <b><a href="FAQ">FAQ</a></b></p>
				<p>The addtion/deletion function of Paralogous protein/interaction/regulation/translation regulation is placed in seperated panels (see left)</p>
			</div>
			<form method="{{:method}}" action="gene" class="box" type="ajax">
				<input type="hidden" name="id" value="{{:id}}" />
				<p><label>Title:</label><input type="text" name="title" value="{{:title}}"></p>
				<div class="editor">
					<textarea name="data" type="monkey" id="gene-editor">{{::rest}}</textarea>
				</div>
				<p style="text-align: right;"><input type="submit" /></p>
			</form>
			<div class="footnote" style="margin-top: 50px; display: {{:showFootNote}}">
				<p style="display: none;">{{:bank_id}}</p>
				<p style="display: none;">{{:id}}</p>
				<p><b>Page visits: </b>{{:count}}</p>
				<p><b>Time of last update: </b>{{:lastUpdate}}</p>
				<p><b>Author of last update: </b>{{:lastAuthor}}</p>
			</div>
		</div>
		<div class="tab-content">
			<div class="content" id="content-category"></div>
		</div>
		<div class="tab-content">
			<button class="newBtn" id="new-paralogue">Add new paralogues protein</button>
			<div class="content" id="content-paralogue"></div>
		</div>
		<div class="tab-content">
			<button class="newBtn" id="new-interaction">Add new protein-protein interactions</button>
			<div class="content" id="content-interaction"></div>
		</div>
		<div class="tab-content">
			<table id="content-regulation" class="content m_table">
				<th>Regulator</th><th>Mode</th><th>Description</th><th>Operation</th>
				{{regulation.blank.tpl}}
			</table>
		</div>
		<div class="tab-content">
			<button class="newBtn" id="new-operon">Add operons</button>
			<div class="content" id="content-operon"></div>
		</div>
		<div class="tab-content">
			<div class="content" id="content-regulon">
				This protein is not a regulator
			</div>
		</div>
	</div>
</div>
{{jsvars:vars}}
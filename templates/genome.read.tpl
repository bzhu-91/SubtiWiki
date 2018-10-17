<div>
	<div>
		<a href="javascript:void(0);" class="tab is-active">Gene</a>
		<a href="javascript:void(0);" class="tab">Gene with flanking regions</a>
		<a href="javascript:void(0);" class="tab">Region</a>
	</div>
	<div>
		<div class="tab-content is-active">
		<form id="gene">
			<p><input type="text" name="geneName" placeholder="gene name, e.g. dnaA"></input><input type="submit"></input></p>
		</form>
		</div>
		<div class="tab-content">
			<form id="flanking">
				<p><div class="inline"><span>Gene: </span><input type="text" name="geneName" placeholder="gene name, e.g. citB" ></input></div>
				<div class="inline"><span>Upstream: </span><input type="number" name="up"></input></div>
				<div class="inline"><span>Downstream: </span><input type="number" name="down"></input></div>
				<input type="submit"></input>
				</p>
			</form>
		</div>
		<div class="tab-content">
		<form id="region">
			<p><div class="inline"><span>Start: </span><input type="nubmer" name="start"></input></div>
			<div class="inline"><span>End: </span><input type="nubmer" name="stop"></input><input type="submit"></input></p></div>
		</form>
		</div>
	</div>
</div>
<div id="data">
	<div id="context-browser"></div>
	<div>
		<div class="left">
			<p style="font-weight: bold;">Current sequence: <span id="dna-sequence-label"></span> <span id="dna-sequence-reversed">(reverse complement)</span></p>
			<p style="font-weight: bold;">Showing <span id="dna-sequence-strand"></span> strand</p>
			<pre><span id="dna-sequence-header"></span><br /><div id="dna-sequence"></div></pre>
		</div>
		<div class="right">
			<div class="gray-box">
				<label for="dna-sequence-show-reverse">Show reverse complement</label><input type="checkbox" id="dna-sequence-show-reverse" />
			</div>
			<div class="gray-box">
				<form id="dna-sequence-search">
					<p><label>Search in DNA sequence</label></p>
					<p style="line-height: 2em">
						<input type="text" name="keyword" placeholder="search in DNA sequence" />
						<input type="submit" value="Send" />
						<a class="button" id="dna-sequence-search-clear">Clear</a>
					</p>
				</form>
				
			</div>
		</div>
	</div>
	<pre id="protein-sequence-container"><hr/><br/><span id="protein-sequence-header"></span><br /><div id="protein-sequence"></div></pre>
</div>
{{jsvars:vars}}
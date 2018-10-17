<h1>FAQ</h1>
<h2>1. How to log in?</h2>
<p></p>
<h3>I lost my password</h3>
<p>Please contact web admin bzhu@gwdg.de to reset the password</p>

<h3>I want to have an account</h3>
<p>Please contact admin jstuelk@gwdg.de to create an account</p>

<h3>I found something not correct</h3>
<p>Please contact admin jstuelk@gwdg.de</p>

<div style="clear: both;height: 20px;"></div>

<h2 id="editing">2. How to edit?</h2>
<p></p>
<h3>A new markup</h3>
<p><i>Listi</i>Wiki has adapted the same markup system as <i>Subti</i>Wiki. This markup system not only indicate the style of the page, but also the structure the page. The text below is a part of source text of gene dnaA</p>

<div><pre style="margin: 0; background: #333; color: white; padding: 15px; min-width: 700px; width: 100%"><code>
* description: AAA  ATPase, replication initiation protein

* locus: BSU00010

* pI: 6.03

* mw: 50.695

* proteinLength: 446

* Gene

** Coordinates:  410 → 1,750

** Phenotypes of a mutant
essential [Pubmed|12682299]
</code></pre>

<div style="clear: both;height: 20px;"></div>
<div class="box" style="min-width: 700px; width: 100%;">
	<div class="m_block"><div class="m_key m_inline">Description</div><div class="m_value m_inline"> AAA ATPase, replication initiation protein</div></div>	
	<div class="m_block"><div class="m_key m_inline">Isoelectric point</div><div class="m_value m_inline"> 6.03</div></div>	
	<div class="m_block"><div class="m_key m_inline">Protein length</div><div class="m_value m_inline"> 446 aa</div></div>	
	<div class="m_key">Gene</div>
	<div class="m_object">
		<div class="m_block"><div class="m_key m_inline">Coordinates</div><div class="m_value m_inline">410 → 1,750</div></div>
		<div class="m_key">Phenotypes of a mutant</div>
		<div class="m_object m_array">
			<li class="m_value">essential [Pubmed|12682299]</li>
		</div>
	</div>
</div>
</div>

<p>In this markup system, all section titles starts with a asterisk " * ", the primary section title with one " * " while the secondary section title with two " * ".</p>

<p>Content of the section can be placed either right behind the title, or under the title. A new line in the edit box corresponds to a bullet point when parsed.</p>

<p>A universal template is provided for gene's page in <i>Listi</i>Wiki. Information can be added to intended section. </p>


<div style="background: orange;color: white; padding: 0.5px 10px">
	<p style="color: white"><b>Note:</b></p>
	<p style="color: white">A space is needed between " * " and the title, as well as between " : " and the content.</p>
</div>

<div style="clear: both;height: 20px;"></div>

<h3>Editing table</h3>
<table class="common" style="font-family: monospace; font-size: larger;">
	<tr><th>Name</th><th>Syntax</th><th>Parsed</th></tr>
	<tr>
		<td>gene</td>
		<td class="rewrite-ignore">[[gene|dnaA]]<br/>
			[[gene|ptsH|Hpr]]</td>
		<td>[gene|6740108089F13116F200C15F35C2E7561E990FEB|dnaA]<br/>
			[gene|29B793660E4D30C0656248F3EF403FEF76FB9025|Hpr]</td>
	</tr>
	<tr>
		<td>protein</td>
		<td class="rewrite-ignore">[[protein|dnaA]]<br/>
			[[protein|ptsH|Hpr]]</td>
		<td>[protein|6740108089F13116F200C15F35C2E7561E990FEB|DnaA]<br/>
			[protein|29B793660E4D30C0656248F3EF403FEF76FB9025|Hpr]</td>
	</tr>
	<tr>
		<td>wiki</td>
		<td class="rewrite-ignore">[wiki|Ribosome]</td>
		<td>[wiki|Ribosome]</td>
	</tr>
	<tr>
		<td>PDB</td>
		<td class="rewrite-ignore">[PDB|2Z4R]</td>
		<td>[PDB|2Z4R]</td>
	</tr>
	<tr>
		<td>PUBMED</td>
		<td class="rewrite-ignore">[Pubmed|23909787,22286949]</td>
		<td>[Pubmed|23909787,22286949]</td>
	</tr>
	<tr>
		<td>REGULON</td>
		<td class="rewrite-ignore">[[regulon|dnaA]]<br/>[[regulon|T-box]]</td>
		<td>[regulon|6740108089F13116F200C15F35C2E7561E990FEB|dnaA]<br/>[regulon|T-box|T-box]</td>
	</tr>
	<tr>
		<td>External url</td>
		<td class="rewrite-ignore">[https://www.google.de/ Google]</td>
		<td>[https://www.google.de/ Google]</td>
	</tr>
	<tr>
		<td><i>I</i>(i)</td>
		<td>&lt;i&gt;italic text&lt;/i&gt;</td>
		<td><i>italic text</i></td>
	</tr>
	<tr>
		<td><b>B</b>(b)</td>
		<td>&lt;b&gt;bold text&lt;/b&gt;</td>
		<td><b>bold text</b></td>
	</tr>
	<tr>
		<td>X<sub>2</sub>(down)</td>
		<td>X&lt;sub&gt;2&lt;/sub&gt;</td>
		<td>X<sub>2</sub></td>
	</tr>
	<tr>
		<td>X<sup>2</sup>(up)</td>
		<td>X&lt;sup&gt;2&lt;/sup&gt;</td>
		<td>X<sup>2</sup></td>
	</tr>
	<tr>
		<td>Citation</td>
		<td>&lt;pubmed&gt;23909787,22286949&lt;/pubmed&gt;</td>
		<td>Citation in green box</td>
	</tr>
</table>

<div style="clear: both;height: 20px;"></div>

<h3>Edit a gene</h3>
<p>In this version of <i>Listi</i>Wiki, the editing page of a gene provides portals of all editing interface concerning this gene.</p>
<img src="img/geneEdit.png" class="box"  style="width: 100%"/>
<table class="m_table">
	<tr><th>Section name</th><td>Description</td></tr>
	<tr><td>Gene</td><td>General information about this gene, transcribed RNA, and translated protein (if exists).</td></tr>
	<tr><td>Interactions</td><td>Information of protein-protein interactions.</td></tr>
	<tr><td>Translational regulations</td><td>regulation which happens at translational level, transcriptional regulation please go to "Operons" section</td></tr>
	<tr><td>Operon</td><td>all operons this gene is in, transcriptional regulation data stored here.</td></tr>
	<tr><td>Additional information on regulon</td><td>If this protein is a regulator, some information about this protein as regulation can be added here, such as the reference papers or etc.</td></tr>
</table>
<div style="clear: both;height: 10px;"></div>

<h3>What is [[this]]</h3>
<p>The gene page in <i>Listi</i>Wiki displays all information about the gene, the transcribed RNA (if exists) and the translated Protein (if exists). This includes the relationships between gene/RNA/protein with other gene/RNA/protein or other biological entities, such as metabolites or operons. Hence in the editing page of the each gene, there are "[[this]]" tags provided as placeholders for the system to properly place the information. The edition of such information, such as paralogous proteins, is enabled through the different tabs in the gene editing page (left panel).</p>
<div style="clear: both;height: 20px;"></div>

<h2 id="regulationBrowser">3. Regulation browser</h2>
<h3>What is radius, and zoom?</h3>
<p>In the new regulation browser, a sub network centering the target gene is displayed. Radius is informally defined as 'how many steps it take from the target gene into the regulatory network'. Formally speaking, radius is the maximal distance between any gene in this sub network to target gene.</p>
<p>The zoom button in the settings panel controls the radius.</p>
<h3>What is coverage?</h3>
<p>It is the ratio of genes in this sub network over all genes in <i>L.monocytogenes</i></p>
<h3>How can I get the underlying data?</h3>
<p>Right click on the white area in the regulation browser. A pop up menu provides the link to export the data as csv, export the network as image, and export the network in NetVis supported format.</p>
<h3>What is NetVis?</h3>
<p>NetVis is a simple cross-platform network visualizer. It can be seen as the offline version of regulation browser. It uses the same visualization engine and from regulation browser network can be exported and viewed later locally.</p>
</div>
	
</div>
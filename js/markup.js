function parseMarkup(txt) {
	rules = {
		'patterns':[
			/\[(https?:[a-z0-9\?=\+\/%\.~\-&\_\$,;\#\(\)]+?) ([^\[\]]+?)\]/ig,
			/\[([^\[\]\|]+?)\|([^\[\]\|]+?)\|([^\[\]\|]+?)\]/gi,
			/\[([^\[\]\|]+?)\|([^\[\]\|]+?)\]/gi,
			/''([^']+?)''/g,
		],
		'replacement': [	
			"<a target='_blank' href='$1'>$2</a>",
			function(m, g1, g2, g3) {
				if (g1 == "gene") {
					g3 = "<i>" + g3 + "</i>";
				}
				var primaryKey = "id";
				if (g1 == "user") {
					primaryKey = "name";
				}
				if (g2 != "search") {
					return "<a href='"+g1+"?"+primaryKey+"="+g2+"'>"+g3+"</a>";
				} else {
					return "<a href='"+g1+"?keyword="+g3+"'>"+g3+"</a>";
				}
			}
			,
			function(m, g1, g2) {
				switch (g1.toLowerCase()) {
					case "pubmed":
						return "<a target='_blank' href='https://www.ncbi.nlm.nih.gov/pubmed/"+g2+"'>PubMed</a>";
					case "pdb":
						return "<a target='_blank' href='http://www.rcsb.org/structure/"+g2+"'>"+g2+"</a>";
					case "ncbi":
						return "<a target='_blank' href='http://www.ncbi.nlm.nih.gov/Structure/mmdb/mmdbsrv.cgi?Dopt=s&uid="+g2+"'><span class='default_ncbi'>"+g2+"</span></a>";
					case 'sw.gene':
						return "<a target='_blank' href='http://subtiwiki.uni-goettingen.de/v3/gene/search/exact/"+g2+"'><i>"+g2+"</i></a>";
					case 'sw.protein':
						return "<a target='_blank' href='http://subtiwiki.uni-goettingen.de/v3/gene/search/exact/"+g2+"'>"+(g2[0].toUpperCase() + g2.slice(1))+"</a>";
					default:
						return m;

				}
			},
			"<i>$1</i>",
		]
	}
	for (var i = 0; i < rules.patterns.length; i++) {
		txt = txt.replace(rules.patterns[i], rules.replacement[i]);
	}
	return txt;
}

var getTextNodesIn = function(el) {
	return $(el).find(":not(iframe)").addBack().contents().filter(function() {
		return this.nodeType == 3;
	});
};

var parse = function(){
	getTextNodesIn(document.body).each(function(idx, textNode){
		if($(textNode).parents("textarea, pre, code, input, .rewrite-ignore, script").length == 0)
			$(textNode).replaceWith(parseMarkup(textNode.textContent));
	});

	keyMapping = {
		'short':[
			'bsu','bioMaterials','phenotypes','locations','mw','activity','geneLength','proteinLength','pI','categories','labs','gfp','ec','twoHybrid','expressionVectors','genomicContext', 'regulons'
		],
		'full':['Locus tag','Biological materials','Phenotypes of a mutant','Localization','Molecular weight','Catalyzed reaction/ biological activity','Gene length','Protein length','Isoelectric point','<a href="category" target="_blank">Categories</a> containing this gene/protein','Labs working on this gene/protein','GFP fusion','E.C.','Two-hybrid system','Expression Vectors','Genomic Context', 'This gene is a member of the following <a href="?mode=regulon&action=list">regulons</>'
		],
	};
	$('.m_key').each(function(i, n){
		var k = n.innerHTML.trim();
 		var idx = keyMapping.short.indexOf(k)
 		if (idx >= 0) n.innerHTML = keyMapping.full[idx];
 		else n.innerHTML = k[0].toUpperCase() + k.slice(1);
	});
}
$(document).ready(parse);

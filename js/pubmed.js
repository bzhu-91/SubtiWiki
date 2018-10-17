$(window).on("load", function(){
	// asynchronically get pubmed
	var pubmeds = $("pubmed");
	var ids = []
	pubmeds.each(function(i){
		var ids = $(this).html();
		var div = $(this).parents(".m_array")[0];
		div = div ? div : this.parentNode;
		$(div).load("pubmed?ids=" + encodeURIComponent(ids), null);
		$(div).html("Loading");
	});
	$(".pubmed").on("click", function(){
		window.open("https://www.ncbi.nlm.nih.gov/pubmed/"+this.id);
	})
});
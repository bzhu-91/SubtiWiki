$(window).on("load", function(){
	// asynchronically get pubmed
	var pubmeds = $("pubmed");
	pubmeds.each(function(i){
		var ids = $(this).html();
		var div = $(this).parents(".m_array")[0];
		div = div ? div : this.parentNode;
		$(div).load("pubmed?ids=" + encodeURIComponent(ids), null);
		$(div).html("Loading");
	});
	
	$("DOI").each(function(i, each){
		var DOIs = each.innerHTML.trim().split(/\s+/g);
		var div = $(each).parents(".m_array")[0];
		div = div ? div : each.parentNode;
		$(div).html("");
		DOIs.forEach(function(doi){
			ajax.get({
				url: "https://crosscite.org/format?doi="+doi+"&style=cell&lang=en-US",
			}).done(function(status, data, error, xhr){
				if (status == 200) {
					var box = $("<div></div>").html(data).addClass("pubmed").on("click", function(){
						window.open("https://doi.org/" + doi);
					});
					$(div).append(box);
				} else {
					var box = $("<div></div>").html(data).addClass("pubmed")
					$(div).append(box);
				}
			});
		})
	})
});

$(document).on("click", ".pubmed", function(){
	window.open("https://www.ncbi.nlm.nih.gov/pubmed/"+this.id);
});
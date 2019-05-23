$(window).on("load", function(){
	var width = $("#floatTop").width() + 40;
	$(window).on("resize", function(){
		var width = $("#floatTop").width() + 40;
	})
 $(window).on("scroll", function(){
	  if (window.pageYOffset > $("#content").position().top) {
		$("#floatTop").show();
		$("#floatTop").css("width", width + "px");
		$("#floatTop").css("position", "fixed");
	  } else {
		$("#floatTop").hide();
		$("#floatTop").css("position", "relative");
	  }
	})
	window.showSearch = function(){
		var el = $("#search");
		var container = $("<div></div>");
		container.css("background", "white");
		container.css("padding", "10px");
		container.append(el);
		var l = new SomeLightBox({
			height: "fitToContent"
		});
		l.replace(container[0]);
		l.ondismiss(function(){
			$("#searchWrapper").append(el);
		});
		l.show();
	}

	$("section#content > #content-wrapper").animate({
		opacity: 1
	},50)
});
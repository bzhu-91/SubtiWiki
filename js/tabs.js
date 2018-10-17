$(window).on("load", function(){
	var tabs = $(".tab");
	var contents = $(".tab-content");
	currentActive = $(".tab.is-active")[0];
	tabs.each(function(i){
		this.content = contents[i];
		this.index = i;
		tabs[i].addEventListener("click", function(ev){
			var clicked = ev.currentTarget;
			if (clicked != currentActive) {
				$(currentActive).removeClass("is-active");
				$(currentActive.content).hide();
				currentActive = clicked;
				$(currentActive).addClass("is-active");
				$(currentActive.content).show();
				if (currentActive.content.ondisplay) {
					currentActive.content.ondisplay();
				}
				window.location.hash = "#" + clicked.index;
			}
		})
	});

	if (window.location.hash) {
		var index = Number(window.location.hash.substr(1));
		tabs[index].click();
	}
});
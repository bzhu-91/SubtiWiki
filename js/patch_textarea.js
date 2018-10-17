// patch textarea auto resizing
$(window).on("load",function(){
	$(document).on('input', "textarea",  resize);
	function resize () {
		var top = window.pageYOffset;
		this.style.height = 'auto';
		this.style.height = (this.scrollHeight) + 'px';
		window.scrollTo(0, top);
	}
	window.patch_textarea = function () {
		var top = window.pageYOffset;
		$("textarea").focus();
		$("textarea").each(function(){
			resize.call(this);
		});
		window.scrollTo(0,top);
	}
	setTimeout(patch_textarea, 5);

	$(document).on('click', 'textarea', resize);
});
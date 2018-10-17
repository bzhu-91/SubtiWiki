$(document).on("click", "a.viewEdit", function() {
	window.open($("base").attr("href") + "history/comparison?commits=" + $(this).attr("commit") + "%20" + $(this).attr("previous"));
});
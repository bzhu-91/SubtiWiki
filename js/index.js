const baseURL = $("base").prop("href");
$("#searchBox").focus();
$(document).ready(function(){
	var send_go = $("#send_go");
	var send_search = $("#send_search");
	var scope = $("#searchScope");
	var box = $("#searchBox")
	var validate = function(str){
		str = str.trim();
		if (str.length < 2) return false;
		if (/^[&!=%+]+$/.test(str)) return false;
		return true;
	}
	box.on("keydown", function(e){
		var x = e.keyCode? e.keyCode : e.charCode
		if (13 == x) {
			send_go.click();
		}
	})
	send_go.on("click", function(){
		if (validate(box.val())) {
			switch(scope.val()) {
				case "gene":
				case "wiki":
					location = baseURL + scope.val() + "?keyword=" + box.val().trim();
					break;
				case 'interaction':
				case 'regulation':
					$.ajax({
						url: "gene?mode=title&keyword=" + encodeURIComponent(box.val().trim()),
						dataType:"json",
						success: function (data) {
							if (data.length > 1) {
								SomeLightBox.error("gene " + box.val() + " is ambigious");
							} else {
								location = baseURL + scope.val() + "?gene=" + data[0].id;
							}
						},
						error: function () {
							SomeLightBox.error("Gene " + box.val() + " not found");
						}
					});
					break;
			}
		}
	})
	send_search.on("click", function(){
		if (validate(box.val())) {
			location =baseURL +  scope.val() + "?keyword=" + box.val().trim();
		}
	})

});

$("#overflow").on("click", function(){
	$("#upper").toggle();
});
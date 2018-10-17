function parseQuery(queryString) {
    var query = {};
    var pairs = (queryString[0] === '?' ? queryString.substr(1) : queryString).split('&');
    for (var i = 0; i < pairs.length; i++) {
        var pair = pairs[i].split('=');
        query[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
    }
    return query;
}


$(document).ready(function(){
	var initCtrlPanel = function () {
		var queryString = window.location.search || "";
		var args = parseQuery(queryString);
		if (queryString != "" && ("filters" in args)) {
			for (var i in args) {
				if (i.startsWith("filter-")) {
					$("#" + i).attr("checked", true);
				}
				if (i.startsWith("operation-")) {
					$("#" + i).attr("checked", true);
				}
			}
		} else {
			$("input[type=checkbox]").attr("checked", true);
		}
		if (args.user != "all") {
			$("#users").find("option[value=all]").removeAttr("selected");
			$("#users").find("option[value='"+args.user+"']").attr("selected", true);
		}
	}
	
		// get all the users and populate the select tag
	ajax.get({
		url: "user?page=1&page_size=500000",
		headers: {Accept: "application/json"}
	}).done(function(status, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if (status == 200) {
			data = data.sort(function(a,b){
				if (a.name == b.name) return 0;
				return a.name > b.name ? 1 : -1;
			});
			$("#users").append($("<option value='all' selected>All</option>"));
			data.forEach(function(user){
				$("#users").append($("<option value='"+encodeURIComponent(user.name)+"'>"+user.name+"</option>"));
			});
			initCtrlPanel();
		}
	})
});

$(document).on("click", "#clear-all", function() {
	$("input[type=checkbox]").prop("checked", false);
	$("input[name=filters]").prop("checked", true);
});

$(document).on("click", "#check-all", function() {
	$("input[type=checkbox]").prop("checked", true);
});
function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
    return null;
}

function buildQuery (obj) {
	var components = [];
	for(var key in obj) {
		components.push(encodeURIComponent(key) + "=" + encodeURIComponent(obj[key]));
	}
	return components.join("&");
}

$(window).load(function(){
	if (max == 0) {
		$("#select-page").hide();
	}

	if (currentInput.page <= 1) {
		$("#previous").hide();
	} else {
		var previousInput = Object.assign({}, currentInput);
		previousInput.page--;
		$("#previous").attr("href", type + "?" + buildQuery(previousInput));
	}

	if (currentInput.page + 1 > max) {
		$("#next").hide();
	} else {
		var nextInput = Object.assign({}, currentInput);
		nextInput.page++;
		$("#next").attr("href", type + "?" + buildQuery(nextInput));
	}

	var select = $("#select-page");
	for (var i = 1; i <= max; i++) {
		option = $("<option></option>");
		option.html("page " + i);
		option.val(i);
		if (currentInput.page == i) {
			option.attr("selected", true);
		}
		select.append(option)
	}

	select.on("change", function(ev){
		currentInput.page = this.value;
		window.location = $("base").attr("href") + type + "?" + buildQuery(currentInput);
	});
})
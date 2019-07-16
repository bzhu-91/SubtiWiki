$(document).ready(function(){
	var container = $("#exporter");
	var currentPerfix = null;
	scheme.forEach(function(each){
		if (each.default == "[[this]]" || each.path[each.path.length-1] == "id") {
			return;
		}
		if (each.path.length == 1) {
			var view = createCheckbox(each.path);
			container.append(view);
		} else {
			prefix = each.path.slice(0, each.path.length-1);
			if (!currentPerfix || !arrayEqual(currentPerfix, prefix)) {
				currentPerfix = prefix;
				for (var i = 0; i < prefix.length; i++) {
					var view = createLabel(prefix.slice(0,i+1));
					container.append(view);
				}
			}
			var view = createCheckbox(each.path);
			container.append(view);
		}
	});
});

var randomId = function () {
  // Math.random should be unique because of its seeding algorithm.
  // Convert it to base 36 (numbers + letters), and grab the first 9 characters
  // after the decimal.
  return '_' + Math.random().toString(36).substr(2, 9);
};

var createCheckbox = function (keypath) {
	var id = randomId();
	var view = $("<div></div>")
		.css("margin-left", (keypath.length - 1) * 30 + "px")
		.css("margin-bottom", "10px");
	var label = $("<label></label>").prop("for",id).html(keypath[keypath.length-1]);
	var checkbox = $("<input type='checkbox' />").prop("id", id).prop("name", keypath.join("->"));
	return view.append(checkbox,label);
}

var createLabel = function (keypath) {
	var id = randomId();
	var view = $("<div></div>").css("margin-left", (keypath.length-1) * 30 + "px").css("margin-bottom", "10px");
	var label = $("<label></label>").html(keypath[keypath.length-1]).css({color: "black", fontSize: "larger"}).attr("for", id);
	var checkbox = $("<input type='checkbox'>").on("change", function(){
		var checked = this.checked;
		$("#exporter").find("[type=checkbox][name]").each(function(){
			var $el = $(this);
			if ($el.attr("name") != keypath.join("->") && $el.attr("name").startsWith(keypath.join("->"))) $el.prop("checked", checked);
		});
	}).prop("id", id);
	return view.append(label, checkbox);
}

function addslashes( str ) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

var arrayEqual = function (a,b) {
	if (a.length != b.length) {
		return false;
	} else {
		for (var i = 0; i < a.length; i++) {
			if (a[i] != b[i]) {
				return false;
			}
		}
		return true;
	}
}

$(document).on("click", "#reset", function(){
	$("input[type=checkbox]").prop("checked", false);
});

$(document).on("click", "#submit", function(){
	var queries = [];
	$("#exporter input[type=checkbox][name]:checked").each(function(i,checkbox){
		queries.push(checkbox.name);
	});
	if (queries.length) {
		$("#hidden-form input[name=query]").val(queries.join(";"));
		$("#hidden-form").trigger("submit");
	} else SomeLightBox.error("Nothing selected");
});
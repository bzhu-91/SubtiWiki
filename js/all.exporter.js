$(document).ready(function(){
	var container = $("#exporter");
	var currentPerfix = null;
	scheme.forEach(function(each){
		if (each.default == "[[this]]") {
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
					var view = createLabel(prefix[i], i);
					container.append(view);
				}
			}
			var view = createCheckbox(each.path);
			container.append(view);
		}
	});
	container.append("<button id='reset' style='float:right;background:orange'>Reset</button>");
	container.append("<button id='submit' style='float:right'>Submit</button>");
});

var randomId = function () {
  // Math.random should be unique because of its seeding algorithm.
  // Convert it to base 36 (numbers + letters), and grab the first 9 characters
  // after the decimal.
  return '_' + Math.random().toString(36).substr(2, 9);
};

var createCheckbox = function (keypath) {
	var view = $("<div></div>");
	view.css("margin-left", (keypath.length - 1) * 30 + "px");
	view.css("margin-bottom", "10px");
	var label = $("<label></label>");
	var id = randomId();
	label.prop("for", id);
	label.html(keypath[keypath.length-1]);
	var checkbox = $("<input type='checkbox' />");
	checkbox.prop("id", id);
	checkbox.prop("name", keypath.join("->"));
	view.append(checkbox);
	view.append(label);
	return view;
}

var createLabel = function (text, indent) {
	var view = $("<div></div>");
	view.css("margin-left", indent * 30 + "px");
	view.css("margin-bottom", "10px");
	var label = $("<label></label>");
	label.html(text);
	view.append(label);
	return view;
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
	$("input[type=checkbox]:checked").each(function(i,checkbox){
		queries.push(checkbox.name);
	});
	if (queries.length) {
		$.ajax({
			url: "gene?query=" + queries.join(";"),
			dataType:"json",
			success: function (data) {
				var csvFile = [];
				for (var i = 0; i < data.length; i++) {
					var line = data[i].slice(1).map(function(each){
						if (each instanceof Array) {
							each = each.join("; ");
						}
						return '"' + addslashes(each) + '"';
					}).join(",");
					csvFile.push(line);
				}
				csvFile = csvFile.join("\n");
				csvFile = csvFile.replace(/[[this]]/i, "");
				fileName = "gene" + (new Date().toLocaleDateString()) + ".csv";
				var blob = new Blob([csvFile]);
				if (window.navigator.msSaveOrOpenBlob) {// IE hack; see http://msdn.microsoft.com/en-us/library/ie/hh779016.aspx
				    window.navigator.msSaveBlob(blob, fileName);
				} else {
				    var a = window.document.createElement("a");
				    a.href = window.URL.createObjectURL(blob, {type: "text/plain"});
				    a.download = fileName;
				    document.body.appendChild(a);
				    a.click();  // IE: "Access is denied"; see: https://connect.microsoft.com/IE/feedback/details/797361/ie-10-treats-blob-url-as-cross-origin-and-denies-access
				    document.body.removeChild(a);
				}
			},
			error: function () {
				SomeLightBox.error("Data export not successful");
			}
		});
	} else SomeLightBox.error("Nothing selected");
});
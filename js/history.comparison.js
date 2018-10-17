$(document).ready(function(){
	if (window.entry0 && window.entry1) {
		var diff = compare();
		show(diff);
	} else {
		$("#comparison").append($("<p>No data found</p>"))
	}
});



var show = function (diff) {
	if (diff.length == 0) {
		$("#comparison").append($("<p>Two versions are identical</p>"))
	}
	var A, B;

	// a is the more recent one
	if (window.entry0.time < window.entry1.time) {
		A = window.entry1;
		B = window.entry0;
	} else {
		A = window.entry0;
		B = window.entry1;
	}

	$("#version-a").html(A.time);
	$("#version-b").html(B.time);

	diff.forEach(function(each) {
		var template = $("#template").clone();
		template.removeAttr("id");

		// show keys
		for (var i = 0; i < each.keyPath.length; i++) {
			var p = $("<p></p>");
			p.html(each.keyPath[i]);
			p.css({
				"margin-left": i * 30  + "px"
			});
			template.find("#keys").append(p)
		}

		// show values
		if (each.valueA) {
			if (Array.isArray(each.valueA)) {
				for (var i = 0; i < each.valueA.length; i++) {
					each.valueA[i] = escapeHtml(each.valueA[i]);
					var p = $("<p></p>");
					p.html(each.valueA[i]);
					template.find("#value-a").append(p)
				}
			} else {
				each.valueA = escapeHtml(each.valueA);
				var p = $("<p></p>");
				p.html(each.valueA);
				template.find("#value-a").append(p)
			}
		} else {
			template.find("#value-a").hide();
		}

		if (each.valueB) {
			if (Array.isArray(each.valueB)) {
				for (var i = 0; i < each.valueB.length; i++) {
					each.valueB[i] = escapeHtml(each.valueB[i]);
					var p = $("<p></p>");
					p.html(each.valueB[i]);
					template.find("#value-b").append(p)
				}
			} else {
				each.valueB = escapeHtml(each.valueB);
				var p = $("<p></p>");
				p.html(each.valueB);
				template.find("#value-b").append(p)
			}
		} else {
			template.find("#value-b").hide();
		}

		// append
		$("#comparison").append(template);
	});

}

var escapeHtml = function (unsafe) {
	return (unsafe + "")
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
 }

var compare = function () {
	var A, B;

	// a is the more recent one
	if (window.entry0.time < window.entry1.time) {
		A = window.entry1.record;
		B = window.entry0.record;
	} else {
		A = window.entry0.record;
		B = window.entry1.record;
	}

	window.ignores.forEach(function(key){
		delete A[key];
		delete B[key];
	});

	var treeA = profile(A);
	var treeB = profile(B);

	var diff = [];

	treeA.forEach(function(pathA){
		var pathB = has(pathA, treeB);
		if (pathB) {
			if (!stringcmp(pathA.data, pathB.data)) {
				diff.push({
					keyPath: pathA.path,
					valueA: pathA.data,
					valueB: pathB.data
				});
			}
		} else {
			diff.push({
				keyPath: pathA.path,
				valueA: pathA.data,
				valueB: null
			});
		}
	});

	treeB.forEach(function(pathB) {
		var pathA = has(pathB, treeA);
		if (!pathA) {
			diff.push({
				keyPath: pathB.path,
				valueA: null,
				valueB: pathB.data
			});
		}
	});
	return diff;
}

var profile = function(obj){
	var keypaths = [];
	var DFS = function(name, obj){
		for(var i in obj){
			if (obj.hasOwnProperty(i)) {
				if (keypaths.length == 0) {
					//init
					keypaths.push({path:[i]});
				} else if (keypaths[keypaths.length - 1].type) {
					// restart a key path
					var last = keypaths[keypaths.length - 1].path;
					var idx = last.indexOf(name);
					var path = last.slice(0,idx + 1);
					path.push(i);
					keypaths.push({path:path});
				} else {
					keypaths[keypaths.length - 1].path.push(i);
				}
				if (obj[i] instanceof Array) {
					keypaths[keypaths.length - 1].type = "b";
					keypaths[keypaths.length - 1].data = obj[i];
				} else if (obj[i] instanceof Object) {
					DFS(i, obj[i]);
				} else {
					keypaths[keypaths.length - 1].type = "a";
					keypaths[keypaths.length - 1].data = obj[i];
				}
			}
		}
	}
	DFS("root",obj);
	return keypaths;
}

/*
	equal checks if two keypaths are equal or not
	this check is case insensitive

*/
var equal = function(path1, path2) {
	if (path1.length != path2.length) return false;
	for (var i = 0; i < path1.length; i++) {
		if(path1[i].trim().toLowerCase() != path2[i].trim().toLowerCase()) return false;
	}
	return true;
};

/*
	checks if a keypath exists in other object (profiled)
*/

var has = function(path, tree){
	for (var i = 0; i < tree.length; i++) {
		if(equal(tree[i].path,path.path)) return tree[i];
	}
	return false;
}

/*
	stringcmp compares scalar types as well as arrays, the comparison is case insensitive and space insensitive
*/
var stringcmp = function(a,b){
	if (a == null) if (b == null) return true; else return false;
	else if (b == null) return false;
	if (a instanceof Array){
		a = a.join("\n");
	}
	if (b instanceof Array) {
		b = b.join("\n");
	}

	a = String(a).replace(/ /g,"");
	b = String(b).replace(/ /g,"");
	return a.toLowerCase() == b.toLowerCase();
}

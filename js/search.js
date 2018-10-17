$(document).ready(function(){
	var validate = function(str){
		str = str.trim();
		if (str.length < 2) return false;
		if (/^[&!=%+]+$/.test(str)) return false;
		return true;
	}
	$(document).on("click", "#send_go", function(){
		var p = $("#send_go").parent();
		var str = p.find("#searchBox").val().trim();
		if (validate(str)) {
			var url = "gene?mode=exact&keyword=" + encodeURIComponent(str);
			var a = document.createElement('a');
			document.body.appendChild(a);
			a.style.display = "none";
			a.href = url;
			a.click();
			document.body.removeChild(a);
		}
	});
	$(document).on("click", "#send_search", function(){
		var p = $("#send_go").parent();
		var str = p.find("#searchBox").val().trim();
		if (validate(str)) {
			var url = "gene?mode=blur&keyword=" + encodeURIComponent(str);
			var a = document.createElement('a');
			document.body.appendChild(a);
			a.style.display = "none";
			a.href = url;
			a.click();
			document.body.removeChild(a);
		}
	});
	$(document).on("click", "#pubmed_search", function(){
		var p = $("#pubmed_search").parent();
		var str = p.find("#searchBox2").val().trim();
		if (validate(str)) {
			var url = "pubmed?keyword=" + encodeURIComponent(str);
			var a = document.createElement('a');
			document.body.appendChild(a);
			a.style.display = "none";
			a.href = url;
			a.click();
			document.body.removeChild(a);
		}
	});
	$(document).on("keydown", "#searchBox", function(e){
		var x = e.keyCode? e.keyCode : e.charCode
		if (13 == x) {
			var p = $("#searchBox").parent();
			p.find("#send_go").click();
		}
	});
	$(document).on("keydown", "#searchBox2", function(e){
		var x = e.keyCode? e.keyCode : e.charCode
		if (13 == x) {
			var p = $("#searchBox2").parent();
			p.find("#pubmed_search").click();
		}
	});
});
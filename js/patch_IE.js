// IE patch
function get_browser() {
	var ua=navigator.userAgent,tem,M=ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || []; 
	if(/trident/i.test(M[1])){
		tem=/\brv[ :]+(\d+)/g.exec(ua) || []; 
		return {name:'IE',version:(tem[1]||'')};
		}   
	if(M[1]==='Chrome'){
		tem=ua.match(/\bOPR|Edge\/(\d+)/)
		if(tem!=null)   {return {name:'Opera', version:tem[1]};}
		}   
	M=M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
	if((tem=ua.match(/version\/(\d+)/i))!=null) {M.splice(1,1,tem[1]);}
	return {
	  name: M[0],
	  version: M[1]
	};
 }
 

$(document).ready(function(){
	 if (get_browser().name == "IE") {
		if (document.getElementById("middle")) {
			document.body.style.display = "block";
		}
	}
	 
	 if (get_browser().name == "IE") {
		window.onresize = function(){
			var height1 = $(window).height(); 
			var height2 = $("#upper").outerHeight();
			var height3 = $("#under").outerHeight();
			var margin1 = Number($("#middle").css("marginTop").replace("px", ""));
			var margin2 = Number($("#middle").css("marginBottom").replace("px", ""));
			
			var height = $("#middle").outerHeight();

			var minHeight = height1 - height2 - height3 - margin1 - margin2;
			if (height < minHeight) {
				$("#middle").css("min-height", height1 - height2 - height3 - margin1 - margin2 + "px");
				console.log("IE patched")
			}
		}
		window.onresize();
	 }
 });



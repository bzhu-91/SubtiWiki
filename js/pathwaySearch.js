function pathwaySearch(id) {
	ajax.get({
		url: "pathway?protein="+id,
		headers: {Accept: "application/json"}
	}).done(function(status, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if(status == 200) {
			var result = $("<div></div>");
			result.css({
				"padding": "10px",
				"background": "white"
			});
			var html = "<h3>Involved pathways:</h3><br/>";
			for (var i = 0; i < data.length; i++) {
				var p = data[i];
				if (p) html += "<div onclick=\"window.open('pathway/?id="+p.id+"')\" style='cursor:pointer' class='box'><p><b>" + p.title + "</b></p></div>"
			}
			result.html(html);
			var l = new SomeLightBox({
				height: "fitToContent",
				width: "500px"
			});
			l.load(result[0]);
			l.show();
		} else SomeLightBox.alert("Not found", "No pathways found");
	})
}

$(window).on("load", function(){
	var categories = $(".category");
	var dictionary = {
		"SW": { // add the root
			subCategories: [],
		}
	};
	categories.each(function(){
		dictionary[this.id] = this;
	});

	var findParent = function(id){
		var id_s = id.split(".")
		id_s.pop();
		return id_s.join(".");
	}

	for(var i in dictionary){
		var parent = findParent(i);
		if (parent != "") {
			if (dictionary[parent].subCategories) {
				dictionary[parent].subCategories.push(dictionary[i])
			} else {
				dictionary[parent].subCategories = [dictionary[i]];
			}
		}
	}
	for(var i in dictionary) {
		if (dictionary[i] instanceof Node) {
			var div = document.createElement("div");
			if (dictionary[i].subCategories) {
				for (var j = 0; j < dictionary[i].subCategories.length; j++) {
					div.appendChild(dictionary[i].subCategories[j]);
				}
				var indicator = document.createElement("span");
				indicator.innerHTML= "[+]"
				indicator.style = "margin-left: 10px; cursor:pointer; font-family: monospace;";
				indicator.div = div;
				indicator.p = dictionary[i];
				indicator.addEventListener("click",function(ev){
					var self = ev.target;
					if (self.innerHTML == "[+]") {
						self.innerHTML = "[-]";
						self.p.parentNode.insertBefore(self.div, self.p.nextSibling);
					} else {
						self.innerHTML = "[+]";
						self.div.remove();
					}
				});
				dictionary[i].appendChild(indicator);
			}
		}
	}
}) 
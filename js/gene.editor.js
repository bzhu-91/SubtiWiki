$(window).on("load", function(){
	$("#search-wrapper").html("<form id='search'><input name='geneName' type='text' /><button>Send</button></form>");

	// load operons
	ajax.get({
		url:"operon/editor?gene="+geneId,
		headers: {Accept: "text/html_partial"}
	}).done(function(status, data, error, xhr){
		if (!error && status == 200) {
			var editor = $(data);
			window.Editor.init(editor.find(".editor"));
			editor.each(function(i, e){
				$(e).addClass("box");
			});
			$("#content-operon").append(editor);
			patch_textarea();
		}
	});

	// load categories
	ajax.get({
		url:"category/editor?gene="+geneId,
		headers: {Accept: "text/html_partial"}
	}).done(function(status, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if (status == 200){
			var editor = $(data);
			$("#content-category").append(editor);
			var selector = new CategorySelector(".category-selector");
			$(".addBtn[target=category]")[0].selector = selector;
		}
	});


	// load interactions
	ajax.get({
		url:"interaction/editor?protein="+geneId,
		headers:{Accept:"text/html_partial"}
	}).done(function(status, data, error, xhr){
		if (!error && status == 200) {
			var editor = $(data);
			window.Editor.init(editor.find(".editor"));
			editor.each(function(i, e){
				$(e).addClass("box");
			});
			$("#content-interaction").append(editor);
			patch_textarea();
		}
	});

	// load paralogues
	ajax.get({
		url:"protein/paralogueEditor?protein="+geneId,
		headers:{Accept:"text/html_partial"}
	}).done(function(status, data, error, xhr){
		if (!error && status == 200) {
			var editor = $(data);
			window.Editor.init(editor.find(".editor"));
			editor.each(function(i, e){
				$(e).addClass("box");
			});
			$("#content-paralogue").append(editor);
			patch_textarea();
		}
	});

	// load translational regulations
	ajax.get({
		url:"regulation/editor?gene="+geneId,
		headers:{Accept: "text/html_partial"}
	}).done(function(status, data, error, xhr){
		if (!error && status == 200) {
			var editor = $(data);
			$("#content-regulation").append(editor);
		}
	});

	// get the regulon
	ajax.get({
		url:"regulon/editor?id="+("protein:"+geneId),
		headers:{Accept:"text/html_partial"}
	}).done(function(status, data, error, xhr){
		if (!error && status == 200) {
			var editor = $(data);
			window.Editor.init(editor.find(".editor"));
			editor.each(function(i, e){
				$(e).addClass("box");
			});
			$("#content-regulon").append(editor);
			patch_textarea();
		}
	});

	window.Editor.init($(".editor"));

});

$(document).on("submit","#search",function(ev){
	ev.stopPropagation();
	ev.preventDefault();
	var geneName = this.geneName.value.trim();
	if (geneName.length > 1) {
		ajax.get({
			url: "gene?keyword="+geneName+"&mode=title",
			headers: {Accept: "application/json"}
		}).done(function(status, data, error, xhr){
			if (error) {
				SomeLightBox.error("Connection to server lost");
			} else if (status == 200) {
				if (data.length > 1) {
					SomeLightBox.error("Gene " + geneName + " is ambigious");
				} else {
					window.location = $("base").attr("href") + "gene/editor?id=" + data[0].id
				}
			} else {
				SomeLightBox.error("Gene " + geneName + " is not found");
			}
		});
	}
	return false;
});

$(document).on("click","#new-operon", function(){
	ajax.get({
		url:"operon/editor",
		headers:{Accept:"text/html_partial"}
	}).done(function(status,data,error,xhr){
		if (!error && status == 200) {
			var editor = $(data);
			editor.addClass("box");
			window.Editor.init(editor.find(".editor"));
			$("#content-operon").prepend(editor);
			if (window.patch_textarea) {
				patch_textarea();
			}
		}
	});
});

$(document).on("click", "#new-interaction", function(){
	ajax.get({
		url:"interaction/editor",
		headers:{Accept:"text/html_partial"}
	}).done(function(status, data, error, xhr){
		if (!error && status == 200) {
			var editor = $(data);
			editor.addClass("box");
			window.Editor.init(editor.find(".editor"));
			$("#content-interaction").prepend(editor);
			if (window.patch_textarea) {
				patch_textarea();
			}
		}
	});
});

$(document).on("click", "#new-paralogue", function(){
	ajax.get({
		url:"protein/paralogueEditor",
		headers:{Accept:"text/html_partial"}
	}).done(function(status, data, error, xhr){
		if (!error && status == 200) {
			var editor = $(data);
			editor.addClass("box");
			window.Editor.init(editor.find(".editor"));
			$("#content-paralogue").prepend(editor);
			if (window.patch_textarea) {
				patch_textarea();
			}
		}
	});
});

$(document).on("click","#new-regulation", function(){
	ajax.get({
		url:"regulation/editor",
		headers:{Accept: "text/html_partial"}
	}).done(function(status, data, error, xhr){
		if (!error && status == 200) {
			var editor = $(data);
			if (editor.prop("tagName") == "TR") {
				editor.attr("regulated", "{gene|" + geneId + "}");
			} else {
				editor.find("tr.regulation.blank").attr("regulated", "{gene|" + geneId + "}");
			}
			$("#content-regulation").append(editor);
		}
	});
});

$(document).on("click",".addBtn[target=category]", function () {
	var self = this;
	var categoryId = self.selector.getValue();
	if (categoryId) {
		ajax.post({
			url: "category/assignment?category="+categoryId+"&gene="+geneId,
			headers: {Accept: "application/json"},
		}).done(function(status, data, error, xhr){
			if (status == 201) {
				// need to create view
				var row = $("<tr></tr>");
				var cell1 = $("<td></td>");
				cell1.html(self.selector.getPresentation());
				var cell2 = $("<td></td>");
				var delBtn = $("<button>Delete</button>");
				delBtn.attr("class", "delBtn");
				delBtn.attr("id", categoryId);
				delBtn.attr("target", "category");
				cell2.append(delBtn);
				row.append(cell1, cell2);
				row.insertBefore($(self).parents("tr"));
				self.selector.reset();
			} else {
				SomeLightBox.error(data.message);
			}
		});
	} else {
		SomeLightBox.error("Please select the category.");
	}
});

$(document).on("click", ".delBtn[target=category]", function(){
	var categoryId = this.id;
	var row = $(this).parents("tr");
	if (categoryId) {
		SomeLightBox.alert("Delete", "Do you want to delete this gene from this category?", {title: "Delete", color: "red", onclick: function(){
			ajax.delete({
				url: "category/assignment?category="+categoryId+"&gene="+geneId,
				headers: {Accept: "application/json"}
			}).done(function(status, data, error, xhr){
				if (status == 204) {
					row.remove();
				} else {
					SomeLightBox.error(data.message);
				}
			})
		}}, {title: "Cancel"});
	}
});
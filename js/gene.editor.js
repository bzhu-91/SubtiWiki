$(window).on("load", function(){
	$("#search-wrapper").html("<form id='search'><input name='geneName' type='text' /><button>Send</button></form>");

	// load operons
	$.ajax({
		url:"genome/editor?object={gene|"+geneId+"}",
		headers: {Accept: "text/html_partial"},
		success: function(html){
			var $editors = $(html);
			$editors.addClass("box");
			$("#content-genomic-context").append($editors);
		}
	});

	// load operons
	$.ajax({
		url:"operon/editor?gene="+geneId,
		headers: {Accept: "text/html_partial"},
		success: function(html){
			var $editors = $(html);
			$editors.addClass("box");
			$editors.find("textarea").monkey();
			$("#content-operon").append($editors);
			patch_textarea();
		}
	});

	// load categories
	$.ajax({
		url:"category/editor?gene="+geneId,
		headers: {Accept: "text/html_partial"},
		success: function (data) {
			var $editors = $(data);
			$("#content-category").append($editors);
			var selector = new CategorySelector(".category-selector");
			$(".addBtn[target=category]")[0].selector = selector;
		}
	})

	// load interactions
	$.ajax({
		url:"interaction/editor?protein="+geneId,
		headers:{Accept:"text/html_partial"},
		success: function (data) {
			var $editor = $(data).addClass("box");
			$editor.find("textarea").monkey();
			$("#content-interaction").append($editor);
			patch_textarea();
		}
	})

	// load paralogous
	$.ajax({
		url:"protein/paralogueEditor?protein="+geneId,
		headers:{Accept:"text/html_partial"},
		success: function (data) {
			var $editor = $(data).addClass("box");
			$editor.find("textarea").monkey();
			$("#content-paralogue").append($editor);
			patch_textarea();
		}
	});

	// load regulations
	$.ajax({
		url:"regulation/editor?gene="+geneId,
		headers:{Accept: "text/html_partial"},
		success: function (data) {
			$(data).find("textarea").monkey();
			$("#content-regulation").append($(data));
		}
	})

	// load regulons
	$.ajax({
		url:"regulon/editor?id="+("protein:"+geneId),
		headers:{Accept:"text/html_partial"},
		success: function (data) {
			var $editor = $(data).addClass("box");
			$editor.find("textarea").monkey();
			$("#content-regulon").html("").append($editor);
			patch_textarea();
		}
	});

	// load complexes
	$.ajax({
		url: "complex?member={gene|"+geneId+"}",
		dataType: "json",
		success: function (data) {
			var $table = $("<table></table>").addClass("common");
			$table.append
			for (let i = 0; i < data.length; i++) {
				const complex = data[i];
				$table.append(
					$("<tr></tr>").append(
						$("<td></td>").append(
							$("<a></a>").attr("href", "complex?id=" + complex.id).html(complex.title)
						),
						$("<td></td>").append(
							$("<a></a>").attr("href", "complex/editor?id=" + complex.id).html("Edit").addClass("button")
						)
					)
				)
			}
			$("#content-complex").html("").append($table);
		},
	});

	$.ajax({
        url: "reaction?catalyst=" + encodeURIComponent(geneTitle)  + "&page=1&page_size=max",
        dataType: "json",
        success: function (data) {
			var $table = $("<table></table>").addClass("common");
			$table.append(
				$("<tr></tr>").append(
					$("<th></th>").html("Reaction"),
					$("<th></th>").html("Operation")
				)
			)
			for (let i = 0; i < data.length; i++) {
				const reaction = data[i];
				$table.append(
					$("<tr></tr>").append(
						$("<td></td>").append(
							reaction.equation
						),
						$("<td></td>").append(
							$("<a></a>").attr("href", "reaction/editor?id=" + reaction.id).html("Edit").addClass("button")
						)
					)
				)
			}
			$("#content-reaction").html("").append($table);
        }
    })

	$("textarea").monkey();
	patch_textarea();
	
});

$(document).on("submit","#search",function(ev){
	ev.stopPropagation();
	ev.preventDefault();
	var geneName = this.geneName.value.trim();
	if (geneName.length > 1) {
		$.ajax({
			url: "gene?keyword="+geneName+"&mode=title",
			dataType: "json",
			success: function (data) {
				if (data.length > 1) {
					SomeLightBox.error("Gene " + geneName + " is ambigious");
				} else {
					window.location = $("base").attr("href") + "gene/editor?id=" + data[0].id
				}
			}
		})
	}
	return false;
});

$(document).on("click","#new-operon", function(){
	$.ajax({
		url:"operon/editor",
		headers:{Accept:"text/html_partial"},
		success: function (data) {
			var $editor = $(data).addClass("box")
			$editor.find("textarea").monkey();
			$("#content-operon").prepend($editor);
			if (window.patch_textarea) {
				patch_textarea();
			}
		}

	})
});

$(document).on("click", "#new-interaction", function(){
	$.ajax({
		url:"interaction/editor",
		headers:{Accept:"text/html_partial"},
		success: function(data){
			var $editor = $(data).addClass("box");
			$editor.find("textarea").monkey();
			$("#content-interaction").prepend($editor);
			if (window.patch_textarea) {
				patch_textarea();
			}
		}
	});
});

$(document).on("click", "#new-paralogue", function(){
	$.ajax({
		url:"protein/paralogueEditor",
		headers:{Accept:"text/html_partial"},
		success: function (data) {
			var $editor = $(data).addClass("box");
			$editor.find("textarea").monkey();
			$("#content-paralogue").prepend($editor);
			if (window.patch_textarea) {
				patch_textarea();
			}
		}
	});
});

$(document).on("click","#new-regulation", function(){
	$.ajax({
		url:"regulation/editor",
		headers:{Accept: "text/html_partial"},
		success: function (data) {
			var $editor = $(data);
			if ($editor.prop("tagName") == "TR") {
				$editor.attr("regulated", "{gene|" + geneId + "}");
			} else {
				$editor.find("tr.regulation.blank").attr("regulated", "{gene|" + geneId + "}");
			}
			$("#content-regulation").append(editor);
		}
	});
});

$(document).on("click",".addBtn[target=category]", function () {
	var self = this;
	var categoryId = self.selector.getValue();
	if (categoryId) {
		$.ajax({
			type: "post",
			url: "category/assignment?category="+categoryId+"&gene="+geneId,
			dataType: "json",
			statusCode: {
				201: function (data) {
						// need to create view
					var cell1 = $("<td></td>").html(self.selector.getPresentation());
					var cell2 = $("<td></td>");
					var delBtn = $("<button>Delete</button>")
						.attr("class", "delBtn")
						.attr("id", categoryId)
						.attr("target", "category");
					var cell2 = $("<td></td>").append(delBtn);
					$("<tr></tr>")
						.append(cell1, cell2)
						.insertBefore($(self).parents("tr"));
					self.selector.reset();
				},
				500: function (error) {
					SomeLightBox.error(error.message);
				}
			}
		})
	} else {
		SomeLightBox.error("Please select the category.");
	}
});

$(document).on("click", ".delBtn[target=category]", function(){
	var categoryId = this.id;
	var $row = $(this).parents("tr");
	if (categoryId) {
		SomeLightBox.alert("Delete", "Do you want to delete this gene from this category?", {title: "Delete", color: "red", onclick: function(){
			$.ajax({
				type: "delete",
				url: "category/assignment?category="+categoryId+"&gene="+geneId,
				dataType: "json",
				statusCode: {
					204: function () {
						$row.remove();
					},
					500: function (error) {
						SomeLightBox.error(error.message);
					}
				}
			})
		}}, {title: "Cancel", color: "gray"});
	}
});
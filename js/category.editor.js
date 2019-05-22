$(document).ready(function(){
	$("textarea").monkey();
	window.blankForm = new SomeLightBox({
		width: "400px",
		height: "auto"
	});
	blankForm.loadById("blank");
});

$(document).on("click", "button.addBtn[target=category]", function() {
	blankForm.show();
});

var deleteGene = function (geneId) {
	$.ajax({
		type: "delete",
		url: "category/assignment?gene=" + geneId + "&category=" + categoryId,
		dataType:"json",
		statusCode: {
			204: function () {
				$(self).parents("tr").remove();
				if ($("tr").length == 2) {
					window.location.reload();
				}
			},
			500: function () {
				SomeLightBox.error("An unexpected error has happened. Deletion is not successful");
			},
			403: function () {
				SomeLightBox.error("Permission denied");
			}
		}
	})
}

$(document).on("click", "button.delBtn[target=gene]", function () {
	var geneId = this.id;
	SomeLightBox.alert({
		title: {
			color: "red",
			title: "Delete"
		},
		message: "Do you want to remove this gene?",
		confirm: {
			title: "Delete",
			color: "red",
			onclick: function (){
				deleteGene(geneId);
			}
		},
		cancel: true
	});
});

/**
 * create the editor row for a gene
 * @param  {Gene} gene gene
 * @return {tr}      table row
 */
var createRow = function (gene) {
	var tr = $("<tr></td>");
	var nameTd = $("<td></td>");
	var functionTd = $("<td></td>");
	var operationTd = $("<td></td>");

	nameTd.append($("<a href='gene?id='"+gene.id+"'>"+gene.title+"</a>"));
	functionTd.html(parseMarkup(gene.function));
	var delBtn = $("<button class='button delBtn' target='gene' id='"+gene.id+"'>Delete</button>");
	operationTd.append(delBtn);

	tr.append(nameTd);
	tr.append(functionTd);
	tr.append(operationTd);

	return tr;
}

var validateName = function (name) {
	$.ajax({
		url: "gene?keyword=" + encodeURIComponent(name),
		dataType:"json",
		success: function (data) {
			if (data.length != 1) {
				SomeLightBox.error("Gene " + name + " is ambigous")
			} else {
				var gene = data[0];
				addGene(gene)
			}
		},
		error: function () {
			SomeLightBox.error("Gene " + name + " does not exit");
		}
	})
}

var addGene = function (gene) {
	var assignment = {
		gene: gene.id,
		category: categoryId
	}
	$.ajax({
		type:"post",
		url: "category/assignment",
		data: assignment,
		dataType:"json",
		statusCode: {
			201: function () {
				updateView(gene);
			},
			500: function (data) {
				SomeLightBox.error(data.message);
			}
		}
	});
}

var updateView = function (gene) {
	if ($("tr").length == 2) {
		window.location.reload();
	} else {
		var row = createRow(gene);
		row.insertAfter($("tr")[1]);
	}
}

$(document).on("click", "button.addBtn[target=gene]", function() {
	var input = $(this).parents("tr").find("input");
	var geneName = input.val();
	if (geneName.length >= 2) {
		validateName(geneName);
	}
});

var deleteCategory = function () {
	$.ajax({
		type: "delete",
		url: "category?id=" + categoryId,
		dataType:"json",
		statusCode: {
			204: function () {
				SomeLightBox.alert("Success", "Deletion is successful");
				setTimeout(function(){
					window.location = "category"
				},300);
			},
			500: function (data) {
				SomeLightBox.error(data.message);
			}
		}
	})
}

$(document).on("click", ".delBtn[target=category]", function() {
	SomeLightBox.alert({
		title: {
			color: "red",
			title: "Delete"
		},
		message: "Do you want to remove this category?",
		confirm: {
			title: "Delete",
			color: "red",
			onclick: function (){
				deleteCategory();
			}
		},
		cancel: true
	});
});


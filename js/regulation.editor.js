var empty = [
	"insert text here",
	"\\[pubmed\\|\\]",
	"<pubmed></pubmed>",
	"\\[\\[gene\\|\\]\\]",
	"\\[\\[protein\\|\\]\\]",
	"\\[SW\\|\\]",
	"\\[PDB\\|\\]",
	"\\[\\]",
	"\\[external_url text_to_be_shown\\]"
];

var removeEmpty = function(str) {
	for (var i = 0; i < empty.length; i++) {
		str = str.replace(new RegExp(empty[i], "gi"), "");
	}
	return str;
}

$(document).on("click", ".updateBtn[target=regulation]", function(){
	var row = $(this).parents("tr");
	var mode = row.find("input[name=mode]").val().trim();
	var description = row.find("textarea[name=description]").val().replace(/\[pubmed\|\]/gi, "").trim();
	var id = this.id;

	if (description.length) {
		description = removeEmpty(description);
	}

	if (mode.length == 0) {
		SomeLightBox.error("Mode is required");
	} else {
		ajax.put({
			url: "regulation?id=" + id,
			data: ajax.serialize({
				mode: mode,
				description: description
			}),
			headers: {Accept: "application/json"}
		}).done(function(status, data, error, xhr){
			if (status == 200) {
				SomeLightBox.alert("Success", "Update is succcessful.");
			} else if (error) {
				SomeLightBox.error("Server connection is lost.");
			} else {
				SomeLightBox.error(data.error);
			}
		});
	}
});

$(document).on("click", ".delBtn[target=regulation]", function(){
	var id = this.id;
	var row = $(this).parents("tr");
	SomeLightBox.alert({
		title: {
			title: "Delete",
			color: "red"
		},
		message: "Do you want to remove this regulation?",
		confirm: {
			title: "Delete",
			color: "red",
			onclick : function () {
				ajax.delete({
					url: "regulation?id=" + id,
					headers: {Accept: "application/json"}
				}).done(function(status, data, error, xhr){
					if (status == 204) {
						row.remove();
					} else if (error) {
						SomeLightBox.error("Server connection is lost.");
					} else {
						SomeLightBox.error(data.error);
					}
				});
			}
		},
		cancel: {
			title: "Cancel"
		}
	})
});

var createView = function (id, regulator, mode, description) {
	if (description.length == 0) {
		description = "[pubmed|]";
	}
	var row = $("<tr id='"+id+"'></tr>");
	var cell1 = $("<td><label>Type: </label><span>"+regulator.type+"</span><br><label>Name: </label><span>"+regulator.title+"</span></td>");
	var cell2 = $("<td><input name='mode' value='" + mode + "' /></td>");
	var cell3 = $("<td><textarea name='description'>" + description + "</textarea></td>");
	var cell4 = $("<td><button class='updateBtn' target='regulation' id='"+id+"'>Update</button> "+(showDelBtn ? "<button class='delBtn' target='regulation' id='"+id+"'>Delete</button>": "")+"</td>");
	row.append(cell1, cell2, cell3, cell4);
	return row;
}

// this could be problematic when there are multiple operons in the gene.editor.js
var addRegulation = function (tableView, regulator, regulated,  mode, description) {
	var data = {
		regulator: "{" + regulator.type + "|" + regulator.id + "}",
		regulated: regulated,
		mode: mode,
		description: description
	}
	ajax.post({
		url: "regulation",
		headers: {Accept: "application/json"},
		data: ajax.serialize(data)
	}).done(function(status, data, error, xhr) {
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if (status == 201) {
			var view = createView(data.newid, regulator, mode, description);
			view.insertAfter(tableView[0].rows[1]);
			$("tr.regulation.blank").find("input, textarea").val("");
		} else {
			SomeLightBox.error(data.message);
		}
	});
}

$(document).on("click", ".addBtn[target=regulation]", function(){
	var table = $(this).parents("table");
	var row = $(this).parents("tr");
	var regulated = row.attr("regulated");
	var type = row.find("select[name=_regulatorType]").val();
	var name = row.find("input[name=_regulatorName]").val().trim();
	var mode = row.find("input[name=mode]").val().trim();
	var description = row.find("textarea[name=description]").val().trim();

	if (description.length) {
		description = removeEmpty(description);
	}

	if (mode.length == 0) {
		SomeLightBox.error("Mode is required");
	} else if (name.length < 2) {
		SomeLightBox.error("Please give the name of the regulator.");
	} else if (type == "protein") {
		ajax.get({
			url: "gene?keyword=" + name + "&mode=title",
			headers: {Accept: "application/json"}
		}).done(function(status, data, error, xhr){
			if (error) {
				SomeLightBox.error("Connection to server lost.");
			} else if(status == 200) {
				if (data.length > 1) {
					SomeLightBox.error("Protein " + name + " is ambigious.");
				} else {
					var protein = data[0];
					protein.type = "protein";
					addRegulation(table, protein, regulated, mode, description);
				}
			} else if (status == 404) {
				SomeLightBox.error("Protein " + name + " is not found.");
			}
		})
	} else {
		addRegulation(
		table,
		{
			type:"riboswitch",
			title: name,
			id: name
		}, regulated, mode, description);
	}
	
})
$(document).on("click","tr.regulation .updateBtn", function(){
	var id = this.id;
	var data = {};
	$(this).parents("tr.regulation").find("[name]").each(function(idx){
		data[$(this).attr("name")] = $(this).val();
	});
	$.ajax({
		type: "put",
		dataType: "json",
		url:"regulation?id=" + encodeURIComponent(id),
		data: data,
		statusCode: {
			200: function () {
				var l = SomeLightBox.alert("Success", "Update successful");
				setTimeout(function(){
					l.dissmiss();
				}, 300);
			},
			500: function (error) {
				var l = SomeLightBox.error(error.message);
				setTimeout(function(){
					l.dissmiss();
				}, 300);
			},
			400: function (error) {
				var l = SomeLightBox.error(error.message);
				setTimeout(function(){
					l.dissmiss();
				}, 300);
			}
		},
	})
});

$(document).on("click","tr.regulation.blank .addBtn", function(){
	var $tr = $(this).parents("tr.regulation")
	var data = {};
	$tr.find("[name]").each(function(idx){
		data[$(this).attr("name")] = $(this).val();
	});
	data.regulated = $tr.attr("regulated");
	data.description = data.description.replace(/\[pubmed\|\]/g,"");
	var sendForm = function (data) {
		$.ajax({
			type: "post",
			dataType: "json",
			url:"regulation",
			data: data,
			statusCode: {
				201: function (data) {
					$.ajax({
						url:"regulation/editor?id=" + data.newid,
						headers: {Accept: "text/html_partial"},
						success: function (html){
							$tr.after(html);
							$tr.find("[name]").each(function(idx){
								$(this).val("");
							});
						}
					})
				},
				500: function (error) {
					var l = SomeLightBox.error(error.message);
					setTimeout(function(){
						l.dissmiss();
					}, 300);
				},
				400: function (error) {
					var l = SomeLightBox.error(error.message);
					setTimeout(function(){
						l.dissmiss();
					}, 300);
				}
			},
		})
	}
	if (data._regulatorType == "protein") {
		$.ajax({
			url: "gene?keyword=" + encodeURIComponent(data._regulatorName) + "&mode=title",
			dataType: "json",
			success:function(searchResult){
				var gene;
				if (searchResult.length != 1) {
					var exactMatch = searchResult.filter(function(gene){
						return gene.title.toLowerCase() == data._regulatorName.toLowerCase();
					});
					if (exactMatch.length != 1) {
						SomeLightBox.error("Protein name " + data._regulatorName + " is ambigious.");
					} else gene = exactMatch[1];
				} else {
					gene = searchResult[0];
				}
				data.regulator = "{protein|" + gene.id + "}";
				sendForm(data);
			} 
		})
	} else {
		data.regulator = "{riboswitch|" + data._regulatorName + "}"
		sendForm(data);
	}
	
});

$(document).on("click","tr.regulation .delBtn", function(){
	var id = this.id;
	var $tr = $(this).parents("tr.regulation")
	SomeLightBox.alert({
		title: "Delete",
		message: "Are you sure to delete this regulation?",
		confirm: {
			title: "Delete",
			color: "red",
			onclick: function () {
				$.ajax({
					type: "delete",
					dataType: "json",
					url:"regulation?id=" + encodeURIComponent(id),
					statusCode: {
						204: function () {
							$tr.remove();
						},
						400: function (error) {
							var l = SomeLightBox.error(error.message);
							setTimeout(function(){
								l.dissmiss();
							}, 300);
						}
					},
				})
			}
		},
		cancel: {
			title: "Cancel",
			color: "gray"
		},
		theme: "red"
	})
});

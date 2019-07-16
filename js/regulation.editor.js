$(document).on("change", "tr.form[action=regulation] select[name=_regulatorType]", function(){
	$("tr.form[action=regulation] select[name=_regulatorType]").attr("type", this.value == "protein" ? "protein" : "text");
});
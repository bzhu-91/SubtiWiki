$(document).on("change", "#add-complex-member select[name=type]", function(){
	$("#add-complex-member input[name=member]").attr("type", this.value);
})
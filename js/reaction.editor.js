$(document).on("change", "select[name=type]", function () {
	$(this).parents("form,.form").find(".metabolite-type-select-options > input").hide();
	$(this).parents("form,.form").find("input[type=" + this.value + "]").show().attr("name", "metabolite")
});
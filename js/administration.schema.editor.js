var Scheme = Scheme || function (path, type, ignore) {
    this.path = path;
    this.type = type;
    this.ignore = ignore;
}

Scheme.prototype.getView = function () {
    var box = $("<div></div>").prop("style", "background:#eee;padding:5px 10px;margin-bottom:5px");
    var span = $("<div id='path'></div>").html(this.path.join(" -> "))
    .css({
        display: "inline-block"
    });
    var select = $("<select id='type'></select>")
    .append($("<option value='a'>Scalar</option>"))
    .append($("<option value='b'>Array</option>"))
    .append($("<option value='ab'>Mixed</option>"))
    .css({
        float: "right",
        marginRight: "2.5px"
    });
    select.val(this.type);
    var check = $("<input></input>").attr("type", "checkbox").prop("checked", this.ignore).css({float:"right",marginTop: "5px"});
    var label_check = $("<label></label>").html("Ignore in editor").css("float", "right");
    var editBtn = $("<button>Edit</button>").css({
        float: "right"
    }).on("click", function () {
        SomeLightBox.prompt("Key path editor", span.html().replace("&gt;", ">"), function (val) {
            span.html(val);
        });
    });
    var delBtn = $("<button>Delete</button>")
    .css({
        background: "tomato",
        float: "right",
    }).on("click", function() {
        SomeLightBox.alert({
            title: "Delete",
            message: "Would you like to delete this key path? This will not affect the data stored in the table. However, this key path will become hidden.",
            confirm:{
                color: "tomato",
                title: "Delete",
                onclick: function () {
                    box.remove();
                }
            },
            cancel: {
                color: "gray",
                title: "cancel"
            },
            theme:"tomato"
        });
    });
    box.append(span, delBtn, editBtn, select, label_check, check, $("<p></p>").css("clear", "both"));
    return box;
}

Scheme.fromView = function (box) {
    var keypath = $(box).find("#path").html().replace("&gt;", ">").split("->").map(function(a){
        return a.trim();
    });
    var type = $(box).find("#type").val();
    var ignore = $(box).find("input[type=checkbox]").prop("checked");
    return new Scheme(keypath, type, ignore);
}

$(document).ready(function(){
    var scheme = JSON.parse($("textarea[name=scheme]").val());
	scheme.forEach(function(each,idx){
        var s = new Scheme(each.path, each.type, each.ignore);
        $("#keypaths").append(s.getView());
	});
    $("#keypaths").sortable();
    $("#add-new").on("click", function(){
        lb.show();
    });
    var lb = new SomeLightBox({
        width: "400px",
        height: "auto",
        autoForm: true
    });
    lb.loadById("form-new-keypath");
    lb.ondismiss(function(ev){
        if (ev && ev.formData) {
            var keypath = ev.formData.keypath;
            var type = ev.formData.type;
            var ignore = ev.formData.ignore;
            var scheme = new Scheme(keypath.replace("&gt;", ">").split(" -> "), type, ignore);
            $("#keypaths").prepend(scheme.getView());
        } 
    });

    $("#form-scheme").on("submit", function(ev){
        var scheme = [];
        $("#keypaths").children().each(function(idx, node){
            scheme.push(Scheme.fromView(node));
        });
        $("textarea[name=scheme]").val(JSON.stringify(scheme));
    });
});
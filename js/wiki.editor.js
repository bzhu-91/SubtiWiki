$(document).ready(function(){
    $("textarea[name=article]").summernote({
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['fontname', 'bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['insert', ['picture', 'link', 'table', 'hr']],
            ['help', ['fullscreen','codeview','undo','redo','help']]
        ]
    });
    $("pubmed").each(function(idx, each){
        each.outerHTML = each.outerHTML.replace(/</g, "&lt;").replace(/>/g, "&gt;");
    });
});

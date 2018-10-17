$(document).ready(function(){
    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],		// toggled buttons
        ['blockquote', 'code-block'],

        ['link','image'],
        
        [{ 'header': 1 }, { 'header': 2 }],				 // custom button values
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],		// superscript/subscript
        [{ 'indent': '-1'}, { 'indent': '+1' }],			// outdent/indent
        [{ 'direction': 'rtl' }],						 // text direction

        [{ 'size': ['small', false, 'large', 'huge'] }],	// custom dropdown
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

        [{ 'color': [] }, { 'background': [] }],			// dropdown with defaults from theme
        [{ 'font': [] }],
        [{ 'align': [] }],

        ['clean']										 // remove formatting button
    ];

    window.editor = new Quill("#wiki-editor", {
        modules: {
            toolbar: toolbarOptions,
        },
        placeholder: "please insert text here",
        theme: "snow"
    });

    if (window.article) {
        editor.setContents(window.article);
    }
});

$(document).on("submit", "form[action=wiki]", function(){
    var box = $("<textarea></textarea>");
    box.attr("name", "article");
    box.css("display", "none");
    box.val(JSON.stringify(editor.getContents()));
    $(this).append(box);
});

$(document).on("click", ".delBtn[target=wiki]", function() {
    if (this.id) {
        var id = this.id;
        ajax.delete({
            url: "wiki?id="+id,
            headers: {Accept: "application/json"}
        }).done(function(status, data, error, xhr){
            if (status == 204) {
                SomeLightBox.alert("Success", "Deletion is succesful");
                setTimeout(function(){
                    window.location = "wiki";
                }, 300);
            } else {
                SomeLightBox.error(data.message);
            }
        })
    }
})
$(document).ready(function(){
    if (window.article) {
        var viewer = new Quill('#wiki-viewer', {
            modules: {
            toolbar: false
            },
            theme: 'bubble',
            readOnly: true
        });
        viewer.setContents(window.article);
    }
})
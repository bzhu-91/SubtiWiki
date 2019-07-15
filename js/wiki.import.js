// this script will download the wiki page from the old subtiwiki and try to add the page content to this new database

/**
 * download the page in the old subtiwiki with the same title
 */
function download (title, callback) {
    // XSS protection
    var url = "http://subtiwiki.uni-goettingen.de/wiki/index.php/" + encodeURIComponent(title.replace(/ /g, "_"));
    $.ajax({
        url: url,
        success: function (response) {
            var $html = $(response);
            var $contentDiv = $html.find("div#bodyContent");
            var result = {
                title: title,
                article: $contentDiv.prop("outerHTML")
            }
            if (callback) callback(result)
        },
        error: function (xhr) {
            if (callback) callback(false);
        }
    });
}

/**
 * add the downloaded page to the new database
 */
function upload (title, article, callback) {
    // use ajax to call RESTful API: POST:/wiki
    $.ajax({
        type: "post",
        url: "wiki",
        data: {
            title: title,
            article: article
        },
        dataType: "json",
        success: function(content){
            if (callback) callback(true);
        },
        error: function (xhr) {
            if (callback) callback(false);
        }
    });
}

// main logic
function main (title) {
    // download the page
    // then upload the page
    var uploadCallback = function (result) {
        if (result) {
            window.location.reload();
        } else {
            alert("error");
        }
    }
    /**
     * @param {object/null} result the result of download
     */
    var downloadCallback = function (result) {
        if (result) {
            // do something
            upload(result.title, result.article, uploadCallback);
        } else {
            // do something else
            alert("page does not exist");
        }
    }
    download(title, downloadCallback);
}

$(document).on("click","button#import", function(ev){
    main(pageTitle);
})
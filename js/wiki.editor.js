$(document).ready(function(){
    var pubmedLinkBtn = function (context) {
        var ui = $.summernote.ui;
        var button = ui.button({
            contents: "<i class='fa fa-child' /> Pubmed link",
            tooltip: "add a link to pubmed",
            click: function () {
                SomeLightBox.prompt("Pubmed ids (seperated by ,)", "", function(val){
                    var $link = $("<a></a>").html("PubMed").attr("href", "https://www.ncbi.nlm.nih.gov/pubmed/" + val.replace(/ /g, "")).prop("contenteditable", false);
                    context.invoke('editor.insertNode', $link[0]);
                });
            }
        });
        return button.render();
    }
    var pubmedBlockBtn = function (context) {
        var ui = $.summernote.ui;
        var button = ui.button({
            contents: "<i class='fa fa-child' /> Pubmed block",
            tooltip: "add citation blocks",
            click: function () {
                SomeLightBox.prompt("Pubmed ids (seperated by ,)", "", function(val){
                    $.ajax({
                        url: "pubmed?ids=" + val.replace(/ /gi, ""),
                        success: function (html){
                            var $wrapper = $("<div></div>").append($(html).prop("contenteditable", false));
                            context.invoke('editor.insertNode', $wrapper[0]);
                        }
                    })
                });
            }
        });
        return button.render();
    }
    var editor = $("textarea[name=article]").summernote({
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['style','fontname', 'bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['insert', ['picture', 'link', 'table', 'hr']],
            ['help', ['fullscreen','codeview','undo','redo','help']],
            ['mybutton', ['pubemdLink', 'pubmedBlock']]
        ],
        buttons: {
            pubemdLink: pubmedLinkBtn,
            pubmedBlock: pubmedBlockBtn
        }
    });
    $("pubmed").each(function(idx, each){
        each.outerHTML = each.outerHTML.replace(/</g, "&lt;").replace(/>/g, "&gt;");
    });
    const fastInput = {"Alpha":"Α","alpha":"α","Beta":"Β","beta":"β","Gamma":"Γ","gamma":"γ","Delta":"Δ","delta":"δ","Epsilon":"Ε","epsilon":"ε","Zeta":"Ζ","zeta":"ζ","Eta":"Η","eta":"η","Theta":"Θ","theta":"θ","Iota":"Ι","iota":"ι","Kappa":"Κ","kappa":"κ","Lambda":"Λ","lambda":"λ","Mu":"Μ","mu":"μ","Nu":"Ν","nu":"ν","Xi":"Ξ","xi":"ξ","Omicron":"Ο","omicron":"ο","Pi":"Π","pi":"π","Rho":"Ρ","rho":"ρ","Sigma":"Σ","sigma":"σ,ς *","Tau":"Τ","tau":"τ","Upsilon":"Υ","upsilon":"υ","Phi":"Φ","phi":"φ","Chi":"Χ","chi":"χ","Psi":"Ψ","psi":"ψ","Omega":"Ω","omega":"ω"};
    var searchFastInput = function (keyword) {
        var result = [];
        for(key in fastInput) {
            if (key.match(new RegExp(keyword))) {
                result.push({
                    label: fastInput[key] + ", system",
                    content: fastInput[key]
                })
            }
        }
        return result;
    }

    var ucfirst = function (str) {
        return str.charAt(0).toUpperCase() + str.substr(1);
    }

    var searchGene = function (keyword, callback) {
        $.ajax({
            url: "gene?keyword=" + encodeURIComponent(keyword) + "&mode=title",
            dataType: "json",
            success: function (data) {
                var len = data.length;
                for (let i = 0; i < len; i++) {
                    const gene = data[i];
                    gene.label = gene.title + "</i>";
                    gene.content = $("<a></a>").html(gene.title).attr("href", "gene?id=" + gene.id).css("font-style", "italic")[0].outerHTML;
                    var protein = JSON.parse(JSON.stringify(gene));
                    protein.label = ucfirst(protein.title);
                    protein.content = $("<a></a>").html(protein.title).attr("href", "gene?id=" + protein.id)[0].outerHTML;
                    data.push(protein);
                }
                data.sort(function(a,b){
                    return a.id.localeCompare(b.id);
                });
                callback(data);
            },
            error: function () {
                callback([]);
            }
        })
    }

    var searchCategory = function (keyword, callback) {
        $.ajax({
            url: "category?keyword=" + encodeURIComponent(keyword),
            dataType: "json",
            success: function (data) {
                var len = data.length;
                for (let i = 0; i < len; i++) {
                    const category = data[i];
                    category.label = category.title + "</i>";
                    category.content = $("<a></a>").html(category.title).attr("href", "category?id=" + category.id)[0].outerHTML;
                }
                data.sort(function(a,b){
                    return a.id.localeCompare(b.id);
                });
                callback(data);
            },
            error: function () {
                callback([]);
            }
        })
    }

    editor.summernote('autoComplete.setDataSrc', function(keyword, callback) {
        var results = [];
        results = results.concat(searchFastInput(keyword));
        searchGene(keyword, function(geneResult) {
            results = results.concat(geneResult);
            searchCategory(keyword, function (categoryResult){
                results = results.concat(categoryResult)
                callback(results);
            })
        })
    }, "#");
    
});

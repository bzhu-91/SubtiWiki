<div class="m_key">Domains</div>
<div class="box" id="protein-domain"></div>
<script type="text/javascript">
$(document).ready(function(){
    var domains = {{jsvar:the protein->domains}};
    var proteinLength = {{jsvar:proteinLength}};
    if (domains) {
        var container = $("<div></div>");
        container.css({
            background: "#eee",
            position: "relative"
        });

        domains.forEach(function(each){
            var block = $("<div></div>");
            var title = "";
            for(var key in each) {
                if (key != "id" && key != "protein" ) title += key.replace("_", " ") + ": " + each[key] + "\n";
            }
            block.html(each.hmm_name);
            block.css({
                position: "relative",
                left: each.hmm_start / proteinLength * 100 + "%",
                width: each.hmm_length / proteinLength * 100 + "%",
                cursor: "pointer",
                background: "salmon",
                marginBottom: "2px",
                padding: "2.5px 5px",
                color: "white",
                fontSize: "smaller",
                textAlign: "center",
            });
            block.prop("title", title);
            block.onclick = function(){
                window.open("http://pfam.xfam.org/family/" + each.hmm_acc);
            };
            container.append(block);
        });

        $("#protein-domain").append(container);
    }
});
</script>
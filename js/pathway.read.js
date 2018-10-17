var Select = Select || function (id, data, withNull) {
	this.view = document.getElementById(id);
    this.data = data;
    this.withNull = withNull || true;
	this.populate();
}

Select.prototype.populate = function () {
	var self = this;
	if (this.withNull) self.view.innerHTML = "<option value='-1'>Please select</option>";

    var arr = [];
	for (var i in self.data) {
        arr.push(self.data[i]);
    }
    arr = arr.sort(function(a,b){
        return a.title.localeCompare(b.title);
    });

    for(var i = 0; i < arr.length; i++){
		var each = arr[i];
		var option = document.createElement("option");
		option.value = each.id;
		option.innerHTML = each.title;
		self.view.appendChild(option);
	}
}

var ColorSpectrum = ColorSpectrum || function (title, min, max) {
    this.title = title;
    this.min = min;
    this.max = max;
}

ColorSpectrum.prototype.toHexColor = function (color) {
    var r,g,b;
    r = color[0].toString(16);
    g = color[1].toString(16);
    b = color[2].toString(16);

    if (r.length == 1) r = "0" + r;
    if (g.length == 1) g = "0" + g;
    if (b.length == 1) b = "0" + b;
    return "#" + r + g + b;

}
ColorSpectrum.prototype.getColor = function (value) {
    // green, yellow, red
    var start, end, alpha;
    var middle = (this.max + this.min) / 2;
    if (value < middle) {
        start = [0,255,0]; end = [255,255,0];
        alpha = (value - this.min) / (this.max - this.min) * 2;
    } else {
        start = [255,255,0]; end = [255,0,0];
        alpha = (value - middle) / (this.max - this.min) * 2;
    }
    var color = [
        Math.round(alpha * end[0] + (1 - alpha) * start[0]),
        Math.round(alpha * end[1] + (1 - alpha) * start[1]),
        Math.round(alpha * end[2] + (1 - alpha) * start[2]),
    ];
    return this.toHexColor(color);
}

ColorSpectrum.prototype.createLegend = function (size) {
    size = size || 300;
    var container = $("<div></div>");
    container.css({width: size + 20 + "px"});
    var title = $("<p></p>");
    title.html(this.title);
    title.css({"text-align": "center", margin:0, padding:0})
    var clearBar = $("<div></div>");
    clearBar.css({clear: "both"});
    var colorContainer = $("<div></div>");
    colorContainer.css({margin: "0 10px 0 10px", height: "30px"});
    var labelMin = $("<div></div>");
    labelMin.html(this.min);
    labelMin.css({display:"inline-block"});
    var labelMax = $("<div></div>");
    labelMax.html(this.max);
    labelMax.css({float: "right"});

    container.append(labelMin, labelMax, clearBar, colorContainer, title);
    for (var i = 0; i < size; i++) {
        var block = $("<div></div>");
        var color = this.getColor(i * (this.max - this.min) / size + this.min);
        block.css({height: "30px", width:"1px", background: color, display: "inline-block"});
        colorContainer.append(block);
    }

    return container;
}


$(document).ready(function(){
    PathwayBrowser.load();
});

$(document).on("click", "#closePanel", function(){
    $("#full").hide();
    $("#collapsed").show();
});

$(document).on("click", "#collapsed", function(){
    $("#full").show();
    $("#collapsed").hide();
});

$(document).on("change", "#all-proteins", function(){
    if (this.value != -1) {
        PathwayBrowser.clearOmicsData();
        $("#transcriptomics, #proteomics, #all-metabolites").val(-1);
        PathwayBrowser.highlightProtein(this.value);
    } else {
        PathwayBrowser.clearHighlight();
    }
});

$(document).on("change", "#all-metabolites", function(){
    if (this.value != -1) {
        PathwayBrowser.clearOmicsData();
        PathwayBrowser.highlightMebolite(this.value);
        $("#transcriptomics, #proteomics, #all-proteins").val(-1);
    } else {
        PathwayBrowser.clearHighlight();
    }
});

$(document).on("change", "#all-pathways", function(){
    window.location = "pathway?id"+this.value;
});

$(document).on("change", "#transcriptomics", function(){
    var self = this;
    PathwayBrowser.clearOmicsData();
    if (this.value != -1) {
        PathwayBrowser.clearHighlight();
        $("#all-proteins, #all-metabolites").val(-1);
        PathwayBrowser.loadOmicsData("T" + this.value, function (data){
            if (!PathwayBrowser.showOmicsData("T", data)) {
                SomeLightBox.error("No data available");
                $(self).val(-1);
                
            }
        });
    }
});

$(document).on("change", "#proteomics", function(){
    var self = this;
    PathwayBrowser.clearOmicsData();
    if (this.value != -1) {
        PathwayBrowser.clearHighlight();
        $("#all-proteins, #all-metabolites").val(-1);
        PathwayBrowser.loadOmicsData("P" + this.value, function (data){
            if (!PathwayBrowser.showOmicsData("P", data)) {
                SomeLightBox.error("No data available");
                $(self).val(-1);
         
            }
        });
    }
});

$(document).on("click", "#clear-omic-data", function(){
    PathwayBrowser.clearOmicsData();
    $(".omics").val(-1);
    if (PathwayBrowser.state == "none") {
        PathwayBrowser.restoreAll();
    }
});

$(document).on("click", "#clear-highlight", function(ev){
    PathwayBrowser.clearHighlight();
    $(".highlight").val(-1);
    if (PathwayBrowser.state == "none") {
        PathwayBrowser.restoreAll();
    }
});

$(document).on("click", ".protein", function(evt){
    var id = this.getAttribute("id");
    PathwayBrowser.loadGene(id, function(data){
        $("#info-box").dialog({
            width: 500,
            height: "auto",
            title: "Gene"
        });
        $("#info-box").html(data);
    })
});

$(document).on("click", ".metabolite", function(){
    var id = this.getAttribute("id");
    PathwayBrowser.loadMetabolite(id, function (data){
        $("#info-box").html("<a href='https://pubchem.ncbi.nlm.nih.gov/compound/"+data.pubchem+"'><img src='https://pubchem.ncbi.nlm.nih.gov/image/imagefly.cgi?cid="+data.pubchem+"&width=250&height=250'/></a>");
        $("#info-box").dialog({
            width: "auto", height: "auto",title: data.title
        });
       
    })
});

// load all conditions
var PathwayBrowser = PathwayBrowser || {
    proteins: {},
    metabolites: {},
    allProteins: {},
    allMetabolites: {},
    proteinsView:null,
    metabolitesView:null,
    transcriptomicView: null,
    proteomicView:null,
    label:null,
    state: "none"
}

PathwayBrowser.load = function () {
    var self = this;
    this.loadConditions();
    this.loadData(function(){
        self.configCanvas();
        self.loadCanvas();
        self.loadPathways();
    });
}

PathwayBrowser.loadConditions = function (callback) {
    // load the omics data conditions
    ajax.get({
		url: "expression/condition",
		headers: {Accept: "application/json"}
	}).done(function(state, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if (state == 200) {
			self.conditions = data;
			new Select("transcriptomics", data.transcriptomic);
            new Select("proteomics", data.proteomic);
            if(callback) callback();
		}
	});
}

PathwayBrowser.loadData = function (callback) {
    var self = this;
    ajax.get({
        url: "gene?query=title",
        headers: {Accept: "application/json"}
    }).done(function(status, data, error, xhr){
        if (error) {
            SomeLightBox.error("Connection to server lost");
        } else if(status == 200) {
            data.shift(); // remove header
            data.forEach(function(row){
                self.allProteins[row[0]] = {
                    id: row[0],
                    locus: row[1],
                    title: row[2]
                }
            });
        }

        ajax.get({
            url: "metabolite?query=title",
            headers: {Accept: "application/json"}
        }).done(function(status, data, error, xhr){
            if (error) {
                SomeLightBox.error("Connection to server lost");
            } else if (status == 200) {
                data.shift();
                data.forEach(function(row){
                    self.allMetabolites[row[0]] = {
                        id: row[0],
                        title: row[1]
                    }
                });
            }

            if (callback) callback();
        })
    });
}

PathwayBrowser.loadCanvas = function () {
    var self = this;
    // get all enzymes and metabolites
    $(".protein").each(function(idx, element){
        var id = element.id;
        self.proteins[id] = self.allProteins[id]
    });

    $(".metabolite").each(function(idx, element){
        var id = element.id;
        self.metabolites[id] = self.allMetabolites[id];
    });

    // create select
    this.proteinsView = new Select("all-proteins", self.proteins);
    this.metabolitesView = new Select("all-metabolites", self.metabolites);
}

PathwayBrowser.loadPathways = function () {
    ajax.get({
        url: "pathway",
        headers: {Accept: "application/json"}
    }).done(function(status, data, error, xhr){
        if (error) {
            SomeLightBox.error("Connection to server lost");
        } else if (status == 200){
            new Select("all-pathways", data);
            $("#all-pathways").val(pathwayId);
        }
    });
}

PathwayBrowser.configCanvas = function () {
    var self = this;
    var els2move = [];

    this.canvas = new Pathway.Canvas($("svg")[0]);
    this.canvas.attr({
        style: null
    });
    this.canvas.on("mousedown", function(evt){
        if (evt.button == 0) {
            els2move = [self.canvas];
            initX = currentX = evt.clientX;
            initY = currentY = evt.clientY;
        }
    });

    this.canvas.on("mousemove", function(evt){
        if (els2move.length) {
            var dx = (evt.clientX - currentX);
            var dy = (evt.clientY - currentY);
            els2move.forEach(function(el){
                el.move(dx, dy);
            });
            currentX = evt.clientX;
            currentY = evt.clientY;
        }
    });

    // end of drag/click
    this.canvas.on("mouseup", function(evt){
        if (evt.button == 0) {
            els2move = [];
            var dx = evt.clientX - initX;
            var dy = evt.clientY - initY;
            var d = Math.sqrt(dx * dx + dy * dy);
            if (d < 5) {
                // click event
            }
        }
    });

    this.canvas.on("mouseout", function(evt){
        els2move = [];
    });

    var scrollSensitivity = 0.02
    var onmousewheel = function(evt) {
        var evt = window.event || evt;
        evt.preventDefault();
        var scroll = evt.detail ? evt.detail * scrollSensitivity : (evt.wheelDelta / 120) * scrollSensitivity;
        scale = self.canvas.getScale() + scroll;
        if (scale > 0.05) {
        
            var s = self.canvas.getScale(), e = self.canvas.x, f = self.canvas.y;
            var fixedPoint = {
                x: (evt.clientX - e)/s,
                y: (evt.clientY - f)/s
            }
            var s1 = scale, e1 = (fixedPoint.x*s+e)-fixedPoint.x*s1, f1 = (fixedPoint.y*s+f)-fixedPoint.y*s1
            self.canvas.setScale(scale);
            self.canvas.position(e1, f1);
        }
        return true;
    }
    this.canvas.on("DOMMouseScroll", onmousewheel);
    this.canvas.on("mousewheel", onmousewheel);
}

PathwayBrowser.fadeAll = function () {
    $("rect, text, path, ellipse").each(function(idx, element){
        Util.addClassNS(element, "faded");
    });
}

PathwayBrowser.restoreAll = function () {
    $(".faded").each(function(idx, element){
        Util.removeClassNS(element, "faded");
    });
}

PathwayBrowser.highlightProtein = function (proteinId) {
    if (this.state == "omics") {
        this.clearOmicsData();
        $(".omics").val(-1);
    } else if (this.state == "highlight") {
        $("#all-proteins").val(-1);
    } else {
        this.fadeAll();
    }
    this.state = "highlight";
    $(".protein#" + proteinId + "> *").each(function(idx, element){
        Util.removeClassNS(element, "faded");
        Util.addClassNS(element, "highlighted");
    });
}

PathwayBrowser.highlightMebolite = function (metaboliteId) {
    if (this.state == "omics") {
        this.clearOmicsData();
        $(".omics").val(-1);
    } else if (this.state == "highlight") {
        $("#all-metabolites").val(-1);
    } else {
        this.fadeAll();
    }
    this.state = "highlight";
    $(".metabolite#" + metaboliteId + "> *").each(function(idx, element){
        Util.removeClassNS(element, "faded");
        Util.addClassNS(element, "highlighted");
    });
}

PathwayBrowser.clearHighlight = function () {
    $(".highlighted").each(function(idx, element){
        Util.removeClassNS(element, "highlighted");
    });
    this.state = "none";
}

PathwayBrowser.loadGene = function (geneId, callback) {
    ajax.get({
        url: "gene/summary?id="+geneId,
    }).done(function(status, data, error, xhr){
        if (status == 200) {
            if (callback) callback(data);
        } else {
            SomeLightBox.error(data.message);
        }
    })
}

PathwayBrowser.loadMetabolite = function (metaboliteId, callback) {
    ajax.get({
        url: "metabolite?id=" + metaboliteId,
        headers: {Accept: "application/json"}
    }).done(function(status, data, error, xhr){
        if (error) {
            SomeLightBox.error("Connection to server lost");
        } else if (status == 200) {
            if (callback) callback(data);
        } else SomeLightBox.error(data.message);
    })
}

PathwayBrowser.loadOmicsData = function (dataset, callback) {
    ajax.get({
        url: "expression?condition=" + dataset,
        headers: {Accept: "application/json"}
    }).done(function(status, data, error, xhr){
        if (error) {
            SomeLightBox.error("Connection to server lost");
        } else if (status = 200) {
            if (callback) callback(data);
        } else {
            SomeLightBox.error(data.message);
        }
    });
}

PathwayBrowser.showOmicsData = function (type, data) {
    var dataset = [];
    var cssData = {};
    var max = -Infinity, min = Infinity;
    for (var id in data) {
        if (id in this.proteins && data[id]) {
            dataset.push({
                id: id,
                value: data[id]
            });
        }
        if (data[id] > max) max = data[id];
        if (data[id] < min) min = data[id];
    }
    if (dataset.length) {
        if (this.state == "omics") {
            this.clearOmicsData();
        } else if (this.state == "highlight") {
            this.clearHighlight();
        } else {
            this.fadeAll();
        }
        this.state = "omics";
        var spectrum = new ColorSpectrum(type == "T" ? "Expression level" : "??", min, max);
        dataset.forEach(function(each){
            var color = spectrum.getColor(each.value);
            cssData[".protein[id='" + each.id + "'] rect._protein_rect"] = {
                fill: color
            }
        });
        this.createCSSBlock("transcriptomics", cssData);
        var legend = spectrum.createLegend();
        legend.css({
            position: "absolute", left: "20px", bottom: "20px"
        });
        $("#middle").append(legend);
        this.legend = legend;
        return true;
    } else {
        return false;
    }
}

PathwayBrowser.clearOmicsData = function () {
    if (this.state == "omics") {
        this.removeCSSBlock("transcriptomics");
        this.removeCSSBlock("proteomics");
        this.legend.remove();
        this.state = "none";
    } 
}

PathwayBrowser.createCSSBlock = function (blockName, cssData) {
    var block = $("<style ></style>");
    var cssStrings = [];
    for(var selector in cssData) {
        var css = [];
        for(var key in cssData[selector]) {
            css.push(key + ":" + cssData[selector][key]);
        }
        cssStrings.push(selector + " {" + css.join("; ") + "}");
    }
    block.html(cssStrings.join("\n"));
    block.prop("id", blockName);
    $(document.head).append(block);
}

PathwayBrowser.removeCSSBlock = function (blockName) {
    $("style#" + blockName).remove();
}



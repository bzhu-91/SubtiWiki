function ucfirst (str) {
	str += ''
	var f = str.charAt(0)
		.toUpperCase()
	return f + str.substr(1)
}

var Select = Select || function (container, data, withNull) {
    if (typeof container === "string" || container instanceof String) {
        this.view = document.getElementById(container);
    } else if (container.tagName && container.tagName === "SELECT") {
        this.view = container;
    } else {
        throw new Error ("container is not a valid dom element or id string");
    }
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
        if (each) {
    		var option = document.createElement("option");
            option.value = each.id;
    		option.innerHTML = each.title;
    		self.view.appendChild(option);
        }
	}
}

var GroupSelect = GroupSelect || function (container, data) {
	if (typeof container === "string" || container instanceof String) {
        this.view = document.getElementById(container);
    } else if (container.tagName && container.tagName === "SELECT") {
        this.view = container;
    } else {
        throw new Error ("container is not a valid dom element or id string");
    }
	this.data = data;
	this.populate();
}

GroupSelect.prototype.populate = function () {
	var self = this;
	self.view.innerHTML = "<option value='-1'>Please select</option>";
	
	for (var group in self.data) {
		var optgroup = document.createElement("optgroup");
		optgroup.label = group;
		self.view.appendChild(optgroup);
		for (var id in self.data[group]) {
            var option = document.createElement("option");
			option.value = self.data[group][id].id;
			option.innerHTML = self.data[group][id].title;
			self.view.appendChild(option);
		}
	}
}

$(document).ready(function(){
    $.getScript($("base").attr("href") + "js/libs/colorSpectrum.js", function(){
        PathwayBrowser.load();
    });
});

$(document).on("submit", "#search", function(ev){
	ev.stopPropagation();
	ev.preventDefault();

	var geneName = this.geneName.value.trim();

	if (geneName.length >= 2) {
		ajax.get({
			url:"gene?keyword="+geneName+"&mode=title",
			headers: {Accept: "application/json"}
		}).done(function(state, data, error, xhr){
			if (error) {
				SomeLightBox.error("Connection to server lost");
			} else if (state == 200) {
				if (data.length > 1) {
					SomeLightBox.error("Gene name " + geneName + " is ambigious");
				} else {
					pathwaySearch(data[0].id);
				}
			} else {
				SomeLightBox.error("Gene " + geneName + " not found");
			}
		})
	}
	return false;
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
    window.location = "pathway?id="+this.value;
});

$(document).on("change", ".omics", function(){
    var self = this;
    PathwayBrowser.clearOmicsData();
    var conditionId = this.value;
    // reset other omics select tags
    $("select.omics").val(-1);
    self.value = conditionId;
    if (conditionId != -1) {
        PathwayBrowser.clearHighlight();
        $("#all-proteins, #all-metabolites").val(-1);
        PathwayBrowser.loadOmicsData(conditionId, function (data){
            if (!PathwayBrowser.showOmicsData(conditionId, data)) {
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

$(document).on("click", "#full-screen", function() {
    if (this.innerHTML == "Full screen") {
        this.innerHTML = "Exit full screen";
    } else {
        this.innerHTML = "Full screen";
    }
    $("#upper, #under").toggle();
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
    self.configCanvas();
    this.loadConditions();
    this.loadPathways();
    this.loadCanvas();
}

PathwayBrowser.loadConditions = function (callback) {
    var self = this;
    // load the omics data conditions
    if (window.conditions) {
        self.conditions = {};
        for(var i in conditions){
            self.conditions[conditions[i].id] = conditions[i];
        }
        
        var forSelection = {};
        for (var id in conditions) {
            var type = conditions[id].type
            if (!(type in forSelection)) {
                forSelection[type] = {};
            }
            forSelection[type][id] = conditions[id];
        }
        // filters
        if (window.datasetDisplayMode == "seperate") {
            for(var type in forSelection) {
                var label = $("<label></label>").html(ucfirst(type));
                var select = $("<select></select>").addClass("omics");
                new Select(select[0], forSelection[type]);
                $("#omics-data-select-container").append(label, select);
            }
        } else {
            var label = $("<label>Omics data</label>");
            var select = $("<select></select>").addClass("omics");
            new GroupSelect(select[0], forSelection);
            $("#omics-data-select-container").append(label, select);
        }
        if (callback) callback.apply(this);
    }
}

PathwayBrowser.loadCanvas = function () {
    var self = this;
    // get all enzymes and metabolites
    $(".protein").each(function(idx, element){
        var id = element.id;
        self.proteins[id] = {
            id: id,
            title: element.getAttribute("title")
        }
    });

    $(".metabolite").each(function(idx, element){
        var id = element.id;
        self.metabolites[id] = {
            id: id,
            title: element.getAttribute("title")
        };
    });

    // create select
    this.proteinsView = new Select("all-proteins", self.proteins);
    this.metabolitesView = new Select("all-metabolites", self.metabolites);
}

PathwayBrowser.loadPathways = function () {
    if (window.pathways) {
        new Select("all-pathways", window.pathways);
        $("#all-pathways").val(pathwayId);
    } else ajax.get({
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
    this.canvas.setScale(0.5);
    this.canvas.position(0,0,"top left");
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

PathwayBrowser.loadOmicsData = function (conditionId, callback) {
    ajax.get({
        url: "expression?condition=" + conditionId,
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

PathwayBrowser.showOmicsData = function (conditionId, data) {
    var self = this;
    var dataset = [];
    var cssData = {};
    var con = self.conditions[conditionId];
    for (var id in data) {
        if (id in this.proteins && data[id]) {
            dataset.push({
                id: id,
                value: data[id]
            });
        }
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
        var spectrum = new ColorSpectrum(con.title, con.min, con.max, con.type == "protein level (copies per cell)" ? "log": "");
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



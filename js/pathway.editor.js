// pathway TODOs:
// 1. shrink/enlarge funciton of the membrane
// 2. lock the reaction to the membrane

if (Object.assign) {
    //shallow copy
    Object.assign = function (a,b) {
        var merged = {};
        for (var key in a) {
            merged[key] = a[key];
        }
        for (var key in b) {
            merged[key] = b[key];
        }
        return merged;
    }
}

var findAll = function (obj,key, keypath, count) {
    keypath = keypath || [];
    count = count || 0;
    count++;
    var result = [];
    if (count > 1000) return result;
    for(var k in obj) {
        if (obj.hasOwnProperty(k)) {
            keypath.push(k);
            if (k == key) {
                result.push(keypath.slice(0));
            }
            if (obj[k] && (typeof obj[k] == "object" || obj[k] instanceof Object)) {
                result = result.concat(findAll(obj[k], key, keypath, count));
            }
            keypath.pop();
        }
    }
    return result;
}

var setProperty = function (obj, keypath, val) {
    var last = keypath.pop();
    var o = obj;
    for(var i = 0; i < keypath.length; i++){
        var k = keypath[i];
        if (k in o) {
            o = o[k];
        } else return false;
    }
    o[last] = val;
}

var getProperty = function (obj, keypath) {
    var o = obj;
    for(var i = 0; i < keypath.length; i++){
        var k = keypath[i];
        if (k in o) {
            o = o[k];
        } else return false;
    }
    return o;
}

$(document).ready(function(){
    $("#top-menu-bar").tooltip();
    if (window.pathwayId) {
        PathwayModel.loadIndex(function(){
            PathwaySearchViewController.init();
            PathwayEditor.init();
        })    
    } else {
        PathwayEditor.loadPathways();
    }
});

var PathwayModel = PathwayModel || {
    reactionIndex: [],
    reactionData: {}
};

PathwayModel.loadIndex = function (callback) {
    var self = this;
    $.ajax({
        url: "reaction?page=1&page_size=max",
        dataType:"json",
        success: function (data) {
            self.reactionIndex = data;
            if (callback) callback();
        },
        error: function () {
            SomeLightBox.error("connection to server lost");
        }
    });
}

PathwayModel.prepareReactionData = function (data) {
    data = JSON.parse(JSON.stringify(data).replace(/title/gi, "label").replace(/modification/gi, "extra"))
    // process data
    var reactants = [];
    data.reactants.forEach(function(r){
        var keys = ["coefficient", "extra"];
        keys.forEach(function(k){
            r.metabolite[k] = r[k];
        })
        reactants.push(r.metabolite);
    });
    data.reactants = reactants;

    var products = [];
    data.products.forEach(function(r){
        var keys = ["coefficient", "extra"];
        keys.forEach(function(k){
            r.metabolite[k] = r[k];
        })
        products.push(r.metabolite);
    });
    data.products = products;

    var catalysts = [];
    if (data.catalysts) data.catalysts.forEach(function(r){
        var keys = ["novel", "extra"];
        keys.forEach(function(k){
            r.catalyst[k] = r[k];
        })
        catalysts.push(r.catalyst);
    });
    data.catalysts = catalysts;

    // handle complex members
    var keypaths = findAll(data, "members");
    keypaths.sort(function(a,b){
        return b.length - a.length;
    });
    keypaths.forEach(function(keypath){
        var membersOld = getProperty(data, keypath);
        if (membersOld) {
            var members = [];
            membersOld.forEach(function(r){
                var keys = ["coefficient", "extra"];
                keys.forEach(function(k){
                    r.member[k] = r[k];
                })
                members.push(r.member);
            });
            setProperty(data, keypath, members);
        }
    });
    return data;
}

PathwayModel.loadReaction = function (reactionId, callback, remote) {
    var self = this;
    if ((reactionId in self.reactionData) && !remote) {
        callback(self.reactionData[reactionId]);
    } else {
        $.ajax({
            url: "reaction?id="+reactionId,
            dataType:"json",
            success: function (data) {
                data = self.prepareReactionData(data);
                self.reactionData[data.id] = data; // cache the result
                callback(data);
            },
            error: function () {
                SomeLightBox.error("Connection to server lost");
            }
        });
    }
}

PathwayModel.searchReactionByMetabolite = function (keyword) {
    var results = [];
    keyword = keyword.toLowerCase();
    var keywords = [];
    if (keyword.indexOf(" ") != -1) {
        keywords = keyword.split(" ");
    } else {
        keywords.push(keyword);
    }
    this.reactionIndex.forEach(function(reaction){
        var result = {
            reaction: reaction,
            relevance: 0
        }
        if (reaction.equation) {
            keywords.forEach(function(keyword){
                if (reaction.equation.toLowerCase().indexOf(keyword) != -1) {
                    result.relevance++;
                }
            });
            results.push(result);
        }
    });
    results = results.filter(function(result) {
        return result.relevance > 0;
    });
    results = results.sort(function(a,b){
        if (a.relevance > b.relevance) return -1;
        else if (a.relevance < b.relevance) return 1;
        else if (a.id > b.id) return 1;
        else if (a.id < b.id) return -1;
        else return 0;
    });
    var reactions = [];
    results.forEach(function(result){
        reactions.push(result.reaction);
    });
    return reactions;
}

PathwayModel.searchReactionByCatalyst = function (keyword, callback) {
    $.ajax({
        url: "reaction?catalyst=" + encodeURIComponent(keyword)  + "&page=1&page_size=max",
        dataType: "json",
        success: function (data) {
            callback(data)
        },
        error: function () {
            callback([]);
        }
    })
}

var PathwaySearchViewController = PathwaySearchViewController || {
    view: $("#panel-add-reaction"),
    searchForm: $("form#form-search-reaction"),
    resultsContainer: $("#container-reactions")
}

PathwaySearchViewController.init = function () {
    var self = this;
    this.view.dialog({
        minWidth: 500,
        maxWidth: 800,
        maxHeight: 600,
        hide: {
            effect: "blind",
            duration: 50,
        },
        show: {
            effect: "blind",
            duration: 50
        }
    });
    this.resultsContainer.html("");
    this.searchForm.on("submit", function(evt){
        evt.preventDefault();
        var keyword = this.reaction.value.trim();
        self.searchForReaction(keyword);
    });
}

PathwaySearchViewController.show = function () {
    this.view.dialog("open");
}

PathwaySearchViewController.hide = function () {
    this.view.dialog("close");
}

PathwaySearchViewController.searchForReaction = function (keyword) {
    var self = this;
    if (keyword.length >= 2) {
        self.resultsContainer.html("");
        var resultByMetabolites = PathwayModel.searchReactionByMetabolite(keyword);
        PathwayModel.searchReactionByCatalyst(keyword, function(resultByCatalyst){
            var results = resultByMetabolites.concat(resultByCatalyst);
            if (results.length) {
                self.createResultList(results);
            } else {
                self.resultsContainer.html("No results found");
            }
        });
    } else SomeLightBox.error("Keyword too short");
}

PathwaySearchViewController.createResultList = function (results) {
    
    results.forEach(function(reaction){
        var li = $("<li reaction='"+reaction.id+"'>R"+ reaction.id + ": " + reaction.equation+"</li>");
        li.css({
            cursor: "pointer"
        });
        li.on("click", function(){
            var reactionId = $(this).attr("reaction");
            PathwayEditor.drawReaction(reactionId);
            PathwaySearchViewController.hide();
        });
        $("#container-reactions").append(li);
    });
}

var PathwayEditor = PathwayEditor || {
    selectedViews: [],
    autoselectViews: [],
    reactionViews: {},
    renameDialog: new SomeLightBox({
            width: "400px",
            height: "auto"
        }).loadById("form-rename"),
    newDialog: new SomeLightBox({
            width: "400px",
            height: "auto"
        }).loadById("form-new"),
    helpDialog: new SomeLightBox({
        width: "70%",
        height: "auto"
    }).loadById("help-info"),
    elementsOnMembrane: {
        right: [],
        bottom: []
    },
    editorDialog: new SomeLightBox({
        width: "80%",
        height: "80%"
    }).loadById("reaction-editor").ondismiss(function(ev){
        // requires the update function of Pathway.Reaction class
        PathwayModel.loadIndex(); // equation change
        PathwayEditor.updateReaction(this.reaction.id);
    }),
    elEditorDialog: new SomeLightBox({
        width: "80%",
        height: "80%"
    }).loadById("el-editor").ondismiss(function(ev){
        PathwayModel.loadIndex(); // equation change
        return true;
    }),
    creatorDialog: new SomeLightBox({
        width: "80%",
        height: "80%",
    }).loadById("reaction-creator").ondismiss(function(ev){
        // TODO: reload the reaction index
        // should call methods in PathwayModel
        PathwayModel.loadIndex();
        return true;
    }),
    currentZ: 0
}

PathwayEditor.bubbleUp = function (element) {
    while (element.tagName != "svg" && !("wrapper" in element) || (element.getAttribute("class") && element.getAttribute("class").indexOf("nested") != -1)) {
        element = element.parentNode;
    }
    return element.wrapper;
}

PathwayEditor.init = function () {
    this.processView();
    this.createCanvas();
    this.loadPathways();
}

PathwayEditor.loadPathways = function () {
    $.ajax({
        url: "pathway",
        dataType:"json",
        success: function (data) {
            $("#select-pathway").html("<option>Please select</option>");
            data.forEach(function(pathway){
                $("#select-pathway").append($("<option></option>").html(pathway.title).val(pathway.id));
            });
            if (window.pathwayId) {
                $("#select-pathway").val(pathwayId);
            }
        },
        error: function () {
            SomeLightBox.error("Connection to server lost");
        }
    })
}

PathwayEditor.processView = function () {
    $(".menu").menu();

    $("#btn-add-reaction").on("click", function(){
        PathwaySearchViewController.show();
    });
    $("#form-new").prop("done", function(){
        PathwayEditor.loadPathways();
    });
    $("#form-rename").prop("done", function(){
        PathwayEditor.loadPathways();
    });
    
}

PathwayEditor.drawReaction = function (reactionId) {
    var self = this;
    PathwayModel.loadReaction(reactionId, function(data){
        data.width = 100; data.height = 200;
        var reaction = new Pathway.Reaction(data);
        reaction.appendTo(self.canvas);
        // add z index
        reaction.z = self.currentZ;
        self.currentZ++;
        reaction.position(0,0,"left top");
        // set the position of the reaction to the center
        var screenW = $(document.body).width();
        var screenH = $(document.body).height();
        var bbox = self.canvas.getBBox();
        var x = (screenW/2 - bbox.x)/self.canvas.getScale();
        var y = (screenH/2 - bbox.y)/self.canvas.getScale();
        reaction.position(x,y);
        // save the reference
        if (data.id in self.reactionViews) {
            self.reactionViews[data.id].push(reaction);
        } else self.reactionViews[data.id] = [reaction]; // multiple copies of the same reaction can exist
    });
}

PathwayEditor.updateReaction = function (reactionId) {
    var self = this;
    PathwayModel.loadReaction(reactionId, function(data){
        // find the reaction
        if (data.id in self.reactionViews) {
            var reactions = self.reactionViews[data.id];
            data.isDashed = data.novel;
            data.hasArrow = !data.reversible;
            reactions.forEach(function(each) {
                each.update(data);
            });
        } else {
            console.error("Reaction R" + data.id + " does not exist in this map");
        }
    }, true);
}

PathwayEditor.removeReaction = function (reaction) {
    if (reaction.id in this.reactionViews) {
        var idx = this.reactionViews[reaction.id].indexOf(reaction);
        if (idx > -1) this.reactionViews[reaction.id].splice(idx,1);
        this.freeReaction(reaction);
        reaction.remove();
    }
}

PathwayEditor.getReactionsById = function (id) {
    return this.reactionViews[id];
}

PathwayEditor.calcAutoSelection = function () {
    var self = this;
    self.autoselectViews.forEach(function(view){
        if (self.selectedViews.indexOf(view) == -1) view.setState("normal");
    });
    self.autoselectViews = [];

    // auto-select the shared elements from reactions
    var selectedReactions = [];
    var tops = [];
    self.selectedViews.forEach(function(view){
        if (view instanceof Pathway.Reaction) {
            view.getLockGroup().forEach(function(lockedView){
                tops.push(lockedView.top ? lockedView.top : lockedView);
            });
            selectedReactions.push(view);
        }
    });
    // validate each stack whether all reactions are selected
    // if so, add the top view to the autoselection
    tops.forEach(function(top){
        var validated = selectedReactions.indexOf(top.parent) != -1;
        top.getSyncGroup().forEach(function(syncView){
            if (selectedReactions.indexOf(syncView.parent) == -1) validated = false;  
        });
        if (validated) {
            if (self.autoselectViews.indexOf(top) == -1 && self.selectedViews.indexOf(top) == -1) {
                top.setState("selected");
                self.autoselectViews.push(top);
            }
        }
    });
}

PathwayEditor.addToSelection = function (view) {
    var self = this;
    if (self.selectedViews.indexOf(view) == -1) {
        self.selectedViews.push(view);
        view.setState("selected");
        self.calcAutoSelection();
    }
}

PathwayEditor.removeFromSelection = function (view) {
    var self = this;
    var idx = self.selectedViews.indexOf(view);
    if (idx != -1) {
        self.selectedViews.splice(idx, 1);
        view.setState("normal");
        self.calcAutoSelection();
    }
}

PathwayEditor.clearSelection = function () {
    var self = this;
    self.selectedViews.forEach(function(view){
        view.setState("normal");
    });
    self.selectedViews = [];
    self.calcAutoSelection();
}

PathwayEditor.createCanvas = function () {
    var self = this;
    // if there is no SVG tag, then create one
    // if there is a svg tag, need to adapt all the reactions etc.
    if ($("#editor svg").length) {
        this.loadCanvas();
    } else {
        this.canvas = new Pathway.Canvas();
        this.canvas.appendTo($("#editor")[0]);
        this.canvas.attr({
            style: "width:100%;height:100%;position:fixed;top:"+$("#top-menu-bar").height()+"px;left:0;bottom:0;right:0",
        });
        // this.canvas.setScale(0.3);

        this.configCanvas();

        // create the cell membrane
        this.membrane = Util.elNS("rect", {
			rx: 20,
			ry: 20,
			width: 1600 * 5, 
			height: 900 * 5, 
			x: 100,
			y: 100,
			"pointer-events":"visibleStroke",
			cursor: "pointer",
			style: "fill:rgba(0,0,0,0); stroke-width: 30; stroke: lightgray",
			id: "membrane"
        });
        
        this.canvas.append(this.membrane);

        
    }
}

// add all the event listeners to the canvas
// includes:
//      1. drag and move function
//      2. click to (multi-)select
PathwayEditor.configCanvas = function () {
    var self = this;
    var els2move = [], initX, initY, currentX, currentY;
    this.canvas.attr("tabindex",0)
    // beginning of the drag/click
    this.canvas.on("mousedown", function(evt){
        if (evt.button == 0) {
            if (self.selectedViews.length == 0) { // deselect all the elements
                els2move = [self.canvas];
            } else {
                els2move = self.selectedViews.concat(self.autoselectViews);
            }
            initX = currentX = evt.clientX;
            initY = currentY = evt.clientY;
        }
    });

    this.canvas.on("mousemove", function(evt){
        if (els2move.length) {
            var dx = (evt.clientX - currentX);
            var dy = (evt.clientY - currentY);
            // if not the canvas, scale need to be considered
            if (els2move.length && els2move[0] != self.canvas) {
                dx /= self.canvas.getScale();
                dy /= self.canvas.getScale();
            }
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
                // handle click evt
                // pop up until the draggable/clickable unit
                var view = self.bubbleUp(evt.target);
                if (view && !(view instanceof Pathway.Canvas)) {
                    if (view.getState() == "error" || view.getState() == "warning") {
                        // show error message instead of selection
                        var dialogBox = $("<div title='Error'></div>").html(view.errorMessage.replace(/\n/gi, "<br>"));
                        $(document.body).append(dialogBox);
                        dialogBox.dialog({
                            position: {
                                my: "left top",
                                of: evt
                            }
                        });
                        view.setState("normal");
                    } else {
                        // clicking on the other elements
                        var isMac = window.navigator && window.navigator.platform && window.navigator.platform == "MacIntel";
                        // if ctrl is pressed in windows/linus or command is pressed in Mac
                        if ((isMac && evt.metaKey) || (!isMac && evt.ctrlKey)) {
                            // toggle selection
                            var idx = self.selectedViews.indexOf(view);
                            if (idx == -1) {
                                self.addToSelection(view);
                            } else {
                                self.removeFromSelection(view);
                            }
                        } else {
                            // deselect and reselect
                            self.clearSelection();
                            self.addToSelection(view);
                        }
                    }
                } else {
                    // clear selection
                    self.clearSelection();
                }
            }
        }
    });

    this.canvas.on("click", function(){
        $(".menu, .popup").hide();
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

PathwayEditor.loadCanvas = function () {
    var self = this;
    this.membrane = $("rect#membrane")[0];
    this.canvas = new Pathway.Canvas($("#editor svg")[0]);
    self.currentZ = $(".reaction").length;
    // add all the reactions
    $(".reaction").each(function(idx, element){
        var reaction = new Pathway.Reaction(element);
        if (reaction.id in self.reactionViews) {
            self.reactionViews[reaction.id].push(reaction);
        } else {
            self.reactionViews[reaction.id] = [reaction];
        }
        reaction.z = idx;
        // restore lock group
        var uuids = element.getAttribute("lock");
        if (uuids) {
            var views = [];
            uuids.split(",").forEach(function(uuid){
                var el = document.querySelector("[uuid='"+uuid+"']");
                if (el) {
                    views.push(el.wrapper);
                } else {
                    console.error(uuid);
                }
            });
            reaction.setLockGroup(views);
        }
    });
    var ids = [];
    for(var id in self.reactionViews) {
      ids.push(id);
    }


    if (ids.length) $.ajax({
        url: "reaction?ids=" + ids.join(","),
        dataType:"json",
        success: function (data) {
            for(var id in data) {
                var each = PathwayModel.prepareReactionData(data[id]);
                self.reactionViews[id].forEach(function(each){each.update(each)});
            }
        }
    });

    $("[sync]").each(function(idx, element){
        var uuids = element.getAttribute("sync");
        if (uuids) {
            var views = [];
            var top = element.wrapper;
            uuids.split(",").forEach(function(uuid){
                var el = document.querySelector("[uuid='"+uuid+"']");
                if (el) {
                    var view = el.wrapper;
                    views.push(view);
                    view.top = top;
                }
            });
            top.setSyncGroup(views);
        }
    });

    this.configCanvas();
}

// use the graph algorithm
PathwayEditor.findAllConnectedComponents = function (reaction) {
    var pool = {};
    var stack = [reaction];
    var current;
    var connected = {};

    while (stack.length > 0) {
        current = stack.shift();
        connected = PathwayEditor.findConnectedComponents(current);
        for (var uuid in connected) {
            if (connected[uuid] instanceof Pathway.Reaction) {
                if (!(uuid in pool)) {
                    stack.push(connected[uuid]);
                }
            } else {
                pool[uuid] = connected[uuid];
            }
        }
        pool[current.uuid] = current;
    }

    return pool;
}

PathwayEditor.findConnectedComponents = function (reaction) {
    var tops = [];
    var els = {};
    reaction.getLockGroup().forEach(function(view){
        tops.push(view.top ? view.top: view);
    });
    tops.forEach(function(top){
        els[top.uuid] = top;
        top.getSyncGroup().forEach(function(sync){
            var r = sync.parent;
            els[r.uuid] = r;
        });
        var r = top.parent;
        els[r.uuid] = r;
    });
    return els;
}

// only find the elements on the right/bottom edge of the membrane
// includes no connected elements
// need to be tested
PathwayEditor.findReactionsOnMembrane = function () {
    var self = this;
    var elements = {
        top: {},
        left: {},
        right: {},
        bottom: {},
    };
    var membraneBBox = self.membrane.getBBox();
    membraneBBox.right = membraneBBox.x + membraneBBox.width;
    membraneBBox.bottom = membraneBBox.y + membraneBBox.height;
    for (var reactionId in self.reactionViews) {
        self.reactionViews[reactionId].forEach(function(reaction){
            if (Math.abs(reaction.x - membraneBBox.right) < 10) {
                elements.right[reaction.uuid] = reaction;
            }
            if (Math.abs(reaction.y - membraneBBox.bottom) < 10) {
                elements.bottom[reaction.uuid] = reaction;
            }
            if (Math.abs(reaction.x - membraneBBox.left) < 10) {
                elements.left[reaction.uuid] = reaction;
            }
            if (Math.abs(reaction.y - membraneBBox.top) < 10) {
                elements.top[reaction.uuid] = reaction;
            }
        });
    }
    return elements;
}

// find all the elements which covers the point (x,y)
// but the elements which is not visible are NOT included
PathwayEditor.elementsFromPoint = function (x,y) {
    var elements = [], previousPointerEvents = [], current, i, d;

    if(typeof document.elementsFromPoint === "function")
        return document.elementsFromPoint(x,y);
    if(typeof document.msElementsFromPoint === "function")
        return document.msElementsFromPoint(x,y);
    
    // get all elements via elementFromPoint, and remove them from hit-testing in order
    while ((current = document.elementFromPoint(x,y)) && elements.indexOf(current)===-1 && current != null) {
            
        // push the element and its current style
        elements.push(current);
        previousPointerEvents.push({
            value: current.style.getPropertyValue('pointer-events'),
            priority: current.style.getPropertyPriority('pointer-events')
        });
            
        // add "pointer-events: none", to get to the underlying element
        current.style.setProperty('pointer-events', 'none', 'important'); 
    }

    // restore the previous pointer-events values
    for(i = previousPointerEvents.length; d=previousPointerEvents[--i]; ) {
        elements[i].style.setProperty('pointer-events', d.value?d.value:'', d.priority); 
    }
        
    // return our results
    return elements;
}

// the stacked metabolites, ordered from top to bottom
PathwayEditor.lockViews = function (els) {
    var allSync = [];
    els.forEach(function(el){
        allSync.push(el);
        var group = el.getSyncGroup();
        if (group.length) {
            allSync = allSync.concat(el.getSyncGroup());
            el.clearSyncGroup();
        }
    });

    // remove duplications
    allSync = allSync.filter(function(v,i,a){
		return a.indexOf(v) === i;
    });

    // sort it according to the reaction z-index
    allSync = allSync.sort(function(a,b){
        return b.parent.z - a.parent.z;
    });

    var top = allSync.shift();
    
    delete top.top;
    top.setSyncGroup (allSync);
    top.parent.addToLockGroup(top);
    top.show();

    allSync.forEach(function(view){
        view.hide();
        view.top = top;
        view.parent.addToLockGroup(view);
    });
}
// function to unlock locked metabolites incase of deletion or re-layout
// will unlock all metabolites in the reaction
PathwayEditor.freeReaction = function (reaction) {
    var self = this;
    // find all the locked views in the reaction
    reaction.getLockGroup().forEach(function(view){
        var top = view.top ? view.top : view;
        var syncGroup = top.getSyncGroup();
        if (view == top) {
            if (syncGroup.length == 1) {
                self.unlockViews(top);
            } else {
                self.lockViews(syncGroup);
                view.clearSyncGroup();
            }
        } else {
            top.removeFromSyncGroup(view);
            top.removeFromSyncGroup(view);
            view.show();
        }
    });
    reaction.clearLockGroup();
}

PathwayEditor.unlockViews = function (top) {
    top.parent.removeFromLockGroup(top);
    top.getSyncGroup().forEach(function(sync){
        sync.parent.removeFromLockGroup(sync);
        delete sync.top;
        sync.show();
    });
    top.clearSyncGroup();
}

$(document).on("contextmenu", "svg", function(evt){
    evt.preventDefault();
});

$(document).on("contextmenu", ".reaction", function (evt) {
    evt.preventDefault();
    $("#menu-reaction").show();
    $("#menu-reaction").position({
        my: "left top",
        of: evt
    });
    // here this is the rootview of the reaction class
    $("#menu-reaction").prop("reaction", this.wrapper);
});

$(document).on("contextmenu", "rect#membrane", function(evt){
    evt.preventDefault();
    $("#menu-membrane").show().position({
        my: "left top",
        of: evt
    });
});

$(document).on("contextmenu", ".protein:not(.nested), .metabolite:not(.nested), .complex:not(.nested), .RNA:not(.nested), .DNA:not(.nested)", function(evt) {
    evt.preventDefault();
    evt.stopPropagation();
    // clear all the suggestions
    $("#menu-metabolite-suggestions").html("");
    var title = this.wrapper.label;
    var reactionId = this.wrapper.parent.id;
    if (title) {
        var results = PathwayModel.searchReactionByMetabolite(title);
        // need to exclude the calling reaction
        results.forEach(function(reaction){
            if (reaction.id != reactionId) {
                var li = $("<li></li>");
                li.html("R" + reaction.id + ": " + reaction.equation);
                li.on("click", function(){
                    PathwayEditor.drawReaction(reaction.id);
                    $("#menu-metabolite").hide();
                });
                $("#menu-metabolite-suggestions").append(li);
            }
        });
        var type = $(this).attr("class").replace(/nested/gi, "").trim();
        $("#menu-metabolite").find("#btn-edit-metabolite").attr("href", type + "/editor?id="  + this.wrapper.id);
        $("#menu-metabolite").show().position({
            my: "left top",
            of: evt
        });
        $("#menu-metabolite").prop("event", evt); // save the opening event
    }
    return true;
}); 

$(document).on("click", "#btn-edit-metabolite", function(){
    $("#menu-metabolite").hide();
    PathwayEditor.elEditorDialog.show();
    $("#el-editor-iframe").prop("src", $(this).attr("href"));
});

$(document).on("click", "#btn-reverse-sides", function(){
    var reaction = $("#menu-reaction").prop("reaction");
    reaction.layoutReversed = !reaction.layoutReversed;
    reaction.layout();
    $("#menu-reaction").hide();
});

$(document).on("click", "#btn-change-layout", function(){
    var reaction = $("#menu-reaction").prop("reaction");
    // toogle between vertical/horizontal
    reaction.layoutDirection = reaction.layoutDirection == "vertical" ? "horizontal" : "vertical";
    reaction.layout();
    $("#menu-reaction").hide();
});

$(document).on("click", "#btn-remove-reaction", function () {
    var reaction = $("#menu-reaction").prop("reaction");
    PathwayEditor.removeReaction(reaction);
    $("#menu-reaction").hide();
});

$(document).on("click", "#btn-lock-metabolites", function (evt) {
    $("#menu-metabolite").hide();
    var menuEvent = $("#menu-metabolite").prop("event");
    var x = menuEvent.clientX, y = menuEvent.clientY;
    // get all DOM elements from the point, from bottom to top
    var elements = PathwayEditor.elementsFromPoint(x,y);
    var views = [];
    for(var i = 0; i < elements.length; i++) {
        if (elements[i].tagName != "svg") {
            if (elements[i].id == "membrane") continue;
            var view = PathwayEditor.bubbleUp(elements[i]);
            views.push(view);
        } else break;
    }
    // validate type consistancy
    var type = views[0].attr("class");
    var id = views[0].id;
    for(var i = 0; i < views.length; i++) {
        if (type != views[i].attr("class") || id != views[i].id) {
            SomeLightBox.error("Elements are not of the same type or have different ids");
            return;
        }
    }
    PathwayEditor.lockViews(views);
});

$(document).on("click", "#btn-unlock-metabolites", function (ev) {
    $("#menu-metabolite").hide();
    var menuEvent = $("#menu-metabolite").prop("event");
    var x = menuEvent.clientX, y = menuEvent.clientY;
    // get all DOM elements from the point
    var elements = PathwayEditor.elementsFromPoint(x,y);
    var top;
    for(var i = 0; i < elements.length; i++) {
        if (elements[i].tagName != "svg") {
            if (elements[i].id == "membrane") continue;
            var view = PathwayEditor.bubbleUp(elements[i]);
            if (view.getSyncGroup().length > 0) {
                top = view;
                break;
            }
        } else break;
    }
    if (top) {
        PathwayEditor.unlockViews(top);
    }
});

$(document).on("click", "#btn-rename", function(evt){
    PathwayEditor.renameDialog.show();
});

$(document).on("click", "#btn-help", function(evt){
    PathwayEditor.helpDialog.show();
});

$(document).on("submit", "#form-rename", function(evt){
    PathwayEditor.renameDialog.dismiss();
});

$(document).on("change", "#select-pathway", function(evt){
    var self = this;
    if (window.pathwayId) {
        SomeLightBox.alert("Save the result", "Would you like to save your working progress before leaving this page?", {
            title: "Save",
            onclick: function(){
                $("#btn-save").click();
                window.location = "pathway/editor?id=" + self.value;
            }
        }, {
            title: "Don't save",
            color: "red",
            onclick: function (){
                window.location = "pathway/editor?id=" + self.value;
            }
        });
    } else {
        window.location = "pathway/editor?id=" + self.value;
    }
})

$(document).on("click", "#btn-save", function(evt){
    // get all the geneIds in this pathway map
    var ids = {};
    $("#editor .reaction").each(function(idx, reaction){
        ids[reaction.id] = true;
    });
    var reactions = [];
    for(var id in ids) {
        reactions.push(id);
    }
    PathwayEditor.clearSelection();
    var outerHTML = $("#editor svg")[0].outerHTML;
    $.ajax({
        type: "put",
        url: "pathway",
        dataType:"json",
        data: {
            map: outerHTML,
            id: pathwayId,
            reactions: reactions.join(",")
        },
        success: function (data) {
            SomeLightBox.alert("Save", "Pathway map has been successfully saved");
        },
        error: function (data) {
            SomeLightBox.error(data.message);
        }
    })
});

$(document).on("click", "#btn-new", function(){
    PathwayEditor.newDialog.show();
});

$(document).on("click", "#btn-membrane-width-plus", function (ev) {
    var bbox = PathwayEditor.membrane.getBBox();
    if (bbox) {
        var reactions = PathwayEditor.findReactionsOnMembrane();
        var block = reactions.right;
        for (var uuid in reactions.right) {
            block = Object.assign(block, PathwayEditor.findConnectedComponents(reactions.right[uuid]));
        }
        for (var uuid in reactions.top) {
            if (uuid in block) delete block[uuid];
        }
        for (var uuid in reactions.left) {
            if (uuid in block) delete block[uuid];
        }
        for (var uuid in block) {
            block[uuid].move(200,0);
        }
        Util.attrNS(PathwayEditor.membrane, {
            width: bbox.width+200,
        });
    }
});

$(document).on("click", "#btn-membrane-width-minus", function (ev) {
    var bbox = PathwayEditor.membrane.getBBox();
    if (bbox) {
        var reactions = PathwayEditor.findReactionsOnMembrane();
        var block = reactions.right;
        for (var uuid in reactions.right) {
            block = Object.assign(block, PathwayEditor.findConnectedComponents(reactions.right[uuid]));
        }
        for (var uuid in reactions.top) {
            if (uuid in block) delete block[uuid];
        }
        for (var uuid in reactions.left) {
            if (uuid in block) delete block[uuid];
        }
        for (var uuid in block) {
            block[uuid].move(-200,0);
        }
        Util.attrNS(PathwayEditor.membrane, {
            width: bbox.width-200,
        });
    }
});

$(document).on("click", "#btn-membrane-height-plus", function (ev) {
    var bbox = PathwayEditor.membrane.getBBox();
    if (bbox) {
        var reactions = PathwayEditor.findReactionsOnMembrane();
        var block = reactions.bottom;
        for (var uuid in reactions.bottom) {
            block = Object.assign(block, PathwayEditor.findConnectedComponents(reactions.bottom[uuid]));
        }
        for (var uuid in reactions.top) {
            if (uuid in block) delete block[uuid];
        }
        for (var uuid in reactions.left) {
            if (uuid in block) delete block[uuid];
        }
        for (var uuid in block) {
            block[uuid].move(0,200);
        }
        Util.attrNS(PathwayEditor.membrane, {
            height: bbox.height+200,
        });
    }
});

$(document).on("click", "#btn-membrane-height-minus", function (ev) {
    var bbox = PathwayEditor.membrane.getBBox();
    if (bbox) {
        var reactions = PathwayEditor.findReactionsOnMembrane();
        var block = reactions.bottom;
        for (var uuid in reactions.bottom) {
            block = Object.assign(block, PathwayEditor.findConnectedComponents(reactions.bottom[uuid]));
        }
        for (var uuid in reactions.top) {
            if (uuid in block) delete block[uuid];
        }
        for (var uuid in reactions.left) {
            if (uuid in block) delete block[uuid];
        }
        for (var uuid in block) {
            block[uuid].move(0,-200);
        }
        Util.attrNS(PathwayEditor.membrane, {
            height: bbox.height-200,
        });
    }
});

$(document).on("click", "#btn-select-connected-components", function (ev) {
    var reaction = $("#menu-reaction").prop("reaction");
    var els = PathwayEditor.findAllConnectedComponents(reaction);
    for (var uuid in els) {
        if (els[uuid] instanceof Pathway.Reaction) PathwayEditor.addToSelection(els[uuid]);
    }
    $("#menu-reaction").hide();
});

$(document).on("click", "#btn-editor", function(ev) {
    var reaction = $("#menu-reaction").prop("reaction");
    PathwayEditor.editorDialog.show();
    PathwayEditor.editorDialog.reaction = reaction;
    $("#reaction-editor-iframe").prop("src", "reaction/editor?id=" + reaction.id);
    $("#menu-reaction").hide();
});

$(document).on("click", "#btn-add-reaction-to-pathway", function(ev){
    PathwayEditor.creatorDialog.show();
    $("#reaction-creator-iframe").prop("src", "reaction/editor");
});

$(window).on("keydown", function(evt){
    if (evt.shiftKey) {
       if (PathwayEditor.selectedViews.length == 1 && PathwayEditor.selectedViews[0] instanceof Pathway.Reaction) {
           var reaction = PathwayEditor.selectedViews[0];
           switch (evt.keyCode) {
               case 38:
               case 40:
                    reaction.layoutDirection = reaction.layoutDirection == "vertical" ? "horizontal" : "vertical";
                    reaction.layout();
                    reaction.setState("selected");
                   break;
               case 37:
               case 39:
                    reaction.layoutReversed = !reaction.layoutReversed;
                    reaction.layout();
                    reaction.setState("selected");
                   break;
               case 27:
                   PathwayEditor.clearSelection();
                   break;
       
           }
       }
    } else {
        var els2move = PathwayEditor.selectedViews.length ? PathwayEditor.selectedViews.concat(PathwayEditor.autoselectViews) : [PathwayEditor.canvas];
        var dx = 0, dy = 0;
        switch (evt.keyCode) {
            case 38:
                dy = -1;
                break;
            case 40:
                dy = 1;
                break;
            case 37:
                dx = -1;
                break;
            case 39:
                dx = 1;
                break;
            case 27:
                PathwayEditor.clearSelection();
                break;
    
        }
        els2move.forEach(function(each){
            if(each) each.move(dx, dy);
        });
    }
});
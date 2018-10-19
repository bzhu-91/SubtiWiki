// require view.js

var SpannableString = SpannableString || function (src) {
    this.text = src;
    this.spans = [];
}

SpannableString.findOverlap = function (s1, s2) {
    if (s1.stop <= s2.start || s2.stop <= s1.start) {
        return null;
    } else {
        return {
            start: Math.max(s1.start, s2.start),
            stop: Math.min(s1.stop, s2.stop)
        }
    }
}

SpannableString.prototype.addSpan = function (start, stop, attrs, styles) {
    this.spans.push({
        start: start,
        stop: stop,
        attrs: attrs,
        styles: styles
    });
}

SpannableString.prototype.clearAll = function () {
    this.spans = [];
}

SpannableString.prototype.toSVGTextElement = function () {
    var self = this;
    var view = Util.elNS("text");
    var breakpoints = [0, self.text.length];
    self.spans.forEach(function(each){
        if (breakpoints.indexOf(each.start) == -1) breakpoints.push(each.start);
        if (breakpoints.indexOf(each.stop == -1)) breakpoints.push(each.stop);
    });
    breakpoints = breakpoints.sort(function(a,b){
        return a-b;
    });
    for(var i = 0; i < breakpoints.length - 1; i++) {
        var tspan = Util.elNS("tspan");
        tspan.textContent = self.text.substring(breakpoints[i], breakpoints[i+1]);
        self.spans.forEach(function(each){
            if (SpannableString.findOverlap(each, {
                start: breakpoints[i],
                stop: breakpoints[i+1]
            })) {
                Util.attrNS(tspan, each.attrs);
                for(var key in each.styles) {
                    tspan.style[key] = each.styles[key];
                }
            }
        });
        view.appendChild(tspan);
    }
    return view;
}

// name space
var Pathway = Pathway || {}

/**
 * Pathway.Protein class
 */
// input: src data or the create SVG element
Pathway.Protein = Pathway.Protein || function (input) {
    View.call(this);

    // views
    this._group;
    this._rect;
    this._rect_duplicate;
    this._text;
    this._extra;
    this._extra_text;

    this._isNested = false;
    this._isPlural = false;

    if (input instanceof Element) {
        // instantiate from SVG element
        this.fromSVGElement (input);
    } else {
        for (var i in input) {
            if (input.hasOwnProperty(i)) {
                if (!(i in this)) this[i] = input[i];
                else console.error("conflict of keys ignored in object", input);
            }
        }
    }
}

Pathway.Protein.prototype = Object.create(View.prototype);

Pathway.Protein.constructor = Pathway.protein;

Pathway.Protein.prototype.fromSVGElement = function (dom) {
    var self = this;
    if (dom.tagName == "g" && dom.getAttribute("class") && dom.getAttribute("class").indexOf("protein") > -1) {
        self.view = self._group = dom;
        self._rect = dom.querySelector("rect._protein_rect");
        if (!self._rect) {
            throw new Error("protein rect not found", dom);
        }
        self._text = dom.querySelector("text._protein_text");
        if (!self._text) {
            throw new Error("protein text not found", dom);
        }
        if (dom.getAttribute("class").indexOf("nested") > -1) {
            self._isNested = true;
        }
        self._rect_duplicate = dom.querySelector("rect._protein_rect_duplicate");
        self._extra = dom.querySelector("ellipse._protein_extra");
        self._extra_text = dom.querySelector("text._protein_extra_text");
        
        self.id = dom.getAttribute("id") || "";
        self.title = dom.getAttribute("tilte") || "";

        var vector = Util.getTransformMatrix(dom);
        self.x = vector[4]; self.y = vector[5];
        self.view.wrapper = self;
        self.uuid = self.view.getAttribute("uuid");
    } else throw new Error("dom element is not a protein (type does not match)", dom);
}

Pathway.Protein.prototype.__createView = function () {
    var self = this;

    self._group = Util.elNS("g");
    self._rect = Util.elNS("rect");
    self._rect_duplicate = Util.elNS("rect");
    self._text = Util.elNS("text");
    self._extra = Util.elNS("ellipse");
    self._extra_text = Util.elNS("text");

    Util.attrNS(self._group, {
        class: self._isNested ? "protein nested" : "protein",
        id: self.id || "",
        title: self.title || self.label || ""
    });
    Util.attrNS(self._rect, {
        class: "_protein_rect"
    });
    Util.attrNS(self._rect_duplicate, {
        class: "_protein_rect_duplicated"
    });
    Util.attrNS(self._text, {
        class: "_protein_text",
        "pointer-events": "none",
        "alignment-baseline": "middle"
    });
    Util.attrNS(self._extra, {
        class: "_protein_extra",
    });
    Util.attrNS(self._extra_text, {
        class: "_protein_extra_text",
        "pointer-events": "none"
    });

    if (self._isPlural) {
        self._group.appendChild(self._rect_duplicate);
    }
    Util.appendAll(self._group, [self._rect, self._text]);

    self._text.textContent = self.label || self.name || self.title;

    if (self.extra) {
        Util.appendAll(self._group, [self._extra, self._extra_text]);
    }

    return self._group;
}

Pathway.Protein.prototype.__layout = function () {
    var self = this;
    var hpadding = 20;
    var vpadding = 10;

    var txtBBox = Util.getBBox(self._text);
    var th = txtBBox.height; var tw = txtBBox.width;
    var rh = vpadding*2 + th; var rw = hpadding * 2 + tw;

    Util.attrNS(self._rect, {
        x: -rw/2,
        y: -rh/2,
        width: rw + 0.5,
        height: rh + 0.5
    });
    // overlapping effect
    if (self._rect_duplicate) Util.attrNS(self._rect_duplicate, {
        x: -rw/2 + 5,
        y: -rh/2 + 5,
        width: rw + 0.5,
        height: rh + 0.5
    })

    Util.attrNS(self._text, {
        x: -tw/2,
        y: 0
    });

    if (self.extra) {
        var vpadding_extra = 20;
        var hpadding_extra = 10;

        var etBox = Util.getBBox(self._extra_text);
        var etw = etBox.width; var eth = etBox.height;
        var ew = etw + hpadding_extra*2; var eh = eth + vpadding_extra * 2;

        Util.attrNS(self._extra, {
            cx: rw/2,
            cy: 0,
            rx: ew/2,
            ry: eh/2
        });

        Util.attrNS(self._extra_text, {
            x: rw/2-etw/2,
            y: eth/2
        });
    }
}

Pathway.Protein.prototype.__setState = function (state) {
    var self = this;
    switch (state) {
        case "selected":
        self._rect.style["stroke-width"] = 2;
        break;
        case "normal":
        self._rect.style["stroke-width"] = 1;
        break;
    }
}

/**
 * Pathway.ProteinClass class
 */

Pathway.ProteinClass = Pathway.proteinClass || function(input) {
	//  super class call
	Pathway.Protein.call(this, input);
}

Pathway.ProteinClass.prototype = Object.create(Pathway.Protein.prototype);

Pathway.ProteinClass.constructor = Pathway.ProteinClass;

Pathway.ProteinClass.prototype.fromSVGElement = function(dom){
    var self = this;
	// given a dom element
	if (dom.tagName == "g" && dom.getAttribute("class") == "proteinClass") {
		self._group = self.view = dom;
		// find rect
		self._rect = dom.querySelector("rect._protein_rect");
		if (!self._rect) {
			throw new Error("protein rect not found");
		}
		self._text = dom.querySelector("text._protein_text");
		if (!self._text) {
			throw new Error("protein text not found");
		}
		self._extra = dom.querySelector("ellipse._protein_extra");
		self._extra_text = dom.querySelector("text._protein_extra_text");
		// also need to find the position of the group from the xml
		var vector = Util.getTransformMatrix(dom);
		self.x = vector[4]; self.y = vector[5];
		self.view.wrapper = self;
        self.uuid = self.view.getAttribute("uuid");
        
	} else {
		throw new Error("dom element is not a protein (type does not match)");
	}
}

Pathway.ProteinClass.prototype.__createView = function(){
    var self = this;
    Pathway.Protein.prototype.__createView.call(this);
    Util.attrNS(this._group, {
        class: "proteinClass"
    });
	Util.attrNS(this._rect, {
		style: "stroke-dasharray: 10 5"
	});
	return this._group;
}


/**
 * Pathway.Complex class
 */
Pathway.Complex = Pathway.Complex || function (input) {
    View.call(this);

    this._group;
    this._rect;
    this._text;
    this._components = [];

    if (input instanceof Element) {
        // instantiate from SVG element
        this.fromSVGElement (input);
    } else {
        for (var i in input) {
            if (input.hasOwnProperty(i)) {
                if (!(i in this)) this[i] = input[i];
                else console.error("conflict of keys ignored in object", input);
            }
        }
    }
}

Pathway.Complex.prototype = Object.create(View.prototype);

Pathway.Complex.constructor = Pathway.Complex;

Pathway.Complex.prototype.fromSVGElement = function (dom) {
    var self = this;
    if (dom.tagName == "g" && dom.getAttribute("class") && dom.getAttribute("class").indexOf("complex") > -1) {
        self.view = self._group = dom;
        self._rect = dom.querySelector("rect._complex_rect");
        if (!self._rect) {
            throw new Error("Complex rect not found", dom);
        }
        self._text = dom.querySelector("text._complex_text");
        if (!self._text) {
            throw new Error("Complex text not found", dom);
        }

        var memberDoms = dom.querySelectorAll("g.protein, g.metabolite");
        memberDoms.forEach(function(each){
            if (each.getAttribute("class") && each.getAttribute("class").indexOf("protein") > -1){
                var protein = new Pathway.Protein(each);
                self._components.push(protein);
                protein.parent = self;
            } else if (each.getAttribute("class") && each.getAttribute("class").indexOf("metabolite") > -1) {
                var metabolite = new Pathway.Metabolite(each);
                self._components.push(metabolite);
                metabolite.parent = self;
            } else {
                console.error("Unknown type, ignored", each);
            }
        });

        self.uuid = dom.getAttribute("uuid");
        self.id = dom.getAttribute("id");
        self.view.wrapper = self;
        var vector = Util.getTransformMatrix(self._group);
        self.x = vector[4]; self.y = vector[5];
    }
}

Pathway.Complex.prototype.__createView = function () {
    var self = this;

    self._group = Util.elNS("g");
    self._rect = Util.elNS("rect");
    self._text = Util.elNS("text");

    Util.appendAll(self._group, [self._rect, self._text]);

    if (self.members) {
        self.members.forEach(function(each){
            if (each.type == "protein") {
                var protein = new Pathway.Protein(each);
                protein._isNested = true;
                self._components.push(protein);
            } else if (each.type = "metabolite") {
                var metabolite = new Pathway.Metabolite(each);
                metabolite._isNested = true;
                self._components.push(metabolite);
            } else {
                console.log("unknown complex member");
            }
        });
    }

    Util.attrNS(self._group, {
        class: "complex",
        id: self.id || "",
        uuid: self.uuid
    });

    Util.attrNS(self._rect, {
        class: "_complex_rect"
    });

    Util.attrNS(self._text, {
        class: "_complex_text",
        "pointer-events": "none"
    });

    self._text.textContent = self.label || self.title || "Complex";
    return self._group;
}

/* assemble all the child components */
Pathway.Complex.prototype.__assemble = function () {
    var self = this;
    self._components.forEach(function(each){
        each.appendTo(self);
    });
}

Pathway.Complex.prototype.__layout = function () {
    var self = this;
    var padding = 10;
    var allW = 0, allH = 0, maxW = 0, maxH = 0;

    // layout all child components
    self._components.forEach(function(each){
        each.layout();
        var bbox = each.getBBox();
		maxH = Math.max(bbox.height, maxH);
		maxW = Math.max(bbox.width, maxW);
		allW += bbox.width;
		allH += bbox.height;
    });

    var tbox = Util.getBBox(self._text);
    var rw = Math.max(tbox.width, maxW) + padding * 2;
    var rh = (self._components.length + 2) * padding + allH + tbox.height;
    var x = padding - rw / 2, y = padding - rh / 2;
    
    // position all the child elements
    self._components.forEach(function(each){
        each.position(x,y,"left top");
        var bbox = each.getBBox();
        y += padding + bbox.height;
    });
    Util.attrNS(self._rect, {
        x: -rw/2,
        y: -rh/2,
        height: rh,
        width: rw
    });
    Util.attrNS(self._text, {
        x: padding - rw/2,
        y: y + padding + tbox.height/2
    });
}

Pathway.Complex.prototype.__setState = function (state) {
    var self = this;
    switch(state){
		case "normal":
			self._rect.style["stroke-width"] = 1;
			break;
		case "selected":
			self._rect.style["stroke-width"] = 2;
	}
}

/**
 * Pathway.Metabolite class
 */
Pathway.Metabolite = Pathway.Metabolite || function (input) {
    View.call(this);

    // views
    this._group;
    this._ellipse;
    this._text;
    this._ellipse_duplicate;

    this._isNested = false;
    this._isPlural = false;
    
    if (input instanceof Element) {
        // instantiate from SVG element
        this.fromSVGElement (input);
    } else {
        for (var i in input) {
            if (input.hasOwnProperty(i)) {
                if (!(i in this)) this[i] = input[i];
                else console.error("conflict of keys ignored in object", input);
            }
        }
    }

}

Pathway.Metabolite.prototype = Object.create(View.prototype);

Pathway.Metabolite.constructor = Pathway.Metabolite;

Pathway.Metabolite.prototype.fromSVGElement = function (dom) {
    var self = this;
    if (dom.tagName == "g" && dom.getAttribute("class") && dom.getAttribute("class").indexOf("metabolite") > -1){
        self.view = self._group = dom;
        self._ellipse = dom.querySelector("ellipse._metabolite_ellipse");
        if (!self._ellipse) {
            throw new Error("metabolite ellipse not found", dom);
        }
        self._text = dom.querySelector("text._metabolite_text");
        if (!self._text) {
            throw new Error("metabolite text not found");
        }
        self.title = dom.getAttribute("title");

        self._ellipse_duplicate = dom.querySelector("ellipse._metabolite_ellipse_duplicate");
        var vector = Util.getTransformMatrix(dom);
        self.x = vector[4];
        self.y = vector[5];

        self.id = dom.getAttribute("id");
        self.uuid = dom.getAttribute("uuid");
        self.side = dom.getAttribute("side");
        self.view.wrapper = self;
    } else throw new Error("dom element is not a metabolite (type does not match)");
}

/* handle supscripts and superscripts, those should not overlap */
Pathway.Metabolite.prototype.createText = function () {
    var self = this;
    var view = Util.elNS("text");
    var text = self.label || self.name || self.title;
    var split = function (txt) {
        var regexp1 = /<sup>(.+?)<\/sup>/gi;
		var m1 = regexp1.exec(txt);
		var regexp2 = /<sub>(.+?)<\/sub>/gi;
		var m2 = regexp2.exec(txt);
		if (m1 && m2) {
			var s1 = m1.index, s2 = m2.index;
			var e1 = s1 + m1[0].length, e2 = s2 + m2[0].length;
			if (e1 < s2) {
				return [txt,s1,e1,m1[1],"sup"]
			} else {
				return [txt,s2,e2,m2[1],"sub"]
			}
		} else if (m1) {
			return [txt,m1.index,m1.index + m1[0].length,m1[1],"sup"]
		} else if (m2) {
			return [txt,m2.index,m2.index + m2[0].length,m2[1],"sub"]
		}
    }
    var render = function (txt, s, e, replacement, type) {
        var txt1 = txt.substr(0, s); // trailing
        var txt3 = txt.substr(e, txt.length - e); // tailing
        if (split(txt1)) {
            render.apply(self, split(txt1));
        } else {
            var tspan1 = Util.elNS("tspan");
            tspan1.textContent = txt1;
            view.appendChild(tspan1);
        }
        var tspan2 = Util.elNS("tspan");
        tspan2.textContent = replacement;
        Util.attrNS(tspan2, {
            "baseline-shift": type,
            "style":"font-size: x-small"
        });
        view.appendChild(tspan2);
        if (split(txt3)) {
            render.apply(self, split(txt3));
        } else {
            var tspan3 = Util.elNS("tspan");
            tspan3.textContent = txt3;
            view.appendChild(tspan3);
        } 
    }
    if (split(text)) {
        render.apply(self, split(text));
    } else {
        view.textContent = text;
    }
    return view;
}

Pathway.Metabolite.prototype.__createView = function () {
    var self = this;

    self._group = Util.elNS("g");
    self._ellipse = Util.elNS("ellipse");
    self._ellipse_duplicate = Util.elNS("ellipse");
    self._text = self.createText();

    if (self._isPlural) {
        self._group.appendChild(self._ellipse_duplicate);
    }

    Util.appendAll(self._group, [self._ellipse, self._text]);

    Util.attrNS(self._group, {
        class: self._isNested ? "metabolite nested": "metabolite",
        id: self.id || "",
        uuid: self.uuid,
        side: self.side,
        title: self.title
    });

    Util.attrNS(self._ellipse, {
        class: "_metabolite_ellipse",
    });

    Util.attrNS(self._ellipse_duplicate, {
        class: "_metabolite_ellipse_duplicate"
    });

    Util.attrNS(self._text, {
        class: "_metabolite_text",
        "pointer-events": "none",
        "alignment-baseline": "middle"
    });

    return self._group;
}

Pathway.Metabolite.prototype.__layout = function () {
    var self = this;
    var vpadding = 10;
    var hpadding = 20;
    var bbox = Util.getBBox(self._text);
    var th = bbox.height;
	var tw = bbox.width;
	var rw = hpadding * 2 + bbox.width;
    var rh = vpadding * 2 + bbox.height;
    
	Util.attrNS(self._ellipse, {
		rx: rw/2 + 0.5, ry: rh/2 + 0.5,
		cx: 0, cy: 0
    });
    if (self._ellipse_duplicate) {
        Util.attrNS(self._ellipse_duplicate, {
            rx: rw/2 + 0.5, ry: rh/2 + 0.5,
            cx: 2, cy: -2
        });
    }
	Util.attrNS(self._text, {
		x: -tw/2, y: 0
	});
}

Pathway.Metabolite.prototype.__setState = function(state) {
    var self = this;
	switch (state) {
		case "selected":
			self._ellipse.style["stroke-width"] = 2;
			break;
		case "normal":
			self._ellipse.style["stroke-width"] = 1;
			break;
	}
}

/**
 * Pathway.JoinPoint class
 */
Pathway.JoinPoint = Pathway.JoinPoint || function (input) {
    View.call(this);

    // views
    this._ellipse;

    if (input) {
        if (input instanceof Element) {
            // instantiate from SVG element
            this.fromSVGElement (input);
        } else {
            for (var i in input) {
                if (input.hasOwnProperty(i)) {
                    if (!(i in this)) this[i] = input[i];
                    else console.error("conflict of keys ignored in object", input);
                }
            }
        }
    }
}

Pathway.JoinPoint.prototype = Object.create(View.prototype);

Pathway.JoinPoint.constructor = Pathway.JoinPoint;

Pathway.JoinPoint.prototype.fromSVGElement = function (dom) {
    var self = this;
    if (dom.tagName == "ellipse" && dom.getAttribute("class") == "joinPoint"){
        self.view = self._ellipse = dom;
        self.x = Number(dom.getAttribute("cx"));
        self.y = Number(dom.getAttribute("cy"));
        self.uuid = dom.getAttribute("uuid");
        self.type = dom.getAttribute("type");
        self.view.wrapper = self;
    } else throw new Error("dom element is not a joinpoint (type does not match)", dom);
}

Pathway.JoinPoint.prototype.__createView = function(){
    var self = this;
	self._ellipse = Util.elNS("ellipse");
	Util.attrNS(self._ellipse, {
        class: "joinPoint",
        type: self.type
	});
	return self._ellipse;
}

Pathway.JoinPoint.prototype.__setState = function(state) {
    var self = this;
	switch(state){
		case "normal":
			self._ellipse.style.fill = "black";
			break;
		case "selected":
			self._ellipse.style.fill = "#0099cc";
	}
}

/**
 * Pathway.Link class
 */
Pathway.Link = Pathway.Link || function(input, parent) {
	//  super class call
	View.call(this);

	this._ellipse;
	this._group;
    this._path;
	this._arrow;
	this.controlPoint = {
		x:0, y:0
    };
    
	if (input instanceof Element) {
        // instantiate from SVG element
        this.parent = parent;
        this.fromSVGElement(input);
    } else {
        for (var i in input) {
            if (input.hasOwnProperty(i)) {
                if (!(i in this)) this[i] = input[i];
                else console.error("conflict of keys ignored in object", input);
            }
        }
    }

	if (!(this.from instanceof View) || !(this.to instanceof View)) {
		throw new Error("can only join two View subclasses");
	}
}

Pathway.Link.prototype = Object.create(View.prototype);

Pathway.Link.constructor = Pathway.Link;

Pathway.Link.prototype.fromSVGElement = function (dom) {
    var self = this;
    if (dom.tagName == "g" && dom.getAttribute("class") == "linkGroup") {
        self.view = self._group = dom;
        self.isCurved = dom.getAttribute("iscurved") ? dom.getAttribute("iscurved") == "true" : false;
        self.hasArrow = dom.getAttribute("hasarrow") ? dom.getAttribute("hasarrow") == "true" : false;
        self.isDashed = dom.getAttribute("isdashed") ? dom.getAttribute("isdashed") == "true" : false;

        ["from", "to", "uuid"].forEach(function(key){
            var value = dom.getAttribute(key);
            if (value != null) {
                self[key] = value;
            } else {
				throw new Error("data corrupted for this linkGroup, " + key + " is missing", dom);
            }
        });

        var fromDom = document.querySelector("[uuid='" + self.from + "']");
        var toDom = document.querySelector("[uuid='"+self.to+"']");

        if (self.isCurved) {
            self.controlPoint = {
                x: dom.getAttribute("ctrlx"),
                y: dom.getAttribute("ctrly")
            }
        }
        if (fromDom && toDom) {
            self.from = fromDom.wrapper;
            self.to = toDom.wrapper;
        } else throw {message: "link element lost", from: {
            uuid: self.from,
            dom: fromDom
        }, to: {
            uuid: self.to,
            dom: toDom
        }};
        self.from.addLink(self);
        self.to.addLink(self);
        self.uuid = dom.getAttribute("uuid");
        self._path = dom.querySelector("path.line");
        self._arrow = dom.querySelector("path.arrow");
        self.view.wrapper = self;
    }
}

Pathway.Link.prototype.__createView = function () {
    var self = this;

    self._group = Util.elNS("g");
    self._path = Util.elNS("path");
    self._arrow = Util.elNS("path");

    Util.attrNS(self._group, {
        from: self.from.uuid,
        to: self.to.uuid,
        class: "linkGroup",
        iscurved: self.isCurved || false,
        hasarrow: self.hasArrow || false,
        isdashed: self.isDashed || false,
    });

    Util.attrNS(self._path, {
        class: "line",
        "pointer-events": "none"
    });

    Util.attrNS(self._arrow, {
        class: "arrow",
        "pointer-events": "none"
    });

    if (self.dashed) {
        Util.attrNS(self._path, {
            "stroke-dasharray": "10 5"
        });
    }

    Util.appendAll(self._group, [self._path, self._arrow]);

    self.from.addLink(self);
    self.to.addLink(self);
    
	return self._group;
}

Pathway.Link.prototype.setControlPoint = function (point) {
    this.controlPoint = point;
    this.layout();
}

Pathway.Link.prototype.__layout = function () {
    var self = this;

    var getDis = function (p1, p2) {
        return Math.sqrt(Math.pow(p1.x-p2.x,2) + Math.pow(p1.y-p2.y,2));
    }

    var p1 = {x: self.from.x,y: self.from.y}
    var p2 = {x: self.to.x,y: self.to.y}
    
    var pdata1 = "M" + p1.x + " " + p1.y + " ";
    var pdata2 = ""; // for arrow

    var d = getDis(p1,p2);

    Util.attrNS(self._group, {
        ctrlx: self.controlPoint.x,
        ctrly: self.controlPoint.y
    });

    if (self.isCurved) {
        pdata1 += "Q " + self.controlPoint.x + " " + self.controlPoint.y + ",";
        Util.attrNS(self._group, {
            ctrlx: self.controlPoint.x,
            ctrly: self.controlPoint.y
        });
        pdata1 += p2.x + " " + p2.y;
    } else {
        pdata1 += "L "+p2.x+","+p2.y;
    }

    Util.attrNS(self._path, {
        d: pdata1
    });

    // now get the arrow
    if (d > 0 && self.hasArrow) {
        var arLen = 30, arWidth = 5;
		// p0 arrow point (theta, d/2+arlen/2)
		// get path middle point
		var len = self._path.getTotalLength();
		if (len) {
            var arrowPosition = len/3;
            if (self.to instanceof Pathway.JoinPoint) {
                arrowPosition = len/3*2
            }
			var arP0 = self._path.getPointAtLength(arrowPosition+arLen/2);
			var arP2 = self._path.getPointAtLength(arrowPosition-arLen/2);
			var a = (arP0.y - arP2.y) / (arP0.x - arP2.x);
			var m = - 1 / a; var n = arP2.y - m * arP2.x;
			// line arp0arp2
			// y = ax + b
			// the line which pass arp2 and is perpendicular to apr0arp2
			// y = mx + n
			// 
			if (Math.abs(a) < 0.1) {
				// a = 0
				var arP1 = {
					x: arP2.x ,
					y: arP2.y + arWidth
				}
				var arP3 = {
					x: arP2.x ,
					y: arP2.y - arWidth
				}
			} else {
				var delta = arWidth / Math.sqrt(m*m+1);
				var arP1 = {
					x: arP2.x - delta,
					y: m * (arP2.x - delta) + n
				}
				var arP3 = {
					x: arP2.x + delta,
					y: m * (arP2.x + delta) + n
				}
			}
			pdata2 = " M "+arP0.x+" "+arP0.y+" "
				+ "L "+arP1.x+" "+arP1.y+" "
				+ "L "+arP2.x+" "+arP2.y+" "
				+ "L "+arP3.x+" "+arP3.y+" "
				+ "L "+arP0.x+" "+arP0.y+" Z";
        }
        Util.attrNS(self._arrow, {
            d: pdata2
        })
    }
}

Pathway.Link.prototype.appendTo = function (parent) {
    var self = this;
    if(!self._group) self.createView();
	if (parent.getUnderLayer) {
		parent.getUnderLayer().appendChild(self._group);
	} else if (parent.getChildViewContainer) {
        parent.getChildViewContainer().appendChild(self._group);
    } else {
        parent.view.appendChild(self._group);
    }
	self.parent = parent;
	self.layout();
}

Pathway.Reaction = Pathway.Reaction || function (input) {
    View.call(this);

    // views
    this._group;
    this._underlayer;
    this._rect;
    this._title;

    // components
    this._catalysts = [];
    this._lhs = [];
    this._rhs = [];
    this._center;
    this._jpCatalysts;
    this._internalLinks = [];
    this._jpLhs;
    this._jpRhs;
    this._ctrlLhs = {x:0, y:0};
    this._ctrlRhs = {x:0, y:0};

    this.enableLock = true;

    if (input instanceof Element) {
        // instantiate from SVG element
        this.fromSVGElement (input);
    } else {
        for (var i in input) {
            if (input.hasOwnProperty(i)) {
                if (!(i in this)) this[i] = input[i];
                else console.error("conflict of keys ignored in object", input);
            }
        }
    }
}

Pathway.Reaction.prototype = Object.create(View.prototype);

Pathway.Reaction.constructor = Pathway.Reaction;

Pathway.Reaction.prototype.fromSVGElement = function (dom) {
    var self = this;
    if (dom.tagName == "g" && dom.getAttribute("class") == "reaction") {
        self.uuid = dom.getAttribute("uuid");
        self.id = dom.getAttribute("id");
        self.layoutDirection = dom.getAttribute("layoutdirection");
		self.layoutReversed = dom.getAttribute("layoutreversed") ? dom.getAttribute("layoutreversed") == "true" : false;
		self.reversible = dom.getAttribute("reversible") ? dom.getAttribute("reversible") == "true": false;
		self.width = Number(dom.getAttribute("width"));
        self.height = Number(dom.getAttribute("height"));
        
        self._ctrlLhs = {
			x: Number(dom.getAttribute("ctrllhsx")),
			y: Number(dom.getAttribute("ctrllhsy"))
        }
        
        self._ctrlRhs = {
            x: Number(dom.getAttribute("ctrlrhsx")),
			y: Number(dom.getAttribute("ctrlrhsy"))
        }

        // get all proteins
        var doms = dom.querySelectorAll("g.protein");
        doms.forEach(function(each){
            if (each.getAttribute("class").indexOf("nested") == -1) {
                // exclude nested protein dom in the complex
                var protein = new Pathway.Protein(each);
                protein.parent = self;
                self._catalysts.push(protein);
            }
        });

        // get all protein class
        var doms = dom.querySelectorAll("g.proteinClass");
        doms.forEach(function(each){
            if (each.getAttribute("class").indexOf("nested") == -1){
                var proteinClass = new Pathway.ProteinClass(each);
                proteinClass.parent = self;
                self._catalysts.push(proteinClass);
            }
        })

        // get all complexes
        var doms = dom.querySelectorAll("g.complex");
        doms.forEach(function(each){
            var complex = new Pathway.Complex(each);
            complex.parent = self;
            self._catalysts.push(complex);
        });

        // get all metabolites
        var doms = dom.querySelectorAll("g.metabolite");
        doms.forEach(function(each){
            var metabolite = new Pathway.Metabolite(each);
            metabolite.parent = self;
            if (metabolite.side == "L") {
                self._lhs.push(metabolite);
            } else {
                self._rhs.push(metabolite);
            }
        });

        // get all join points
        var doms = dom.querySelectorAll("ellipse.joinPoint");
        doms.forEach(function(each){
            var joinPoint = new Pathway.JoinPoint(each);
            joinPoint.parent = self;
            switch(joinPoint.type) {
                case "center":
                    self._center = joinPoint;
                    break;
                case "lhs":
                    self._jpLhs = joinPoint;
                    break;
                case "rhs":
                    self._jpRhs = joinPoint;
                    break;
                case "catalyst":
                    self._jpCatalysts = joinPoint;
                    break;
            }
        });

        // get all links
        var doms = dom.querySelectorAll("g.linkGroup");
        doms.forEach(function(each){
            var link = new Pathway.Link(each, self);
            self._internalLinks.push(link);
            if (link.from instanceof Pathway.JoinPoint) {
                link.setControlPoint(self._ctrlRhs);
            } else {
                link.setControlPoint(self._ctrlLhs);
            }
        });

        self.view = self._group = dom;
        self.view.wrapper = self;
        self._underlayer = dom.querySelector("g.underlayer");
        self._rect = dom.querySelector("rect.background");
        
        var vector = Util.getTransformMatrix(dom);
		self.x = vector[4];
        self.y = vector[5];
        
        self.updateMoveFunction();
    }
}

/* the movement of right/left hand join point will change
        1) the ctrl point
        2) background box
*/

Pathway.Reaction.prototype.updateMoveFunction = function () {
    var self = this;
    var move = function(dx,dy){
        Object.getPrototypeOf(this).move.call(this,dx,dy);
        var a = b = 0;
        // avoid 0 division
        if (Math.abs(self._jpRhs.x - self._jpLhs.x) > 0.1) {
            // y = ax + b; the line for the main axis of the reaction
            a = (self._jpRhs.y - self._jpLhs.y) / (self._jpRhs.x - self._jpLhs.x);
            b = self._jpRhs.y - a * self._jpRhs.x;
            var dx1 = Math.sqrt(8100 / (a*a + 1)); // 8100 = 90^2 (distance from right hand join point and ctrl point), dx1 (delta x) from join point to ctrl point of side
            if (self._jpRhs.x < self._jpLhs.x) {
                dx1 = -dx1
            }
            self._ctrlLhs.x = self._jpLhs.x - dx1;
            self._ctrlRhs.x = self._jpRhs.x + dx1;
            self._ctrlLhs.y = a * self._ctrlLhs.x + b; // calc the y with the line
            self._ctrlRhs.y = a * self._ctrlRhs.x + b;
        } else {
            // reaction axis is nearly vertical, x = b
            self._ctrlLhs.y = self._jpLhs.y - 90;
            self._ctrlRhs.y = self._jpRhs.y + 90;
        }
        // write the change of the control point the reaction node;
        Util.attrNS(self._group, {
            ctrllhsx: self._ctrlLhs.x,
            ctrlrhsx: self._ctrlRhs.x,
            ctrllhsy: self._ctrlLhs.y,
            ctrlrhsy: self._ctrlRhs.y
        });
        var midx = (self._jpLhs.x + self._jpRhs.x) / 2;
        var midy = (self._jpLhs.y + self._jpRhs.y) / 2;
        self._center.position(midx, midy);

        // background rect changes with the movement of the 
        Util.attrNS(self._rect, {
            x: Math.min(self._jpLhs.x, self._jpRhs.x) - 20,
            y: Math.min(self._jpLhs.y, self._jpRhs.y) - 20,
            width: Math.abs(self._jpLhs.x - self._jpRhs.x) + 40,
            height: Math.abs(self._jpLhs.y - self._jpRhs.y) + 40,
        });

        self._internalLinks.forEach(function(link){
            link.layout(); // update all the links
        });

        self._jpLhs.move = move;
        self._jpRhs.move = move;
    }
    self._jpLhs.move = move;
    self._jpRhs.move = move;
}

Pathway.Reaction.prototype.__createView = function () {
    var self = this;

    self._group = Util.elNS("g");
    self._underlayer = Util.elNS("g");
    self._rect = Util.elNS("rect");
    self._title = Util.elNS("title");

    Util.attrNS(self._rect, {
        class: "background"
    });

    self._title.textContent = "R" + self.id + ": " + self.equation;

    Util.appendAll(self._group, [self._title, self._rect, self._underlayer]);

    Util.attrNS(self._group, {
		class: "reaction",
		id: self.id || '',
		layoutdirection: self.layoutDirection,
		layoutreversed: self.layoutReversed || false,
		width: self.width,
		height: self.height,
    });
    
	Util.attrNS(self._underlayer, {
		class: "underLayer"
    });
    
    self._center = new Pathway.JoinPoint();
    self._jpLhs = new Pathway.JoinPoint();
    self._jpRhs = new Pathway.JoinPoint();

    self.updateMoveFunction();

    var link = new Pathway.Link({
        from: self._jpLhs,
        to: self._jpRhs,
        isCurved: false,
        hasArrow: false,
        isDashed: false,
    });

    self._internalLinks.push(link);
    
    self.reactants.forEach(function(m){
        m.side = "L";
        var metabolite = new Pathway.Metabolite(m);
        if (m.coefficient && m.coefficient > 1) {
            metabolite._isPlural = true;
        }
        self._lhs.push(metabolite);
        var link = new Pathway.Link({
            from: metabolite,
            to: self._jpLhs,
            isCurved: true,
            hasArrow: !self.reversible,
            isDashed: self.novel
        });
        self._internalLinks.push(link);
    });

    self.products.forEach(function(m){
        m.side = "R";
        var metabolite = new Pathway.Metabolite(m);
        if (m.coefficient && m.coefficient > 1) {
            metabolite._isPlural = true;
        }
        self._rhs.push(metabolite);
        var link = new Pathway.Link({
            from: self._jpRhs,
            to: metabolite,
            isCurved: true,
            hasArrow: !self.reversible,
            isDashed: self.novel
        });
        self._internalLinks.push(link);
    });

    if (self.catalysts && self.catalysts.length) {
        var joinpoint;
        if (self.catalysts.length == 1) {
            joinpoint = self._center;
        } else {
            self._jpCatalysts = new Pathway.JoinPoint();
            var link = new Pathway.Link({
                from: self._center,
                to: self._jpCatalysts,
                isCurved: false,
                isDashed: self.novel,
                hasArrow: false
            });
            self._internalLinks.push(link);
            joinpoint = self._jpCatalysts; 
        }
        self.catalysts.forEach(function(c){
            var view;
            switch(c.type) {
                case "protein":
                    view = new Pathway.Protein(c);
                    break;
                case "complex":
                    view = new Pathway.Complex(c);
                    break;
                case "class":
                    view = new Pathway.ProteinClass(c);
                    break;
                default:
                    view = null;
            }
            if (view) {
                self._catalysts.push(view);
                var link = new Pathway.Link({
                    from: view,
                    to: joinpoint,
                    isCurved: false,
                    isDashed: self.novel,
                    hasArrow: false
                });
                self._internalLinks.push(link);
            } else console.log("unknown catalyst type", c);
        });
    }
    return self._group;
}

Pathway.Reaction.prototype.__assemble = function () {
    var self = this;
    self._center.appendTo(self);
    self._jpLhs.appendTo(self);
    self._jpRhs.appendTo(self);
    
    self._center.attr({
        type: "center",
        style: "fill: transparent; stroke: transparent; rx:0; ry:0"
    });

    self._jpLhs.attr({
        type: "lhs"
    });

    self._jpRhs.attr({
        type: "rhs"
    });

    if (self._catalysts && self._catalysts.length > 1) {
        self._jpCatalysts.appendTo(self);
        self._jpCatalysts.attr({
            type: "catalyst"
        });
    }

    [self._rhs, self._lhs, self._catalysts, self._internalLinks].forEach(function(elements){
        elements.forEach(function(each){
            each.appendTo(self);
        });
    });
}

Pathway.Reaction.prototype.getUnderLayer = function () {
    return this._underlayer;
}

// it is complicated
Pathway.Reaction.prototype.__layout = function () {
    var self = this;

    // layout all components
    [self._rhs, self._lhs, self._catalysts, self._internalLinks].forEach(function(elements){
        elements.forEach(function(each){
            each.layout();
        });
    });

    // set position parameters (to reduce code duplication)
    var ry,rx, alpha = self.rotation || (Math.PI / 10); // rotation is how far the metabolites are initially seperated from the each other
    // rx, ry determine the size of the diagram of the 
    rx = self.height || 300;
    ry = self.width || 100;

    //position the center point
    self._center.position(0,0);

    // position the join and ctrl points
    if (self.layoutDirection == "vertical") {
        if (self.layoutReversed) {
            self._jpLhs.position(0, 30);
            self._jpRhs.position(0, -30);
            self._ctrlLhs = {x:0, y:120};
            self._ctrlRhs = {x:0, y:-120};
        } else {
            self._jpLhs.position(0, -30);
            self._jpRhs.position(0, 30);
            self._ctrlLhs = {x:0, y:-120};
            self._ctrlRhs = {x:0, y:120};
        }
    } else {
        if (self.layoutReversed) {
            self._jpLhs.position(30,0);
            self._jpRhs.position(-30,0);
            self._ctrlLhs = {x:120, y:0};
            self._ctrlRhs = {x:-120, y:0};
        } else {
            self._jpLhs.position(-30,0);
            self._jpRhs.position(30,0);
            self._ctrlLhs = {x:-120, y:0};
            self._ctrlRhs = {x:120, y:0};
        }
    }

    // write to the dom element
    Util.attrNS(self._group, {
        ctrllhsx: self._ctrlLhs.x,
        ctrllhsy: self._ctrlLhs.y,
        ctrlrhsx: self._ctrlRhs.x,
        ctrlrhsy: self._ctrlRhs.y
    });

    // tbh I really cant remember how this is done
    // the metabolties are arranged up/down the main axis by alpha
	var arrangeMetabolites = function(mainAxisAngle, metabolites) {
        var initInterval = Math.floor(metabolites.length / 2) + (metabolites.length % 2 ? 0:-0.5)
        var point = mainAxisAngle - initInterval * alpha;
        for(var i = 0; i < metabolites.length; i++) {
            var m = metabolites[i];
            // locked elements are stored in the reactions "lock" attr
            if (!self.isInLockGroup(m)) {
                var variance = 1;
                if (self.layoutDirection == "vertical") {
                    variance = (i % 2) ? 1: 0.8;  
                }
                m.position(rx*Math.cos(point)*variance, rx*Math.sin(point)*variance);
                point += alpha;
            }
        }
    }
    
    var mainAxisAngle;
    if (self.layoutDirection == "vertical") {
        mainAxisAngle = Math.PI * 1.5;
    } else {
        mainAxisAngle = Math.PI;
    }

    if (self.layoutReversed) {
        arrangeMetabolites(mainAxisAngle, self._rhs);
        arrangeMetabolites(mainAxisAngle - Math.PI, self._lhs);
    } else {
        arrangeMetabolites(mainAxisAngle, self._lhs);
        arrangeMetabolites(mainAxisAngle - Math.PI, self._rhs);
    }

    // position all the catalysts
    if (self._catalysts.length) {
        if (self._catalysts.length > 1) {
            // if there is only one catalyst, then do not show the join point
            if (self.layoutDirection == "vertical") {
                self._jpCatalysts.position(rx/8,0);
            } else {
                self._jpCatalysts.position(0, -ry/3.5);
            }
        }
        var margin = 10; // margin between the single catalysts
        if (self.layoutDirection == "vertical") {
            var allH = maxW = 0;
            self._catalysts.forEach(function(each){
                var bbox = each.getBBox();
                if (bbox.width > maxW) {
                    maxW = bbox.width;
                }
                allH += bbox.height;
            });
            allH += margin*(self._catalysts.length-1);
            var x = ry; var y = - allH / 2; // here position is determined by left upper corder (instead of 0,0)
            self._catalysts.forEach(function(each){
                if (!each.attr("locked")) {
                    var bbox = each.getBBox();
                    each.position(x,y,"left top");
                    y += bbox.height + margin;
                }
            });
        } else {
            var allW = maxH = 0;
            self._catalysts.forEach(function(each){
                var bbox = each.getBBox();
                if (bbox.height > maxH) {
                    maxH = bbox.height;
                }
                allW += bbox.width;
            });
            allW += margin*(self._catalysts.length -1);
            var x = - allW/2, y = -ry;
            self._catalysts.forEach(function(each){
                if (!each.attr("locked")) {
                    var bbox = each.getBBox();
                    each.position(x,y,"left bottom");
                    x += bbox.width + margin;
                }
            });
        }
    }

    // layout all the links
    self._internalLinks.forEach(function(link){
        var a = link.from instanceof Pathway.JoinPoint;
        var b = link.to instanceof Pathway.JoinPoint;
        if (a && !b){
            link.setControlPoint(self._ctrlRhs);
        } else if (!a && b) {
            link.setControlPoint(self._ctrlLhs);
        }
        link.layout();
    });

    // reset the focus box to get the proper bbox
    Util.attrNS(self._rect, {
        width:0,
        height:0
    });
    Util.attrNS(self._rect, {
        x: Math.min(self._jpLhs.x, self._jpRhs.x) - 20,
        y: Math.min(self._jpLhs.y, self._jpRhs.y) - 20,
        width: Math.abs(self._jpLhs.x - self._jpRhs.x) + 40,
        height: Math.abs(self._jpLhs.y - self._jpRhs.y) + 40
    });
}

Pathway.Reaction.prototype.__setState = function (state) {
    var self = this;
    var bbox = self.getBBox();
    Util.attrNS(self._rect, {
        width:0,
        height:0
    });
    switch(state){
        case 'selected':
        Util.attrNS(self._rect, {
            x: bbox.x - 10,
            y: bbox.y - 10,
            width: bbox.width + 20,
            height: bbox.height + 20
        });
        break;
        case 'normal':
        Util.attrNS(self._rect, {
            x: Math.min(self._jpLhs.x, self._jpRhs.x) - 20,
            y: Math.min(self._jpLhs.y, self._jpRhs.y) - 20,
            width: Math.abs(self._jpLhs.x - self._jpRhs.x) + 40,
            height: Math.abs(self._jpLhs.y - self._jpRhs.y) + 40
        });
    }
}

/**
 * Pathway.Canvas class
 */
Pathway.Canvas = Pathway.Canvas || function (input) {
    View.call(this);

    // views
    this._svg;
    this._group;
    this._scale = 1;

    if (input instanceof Element) {
        // instantiate from SVG element
        this.fromSVGElement (input);
    } else {
        for (var i in input) {
            if (input.hasOwnProperty(i)) {
                if (!(i in this)) this[i] = input[i];
                else console.error("conflict of keys ignored in object", input);
            }
        }
    }
}

Pathway.Canvas.prototype = Object.create(View.prototype);

Pathway.Canvas.constructor = Pathway.Canvas;

//TODO: need to load the scale from the svg element here
Pathway.Canvas.prototype.fromSVGElement = function (dom) {
    var self = this;
    if (dom.tagName == "svg") {
        self._svg = dom;
        self._group = dom.querySelector("#viewport");
        if (!self._group) throw new Error("View port is not found");

        var vector = Util.getTransformMatrix(self._group);
        self.x = vector[4];
        self.y = vector[5];
        self._scale = vector[0];
        self.view = self._svg;
    } else {
        throw new Error("type error, dom element given is not svg");
    }
}

Pathway.Canvas.prototype.__createView = function () {
    var self = this;
    self._svg = Util.elNS("svg");
    Util.attr(self._svg, {
        version: "1.0",
        xmlns: svgNS
    });
    self._group = Util.elNS("g");
    Util.attrNS(self._group, {
        id: "viewport",
        class: "root"
    });
    Util.setTransformMatrix(self._group, [1,0,0,1,0,0]);
    Util.appendAll(self._svg, [self._group]);
    return self._svg;
}

Pathway.Canvas.prototype.getChildViewContainer = function () {
    return this._group;
}

Pathway.Canvas.prototype.__position = function (x,y) {
    var self = this;
    var vector = Util.getTransformMatrix(self._group);
    vector[4] = x, vector[5] = y;
    Util.attrNS(self._group, {
        transform: "matrix(" + vector.join(",") + ")"
    });
}

Pathway.Canvas.prototype.appendTo = function (parent) {
    var self = this;
    self.createView();
    parent.appendChild(self._svg);
}

Pathway.Canvas.prototype.append = function (view) {
    this._group.appendChild(view);
} 

Pathway.Canvas.prototype.setScale = function (scale) {
    var self = this;
    self._scale = scale;
    var vector = Util.getTransformMatrix(self._group);
    vector[0] = vector[3] = scale;
	Util.attrNS(self._group, {
		transform: "matrix(".concat(vector.join(), ")")
	});
}

Pathway.Canvas.prototype.getScale = function () {
    return this._scale;
}
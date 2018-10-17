var Util = {
    deepExtend: function(a, b, allowDel){
        for(var prop in b){
            if (b.hasOwnProperty(prop)) {
                if (b[prop] && b[prop].constructor == Object) {
                    if (a[prop] === undefined) a[prop] = {};
                    if (a[prop].constructor == Object) Util.deepExtend(a[prop],b [prop]);
                } else if(b[prop] && b[prop] instanceof Array) {
                    a[prop] = [];
                    for (var i = 0; i < b[prop].length; i++) {
                        a[prop].push(b[prop][i]);
                    }
                } else if(b[prop] == null && allowDel) {
                    delete a[prop];
                } else if(b[prop] != null) {
                    a[prop] = b[prop];
                }
            }
        }
        return a;
    },
    loadScript: function (url, callback) {
        var script = document.createElement("script")
        script.type = "text/javascript";

        if (script.readyState){  //IE
            script.onreadystatechange = function(){
                if (script.readyState == "loaded" ||
                        script.readyState == "complete"){
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {  //Others
            script.onload = function(){
                callback();
            };
        }

        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
    },
    invertMatrix: function (M) {
        // source: http://blog.acipo.com/matrix-inversion-in-javascript/
        // I use Guassian Elimination to calculate the inverse:
        // (1) 'augment' the matrix (left) by the identity (on the right)
        // (2) Turn the matrix on the left into the identity by elemetry row ops
        // (3) The matrix on the right is the inverse (was the identity matrix)
        // There are 3 elemtary row ops: (I combine b and c in my code)
        // (a) Swap 2 rows
        // (b) Multiply a row by a scalar
        // (c) Add 2 rows
        
        //if the matrix isn't square: exit (error)
        if(M.length !== M[0].length){return;}
        
        //create the identity matrix (I), and a copy (C) of the original
        var i=0, ii=0, j=0, dim=M.length, e=0, t=0;
        var I = [], C = [];
        for(i=0; i<dim; i+=1){
            // Create the row
            I[I.length]=[];
            C[C.length]=[];
            for(j=0; j<dim; j+=1){
                
                //if we're on the diagonal, put a 1 (for identity)
                if(i==j){ I[i][j] = 1; }
                else{ I[i][j] = 0; }
                
                // Also, make the copy of the original
                C[i][j] = M[i][j];
            }
        }
        
        // Perform elementary row operations
        for(i=0; i<dim; i+=1){
            // get the element e on the diagonal
            e = C[i][i];
            
            // if we have a 0 on the diagonal (we'll need to swap with a lower row)
            if(e==0){
                //look through every row below the i'th row
                for(ii=i+1; ii<dim; ii+=1){
                    //if the ii'th row has a non-0 in the i'th col
                    if(C[ii][i] != 0){
                        //it would make the diagonal have a non-0 so swap it
                        for(j=0; j<dim; j++){
                            e = C[i][j];       //temp store i'th row
                            C[i][j] = C[ii][j];//replace i'th row by ii'th
                            C[ii][j] = e;      //repace ii'th by temp
                            e = I[i][j];       //temp store i'th row
                            I[i][j] = I[ii][j];//replace i'th row by ii'th
                            I[ii][j] = e;      //repace ii'th by temp
                        }
                        //don't bother checking other rows since we've swapped
                        break;
                    }
                }
                //get the new diagonal
                e = C[i][i];
                //if it's still 0, not invertable (error)
                if(e==0){return}
            }
            
            // Scale this row down by e (so we have a 1 on the diagonal)
            for(j=0; j<dim; j++){
                C[i][j] = C[i][j]/e; //apply to original matrix
                I[i][j] = I[i][j]/e; //apply to identity
            }
            
            // Subtract this row (scaled appropriately for each row) from ALL of
            // the other rows so that there will be 0's in this column in the
            // rows above and below this one
            for(ii=0; ii<dim; ii++){
                // Only apply to other rows (we want a 1 on the diagonal)
                if(ii==i){continue;}
                
                // We want to change this element to 0
                e = C[ii][i];
                
                // Subtract (the row above(or below) scaled by e) from (the
                // current row) but start at the i'th column and assume all the
                // stuff left of diagonal is 0 (which it should be if we made this
                // algorithm correctly)
                for(j=0; j<dim; j++){
                    C[ii][j] -= e*C[i][j]; //apply to original matrix
                    I[ii][j] -= e*I[i][j]; //apply to identity
                }
            }
        }
        
        //we've done all operations, C should be the identity
        //matrix I should be the inverse:
        return I;
    }
}

var CanvasView = CanvasView || function () {
    this._defaultOptions = {};
    this._globalOptions = {};
    this._privateOptions = {};
    this._actualOptions = {};
    this._listeners = {};
    this._parentView;

    this.visibility = true;
    this.x = this.y = 0;
    this.childViews = [];
}

/**
 * abstract function, must be implemented by subclasses
 */
CanvasView.prototype.draw = function () {
    throw new Error("Subclass of view should override the draw method");
}

/**
 * abstract function, must be implemented by subclasses
 */
CanvasView.prototype.isPointInView = function () {
    throw new Error("Subclass of view should override the isPointInView method");
}

/**
 * set the state of the current view
 * @param {string} s state
 * @param {boolean} draw if set true, view will be redrawn
 */
CanvasView.prototype.setState = function (s, draw) {
    if (this.state !== s) {
        this.state = s;
        draw = draw === undefined ? true: draw;
        if (draw) this.redraw();
    }
}

/**
 * get the actual option (composed from default, global, private options)
 */
CanvasView.prototype.getOptions = function () {
    return this._actualOptions;
}

/**
 * set the default options of the view
 * @param {object} options the options
 * @param {boolean} draw if set true, view will be redrawn
 */
CanvasView.prototype.setDefaultOptions = function(options, draw){
    var self = this;
    draw = draw === undefined ? true : draw;
    self._defaultOptions = Util.deepExtend(self._defaultOptions, options, true);
    self.composeOptions();
    if (draw) {
        this.draw();
    }
}

/**
 * set the global option of the view
 * @param {object} options the options
 * @param {boolean} draw if set true, view will be redrawn
 */
CanvasView.prototype.setGlobalOptions = function(options, draw){
    var self = this;
    draw = draw === undefined ? true : draw;
    self._globalOptions = Util.deepExtend(self._globalOptions, options, true);
    self.composeOptions();
    if (draw) {
        this.draw();
    }
}

/**
 * set the private option of the view
 * @param {object} options options
 * @param {boolean} draw if set true, view.redraw will be called
 */
CanvasView.prototype.setOptions = function(options, draw){
    var self = this;
    draw = draw === undefined ? true : draw;
    self._privateOptions = Util.deepExtend(self._privateOptions, options, true);
    self.composeOptions();
    if (draw) {
        window.diagram = this;
        this.redraw();
    }
}

/**
 * compose the options to get the actual one
 */
CanvasView.prototype.composeOptions = function(){
    var self = this;
    self._actualOptions = Util.deepExtend(self._actualOptions, self._defaultOptions);
    self._actualOptions = Util.deepExtend(self._actualOptions, self._globalOptions);
    self._actualOptions = Util.deepExtend(self._actualOptions, self._privateOptions);
}

/**
 * add a child view
 * @param {CanvasView} view child view to be added
 */
CanvasView.prototype.addView = function (view) {
    this.childViews.push(view);
    view._parentView = this;
}

/**
 * change the visibility of the view
 * @param {boolean} draw if set true, view.redraw will be called
 */
CanvasView.prototype.show = function (draw) {
    this.visibility = true;
    draw = draw === undefined ? true : draw;
    if (draw) this.redraw();
}

/**
 * change the visibility of the view
 * @param {boolean} draw if set true, view.redraw will be called
 */
CanvasView.prototype.hide = function (draw) {
    this.visibility = hide;
    draw = draw === undefined ? true : draw;
    if (draw) this.redraw();
}

/**
 * redraw the view
 * @param {object} o extra options (full) used to redraw
 */
CanvasView.prototype.redraw = function (o) {
    var view = this;
    while (view._parentView) {
        view = view._parentView;
    }
    if (view.clearViewPort) {
        view.clearViewPort();
        view.redraw();
    } else {
        console.error("redraw is not successful")
    }
}

/**
 * find the child view at a point
 * @param {number} x x-coordinate
 * @param {number} y y-coordinate
 */
CanvasView.prototype.findChildViewsAt = function (x,y) {
    var result = [];
    this.childViews.forEach(function(view){
        if (view.isPointInView(x,y)) {
            result.push(view);
        }
    });
    return result;
}


window.Genome = window.Genome || {
    modules: {},
    defaultOptions: {
        resolution: 0.1,
        width: "auto",
        height: "auto",
        background: 'transparent',
        scrollEnabled: true,
        dataViewer: {
            height: 150,
            isReversed: false,
            axisMax: "auto",
            layout: {},
            labelColor: "black",
            labelFont: "16px sans-serif",
            enableSampling: true
        },
        axis: {
            layout: {
                y: 60
            }
        }
    }
}

/**
 * @param {array} raw the raw data, an array of objects and each object should have "start","stop","strand","label" keys
 * @param {enum: "linear", "cyclic"} type the type of the genome
 * @param {int} genomeLength the length of the genome, required when type is cyclic
 */
Genome.dataSet = Genome.dataSet || function (raw, type, genomeLength) {
    this._max = 0; this._min = Infinity; this._length;

	if (type == 'cyclic') {
		if (!genomeLength) {
			throw new Error('Please give length of the genome when it is cyclic');
		}
	}

	this.rawData = raw;
	this.type = type;
	this.genomeLength = genomeLength;
	this.analyse();
}

/**
 * translate coordinates, get max,min,length
 * if the genome is cyclic, it will be break into a line
 */
Genome.dataSet.prototype.analyse = function () {
	var self = this;
	// if the genomic type is cyclic, need to find a break point
	if (self.type == 'cyclic') {
		var intervals = [];
		self.rawData.forEach(function(each){
			var intervalStart = Math.floor(each.start / (self.genomeLength / 64));
			intervals[intervalStart] = true;
			var intervalEnd = Math.floor(each.stop / (self.genomeLength / 64));
			intervals[intervalEnd] = true;
		});

		var breakInterval = 0;
		for (; breakInterval < intervals.length; breakInterval++) {
			if(!intervals[breakInterval]) break;
		}
	}

	//  if break point is 0 or the last, treat as linear
	if (self.type == 'linear' || breakInterval == 0 || breakInterval == intervals.length) {
		self.rawData.forEach(function(each){
			each._start = each.start;
			each._stop = each.stop;
			self._min = Math.min(self._min, each._start, each._stop);
			self._max = Math.max(self._max, each._start, each._stop);
		})
		self._breakPoint = self.genomeLength;
	} else {
		self._breakPoint = Math.floor(self.genomeLength * (breakInterval / 64 + 1 / 128));
		self.rawData.forEach(function(each){
			each._start = self._translateCoordinates(each.start);
			each._stop = self._translateCoordinates(each.stop);
			self._min = Math.min(self._min, each._start, each._stop);
			self._max = Math.max(self._max, each._start, each._stop);
		})
	}
	self._length = self._max - self._min;
}

/**
 * translate the real position on the genome to the relative position
 * for linear genome, this will not change the pos
 * @param {number} pos position of the genome
 * @returns {number} relative position on the genome (if is cyclic)
 */
Genome.dataSet.prototype._translateCoordinates = function (pos) {
	var self = this;
	// if no break point, then no translation needed
	if (self._breakPoint) {
		if (pos > self._breakPoint) return pos - self.genomeLength;
		else return pos;
	} else return pos;
}

/**
 * 
 * @param {number} pos the relative position in the browser
 * @returns {number} the real position on the genome
 */
Genome.dataSet.prototype._translateCoordinatesReal = function (pos) {
	var self = this;
	if (self._breakPoint) {
		if (pos < 0) return pos + self.genomeLength;
		else return pos;
	} else return pos;
}

/**
 * wrapper over the rawdata
 * @param {function} callback callback function for the foreach of the rawdata
 */
Genome.dataSet.prototype.forEach = function (callback) {
	var self = this;
	self.rawData.forEach(callback);
}

/**
 * get the right most coordinate
 * @returns {number} returns the right most coordinate of the current dataset
 */
Genome.dataSet.prototype.getMax = function () {
	return this._translateCoordinatesReal(this._max);
}

/**
 * get the left most coordinate
 * @returns {number} returns the left most coordinate of the current dataset
 */
Genome.dataSet.prototype.getMin = function () {
	return this._translateCoordinatesReal(this._min);
}

/**
 * register modules for the genome browser, e.g. gene, TSS, upshift, downshift
 * the module (object) will be used as prototype of the hanlders of different types of data
 * the handler will be subclasses of CanvasView
 * @param {string} type the type of data
 * @param {object} module the module to handle a certain type of data,
 */
Genome.registerModule = function (type, module) {
    var func = function () {
        CanvasView.call(this);
        module.construct.apply(this, arguments);
    }
    func.prototype = Object.create(CanvasView.prototype);
    func.constructor = func;
    for (var key in module) {
        func.prototype[key] = module[key];
    }
    func.prototype.__super = Object.create(CanvasView.prototype);
    Genome.modules[type] = func;
}

/**
 * the diagram
 * @param {DOMElement} container the container of the diagram
 * @param {Genome.dataSet} dataSet the dataset
 * @param {object} options the global options
 */
Genome.diagram = Genome.diagram || function (container, dataSet, options) {
    CanvasView.call(this);

    options = options || {}

    this.scale = 1;
    this.pixelRatio = 1;
    this.container = container;
    this.x = this.y = 0;
    this.left = this.right = 0;
    this.layout = Genome.layout;
    this.dataSet = dataSet;
    this.options = options;
    this.axis;
    this.position;
    this.range = null;
    

    this.setDefaultOptions(Genome.defaultOptions);
    this.setGlobalOptions(this.options);
    
    // clean the container
    while (container.firstChild) container.firstChild.remove();
    // create the canvas
    this.canvas = document.createElement("canvas");
    // append to container
    container.appendChild(this.canvas);
    
    this.currentTransform = {
        a:1,b:0,c:0,d:1,e:0,f:0
    }

    this.currentTransform.invert = function () {
        var matrix = [
            [this.a,this.c,this.e],
            [this.b,this.d,this.f],
            [0,0,1]
        ];
        var inverted = Util.invertMatrix(matrix);
        return {
            a: inverted[0][0],
            b: inverted[1][0],
            c: inverted[0][1],
            d: inverted[1][1],
            e: inverted[0][2],
            f: inverted[1][2],
            invert: this.invert
        }
    }
    this.ctx = this.canvas.getContext("2d");

    this.setPixelRatio(2, false);

    this.createView();
    this.draw();

    if (this.getOptions().scrollEnabled) {
        this.enableScroll();
    }

    this.enableHover();
}

Genome.diagram.prototype = Object.create(CanvasView.prototype);

Genome.diagram.constructor = Genome.diagram;

/**
 * set the pixel ratio of the canvas, default = 2
 * @param {number} ratio the pixel ratio
 * @param {boolean} draw whether trigger redraw or not, default is true
 */
Genome.diagram.prototype.setPixelRatio = function (ratio, draw) {
    if (ratio) {
        this.pixelRatio = ratio;
        this.canvas.width = this.container.clientWidth * ratio;
        this.canvas.height = this.container.clientHeight * ratio;
        this.canvas.style.width = this.container.clientWidth + "px";
        this.canvas.style.height = this.container.clientHeight + "px";
        this.ctx.scale(ratio,ratio);
        // keep the track of the currentTransform
        this.currentTransform.a = ratio;
        this.currentTransform.d = ratio;
        draw = draw === undefined ? true : draw;
        if (draw) {
            this.redraw();
        }
    }
}

/**
 * highlight an area with a semi transparent black shade
 * @param {Int} start start position at genome (in bp)
 * @param {Int} stop stop position at genome (in bp)
 * @param {boolean} redraw trigger canvas redraw, default is true
 */
Genome.diagram.prototype.selectRange = function (start, stop, redraw) {
    var self = this;
    this.range = {
        start: self.dataSet._translateCoordinates(start),
        stop: self.dataSet._translateCoordinates(stop)
    }
    redraw = redraw === undefined ? true : redraw;
    if (redraw) {
        this.redraw();
    }
}

/**
 * clear the range highlight
 * @param {boolean} redraw trigger canvas redraw or not, default is true
 */
Genome.diagram.prototype.clearRange = function (redraw) {
    this.range = null;
    redraw = redraw === undefined ? true : redraw;
    if (redraw) {
        this.redraw();
    }
}

/**
 * clear the current view port for redraw
 */
Genome.diagram.prototype.clearViewPort = function () {
    this.ctx.clearRect(this.x,0,this.canvas.width, this.canvas.height);
}

/**
 * create child views based on the given dataset
 * axis included
 */
Genome.diagram.prototype.createView = function () {
    var self = this;
    this.axis = new Genome.axis(this.dataSet, this.getOptions().resolution, this.ctx);
    this.axis.setGlobalOptions(this.getOptions().axis);
    this.childViews = [this.axis];
    this.dataSet.forEach(function(el){
        if (el.type && el.type in Genome.modules) {
            var className = Genome.modules[el.type];
            var view = new className(el, self.ctx);
            var resolution = self.getOptions().resolution;
            view.setGlobalOptions(Object.assign({
                resolution: resolution,
            }, self.getOptions()[el.type] || {}), false);
            self.addView(view);
        } else console.error("No module available for type: " + el.type);
    });
}

/**
 * draw the diagram by drawing all child views
 */
Genome.diagram.prototype.draw = function () {
    // draw the range box;
    if (this.range) {
        var o = this.getOptions();
        this.ctx.fillStyle = "black";
        this.ctx.globalAlpha = 0.2;
        this.ctx.fillRect(
            this.range.start * o.resolution,
            0,
            (this.range.stop - this.range.start) * o.resolution,
            this.canvas.height
        );
        this.ctx.globalAlpha = 1.0;
    }
    this.childViews.forEach(function(view){
        view.draw();
    });
}

/**
 * clear the viewport and redraw
 */
Genome.diagram.prototype.redraw = function () {
    var self = this;
    this.clearViewPort();
    this.childViews.forEach(function(view){
        var resolution = self.getOptions().resolution;
        view.setGlobalOptions(Object.assign({
            resolution: resolution,
        }, self.getOptions()[view.type] || {}), false);
    })
    this.draw();
}

/**
 * set the data of the diagram
 * @param {Genome.dataSet} dataSet the new dataset
 */
Genome.diagram.prototype.setData = function (dataSet) {
    this.dataSet = dataSet;
    // reset translate
    this.createView();
    this.redraw();
}

/**
 * move the viewport to a certain base pair
 * @param {number} basepair the position to move to, in bp
 * @param {enum: "left", "middle", "right"} where the anchor point
 */
Genome.diagram.prototype.moveTo = function (basepair, where) {
    where = where || "left";
    var coordinate = this.dataSet._translateCoordinates(basepair) * this.getOptions().resolution;
    var viewPortSpan = Math.round(this.canvas.width / this.pixelRatio / this.getOptions().resolution);
    console.log(viewPortSpan);
    var x;
    switch (where) {
        case 'left':
            x = coordinate;
            this.left = basepair;
            this.right = basepair + viewPortSpan;
            break;
        case 'middle':
            x = coordinate - (this.canvas.width / 2 / this.pixelRatio);
            this.left = basepair - Math.round(viewPortSpan / 2);
            break;
            case 'right':
            x = coordinate - this.canvas.width / this.pixelRatio;
            this.left = basepair - viewPortSpan;
            break;
    }
    this.ctx.translate(this.x - x,0);
    this.currentTransform.e = x;
    this.x = x;
    this.redraw();
}

/**
 * change the layout of the diagram
 * @param {object} layout the new layout
 */
Genome.diagram.prototype.setLayout = function (layout) {
    for(var i in layout) {
        var option = {}
        option[i] = {
            layout: layout[i]
        }
        this.setOptions(option, false);
    }
    this.redraw();
}

/**
 * get the mouse position in the canvas
 * @param {DOMMouseEvent} evt the event
 */
Genome.diagram.prototype.getMousePos = function (evt) {
    var pos = this.getMousePosNoTransformation(evt);
    var x = pos.x, y = pos.y;
    // considder transform
    x = x * inverted.a + y * inverted.c + inverted.e;
    y = x * inverted.b + y * inverted.d + inverted.f;
    
    return {
        x:x,y:y
    }
}

/**
 * get the mouse position in the canvas but canvas transform are ignored
 * @param {DOMMouseEvent} evt the event
 */
Genome.diagram.prototype.getMousePosNoTransformation = function (evt) {
    var rect = this.canvas.getBoundingClientRect(),
        x = evt.clientX - rect.left,
        y = evt.clientY - rect.top;
    // consider pixel ratio
    x = (evt.clientX - rect.left) * this.pixelRatio;
    y = (evt.clientY - rect.top) * this.pixelRatio;
    
    return {
        x:x,y:y
    }
}

/**
 * add an event listener, a wrapper function is created so that the event object the handler recieves will have the key "currentViews"
 * @param {string} type event type
 * @param {function} func event handler
 */
Genome.diagram.prototype.on = function (type, func) {
    var self = this;
    if (!(type in this._listeners)) {
        this._listeners[type] = [];
    }
    var wrapper = function (evt) {
        var pos = self.getMousePosNoTransformation(evt);
        var childViews = self.findChildViewsAt(pos.x, pos.y);
        if (childViews.length) {
            evt.currentViews = childViews.reverse();
        }
        func.call(this, evt);
    }
    func.wrapper = wrapper;
    if (this._listeners[type].indexOf(func) === -1) {
        this._listeners[type].push(func);
        this.canvas.addEventListener(type, wrapper);
    }
}

/**
 * remove an event listener
 * @param {string} type event type
 * @param {function} func handler to be removed
 */
Genome.diagram.prototype.off = function (type, func) {
    if (func) {
        var idx = this._listeners[type].indexOf(func);
        if (idx > -1 && func.wrapper) {
            canvas.removeEventListener(type, func.wrapper);
            this._listeners.splice(idx,1);
        }
    } else {
        this._listeners[type].forEach(function(listener){
            canvas.removeEventListener(tpye, listener.wrapper);
        });
        this._listeners[type] = [];
    }
}

/**
 * trigger an event
 * @param {string} type event type
 * @param {object} event the event for trigger
 */
Genome.diagram.prototype.trigger = function (type, event) {
    var self = this;
    if (type in this._listeners) {
        this._listeners[type].forEach(function(func){
            func.wrapper.call(self, event);
        })
    }
}

/**
 * enable the scroll function
 */
Genome.diagram.prototype.enableScroll = function () {
    var self = this;
    var inJob = false;
    var scroll = function (ev) {
        if (inJob) return;
        inJob = true;
        ev.preventDefault();
		var delta = Math.max(-1, Math.min(1, (ev.wheelDelta || -ev.detail)));
        self.move(50 * delta);
        inJob = false;
    }

    this.canvas.addEventListener("mousewheel", scroll);
    this.canvas.addEventListener("DOMMouseScroll", scroll);

    var touch = function(ev){
		var currentX = ev.touches[0].clientX;
		self.on("touchmove", function(ev){
			ev.preventDefault();
			self.move((ev.touches[0].clientX - currentX) * self.getOptions().resolution);
		});
		self.on("touchend", function(ev){
			ev.preventDefault();
			self.off("touchmove")
		});
    }

    // for touch screen
    this.canvas.addEventListener("touchstart", touch);


    this.disableScroll = function () {
        this.canvas.removeEventListener("mousewheel", scroll);
        this.canvas.removeEventListener("DOMMouseScroll", scroll);
        this.canvas.removeEventListener("touchstart", touch);
    }

    this.enableScroll = function () {
        this.canvas.addEventListener("mousewheel", scroll);
        this.canvas.addEventListener("DOMMouseScroll", scroll);
        this.canvas.addEventListener("touchstart", touch);
    }
}

/**
 * enable the hover function
 * set the "hover" state of the target view
 */
Genome.diagram.prototype.enableHover = function () {
    var self = this;

    var hover = null;
    this.on("mousemove", function(evt){
        if (evt.currentViews) {
            var top = evt.currentViews[0];
            if (hover && hover != top) {
                hover.setState("normal", false);
            } 
            hover = top;
            hover.setState("hover");
            document.body.cursor = "pointer"
        } else if (hover) {
            hover.setState("normal");
            hover = null;
        }
    })
}

/**
 * move the viewport by pixel (NOT bp)
 * will trigger leftedge/ rightedge event
 * @param {number} dx the delta x to move
 */
Genome.diagram.prototype.move = function (dx) {
    var self = this;
    var dSpan = Math.round(dx / self.getOptions().resolution);
    if (dx > 0) {
        // judge on left edge
    
        var minX = this.getOptions().resolution * this.dataSet._min;
        if (this.x - dx < minX) {
            this.trigger("leftedge", {
                previous: self.dataSet._min
            });
            dx = this.x - minX;
        }
    } else {
        var maxX = this.getOptions().resolution * this.dataSet._max - this.canvas.width / this.pixelRatio;
        if (this.x - dx > maxX) {
            this.trigger("rightedge", {
                next: self.dataSet._max
            });
            dx = this.x - maxX;
        }
    }
    this.ctx.translate(dx,0);
    this.currentTransform.e += dx;
    this.x -= dx;
    this.left -= dSpan;
    this.right -= dSpan;
    this.redraw();
}

/**
 * move the viewport so that the view with the given id will be presented in the center
 * @param {string} geneId the id of the gene (or other elements)
 */
Genome.diagram.prototype.focus = function (geneId) {
    var start = null;
    for (var i = 0; i < this.childViews.length; i++) {
        if (this.childViews[i].id == geneId) {
            start = this.childViews[i].start;
            break;
        }
    }
    if (start) {
        this.moveTo(start, "middle");
        return true;
    }
    return false;
}

/**
 * @param {Genome.dataSet} dataSet of the diagram
 * @param {number} resolution the resolution of the diagram
 * @param {DOMCanvasContext2D} ctx the context of the canvas of the diagram
 */
Genome.axis = Genome.axis || function (dataSet,resolution, ctx) {
    CanvasView.call(this);

    this.type = "axis";

    this.resolution = resolution;
    this.max = dataSet._max;
    this.min = dataSet._min;

    this.dataSet = dataSet;
    this.ctx = ctx;
    this.x1 = this.min * resolution;
    this.x2 = this.max * resolution;
    this.setDefaultOptions(Genome.defaultOptions.axis);
}

Genome.axis.prototype = Object.create(CanvasView.prototype);

Genome.axis.constructor = Genome.axis;

/**
 * always return false (no mouse events)
 * @param {number} x x
 * @param {number} y y
 * @returns {boolean} false
 */
Genome.axis.prototype.isPointInView = function (x,y) {
    return false; // no pointer event available
}

/**
 * draw the axis
 */
Genome.axis.prototype.draw = function () {
    this.y1 = this.y2 = this.getOptions().layout.y;

    // draw the line
    var linePath = new Path2D();
    linePath.moveTo(this.x1,this.y1);
    linePath.lineTo(this.x2,this.y2);
    this.ctx.strokeStyle = "#333";
    this.ctx.lineWidth = 1;
    this.ctx.stroke(linePath);

    // draw all the ticks
    var interval = 200 / this.resolution; // have a tick every 200px (200 / resolution) bp
    interval = Math.ceil(interval / 1000) * 1000; // round it ups

    var tickStart = Math.ceil(this.min / interval) * interval // round up
    var currentPos = tickStart;

    while (currentPos <= this.max) {
        var realCoordinates = this.dataSet._translateCoordinatesReal(currentPos);
        this.drawTick(currentPos * this.resolution, realCoordinates);
        currentPos += interval;
    }
}

/**
 * draw the tick on the axis
 * @param {number} position the position of the tick (in pixel)
 * @param {number} label the label of the tick
 */
Genome.axis.prototype.drawTick = function (position, label) {
    var linePath = new Path2D();
    linePath.moveTo(position, this.y1 - 3);
    linePath.lineTo(position, this.y2);
    this.ctx.strokeStyle = "#333";
    this.ctx.lineWidth = 2;
    this.ctx.stroke(linePath);

    // text
    var textWidth = this.ctx.measureText(label).width;
    this.ctx.fillStyle = "#333";
    this.ctx.font = "10px sans-serif";
    this.ctx.textBaseline = "middle"
    this.ctx.fillText(label, position - textWidth/2, this.y1 + 10);
};

/**
 * constructor of dataViewer class
 * @param {Genome.diagram} diagram  the diagram to append the viewer to
 * @param {object} data the expression data
 * @param {object} layout the layout information (y)
 */
Genome.dataViewer = Genome.dataViewer || function (input) {
    CanvasView.call(this);
    var self = this;
    ["diagram", "label"].forEach(function(key){
        if (key in input) {
            self[key] = input[key];
        } else throw new Error (key + " is required for the constuctor of Genome.dataViewer class");
    });

    if (input.data) this.data = input.data;
    else this.data = [];

    this._pts = [];

    this.canvas = this.diagram.canvas;
    this.ctx = this.diagram.ctx;

    this.setDefaultOptions(Genome.defaultOptions.dataViewer);

    if (input.options) {
        this.setOptions(input.options, false);
    }

    this.paths = [];
    this.max = 0;

    this.nextLabelPosition = 0;
    if (this.data.length) {
        this.prepare();
        this.draw();
    }
}

Genome.dataViewer.prototype = Object.create(CanvasView.prototype);

Genome.dataViewer.constructor = Genome.dataViewer;

/**
 * find the max of the dataset
 */
Genome.dataViewer.prototype.prepare = function () {
    var self = this;
    this._pts = [];
    this.data.forEach(function(dataSet){
        self._pts.push(self.sortDataSet(dataSet));
    });
}

/**
 * 
 * @param {object} dataSet sort the dataset by translate x coordinate
 */
Genome.dataViewer.prototype.sortDataSet = function (dataSet) {
    var self = this;
    var dataPts = [];
    for(var i in dataSet.data) {
        dataPts.push({
            x: self.diagram.dataSet._translateCoordinates(i),
            y: dataSet.data[i]
        });
    }
    return dataPts.sort(function(a,b){
        return a.x - b.x;
    });
}

/**
 * draw the dataViewer
 */
Genome.dataViewer.prototype.draw = function () {
    var self = this, o = this.getOptions();
    if (o.axisMax == "auto") {
        this.max = 0;
        this.data.forEach(function(dataSet){
            for(var i in dataSet.data) {
                if (dataSet.data[i] > self.max) {
                    self.max = dataSet.data[i];
                }
            }
        });
    } else {
        this.max = o.axisMax;
    }

    window.test = this;
    // draw viewer label
    self.ctx.font = o.labelFont;
    self.ctx.fillStyle = o.labelColor;
    self.ctx.textBaseline = "top";
    self.ctx.fillText(self.label, self.diagram.x, o.layout.y + 5);
    self.nextLabelPosition = self.diagram.x + self.ctx.measureText(self.label).width + 10;

    for(var i = 0; i < this.data.length; i++) {
        this.drawDataSet(this.data[i], this._pts[i]);
    }
}

/**
 * draw a single dataset
 * @param {Object} dataSet a single dataset
        required keys of dataSet:
        label: the label of the dataset, will show on the canvas
        data: the data
        color: the color code of the drawing
 */
Genome.dataViewer.prototype.drawDataSet = function (dataSet, sorted) {
    var self = this;
    var o = this.getOptions();

    // draw legend
    // measure text
    self.ctx.font = "16px sans-serif";
    var textWidth = self.ctx.measureText(dataSet.label).width;
    // rect
    self.ctx.fillStyle = dataSet.color;
    self.ctx.fillRect(self.nextLabelPosition, o.layout.y, textWidth + 20, 26);
    // text
    self.ctx.fillStyle = dataSet.labelColor || "white";
    self.ctx.lineWidth = 1;
    self.ctx.textBaseline = "top";
    self.ctx.fillText(dataSet.label, self.nextLabelPosition + 10, o.layout.y + 5);

    self.nextLabelPosition += textWidth + 30;

    var x = y = 0;
    // sampling based on the resolution
    var samplingRatio = Math.ceil(0.5 / this.diagram.getOptions().resolution);
    var pts = [];
    sorted.forEach(function(pt){
        if (!o.enableSampling || pt.x % samplingRatio == 0) {
            x = Math.ceil(pt.x * self.diagram.getOptions().resolution);
            if (o.isReversed) {
                y = pt.y / self.max * (o.height - 40);
            } else {
                y = (1.0 - pt.y / self.max) * (o.height - 40);
            }
            pts.push({x:x,y:y});
        }
    });

    // only draw the points in the viewport!
    pts = pts.filter(function (a){
        return a.x > self.diagram.x && a.x < self.diagram.x + self.canvas.width * self.diagram.pixelRatio;
    })

    if (pts.length) {
        var dataPath = new Path2D();
        dataPath.moveTo(pts[0].x, pts[0].y + o.layout.y + 40);
        for (var i = 1; i < pts.length; i++) {
            dataPath.lineTo(pts[i].x, pts[i].y + o.layout.y + 40);
        }
        this.ctx.strokeStyle = dataSet.color;
        this.ctx.lineWidth = 1;
        this.ctx.stroke(dataPath);
    }
}

/**
 * add an extra dataset to the dataViewer
 * @param {object} dataSet a single dataSet
 * @param {boolean} redraw trigger redraw or not, default is true
 */
Genome.dataViewer.prototype.addDataSet = function (dataSet, redraw) {
    ["label", "color", "data"].forEach(function(key){
        if (!(key in dataSet)) {
            throw new Error(key + " is required for the dataset");
        }
    });

    // cast the numbers 
    for(var i in dataSet.data){
        var val = Number(dataSet.data[i]);
        if (isNaN(val)) {
            console.error(dataSet.data[i] + " can not be parsed as number");
            console.error(new Error().stack);
        } else {
            dataSet.data[i] = val;
        }
    }

    this.data.push(dataSet);
    this._pts.push(this.sortDataSet(dataSet));
    redraw = redraw === undefined ? true: redraw;
    if (redraw) {
        this.redraw();
    }
}

/**
 * set the data of the data viewer
 * @param {array} data the data, array of single datasets
 * @param {boolean} redraw trigger redraw or not, default true
 */
Genome.dataViewer.prototype.setData = function (data, redraw) {
    var self = this;
    redraw = redraw === undefined ? true : redraw;

    this.clearData(false);
    // validate data
    data.forEach(function(dataSet, idx){
        self.addDataSet(dataSet, false);
    });
    if (redraw) this.redraw();
}

/**
 * clear the data of this data viewer
 * @param {boolean} redraw optional, trigger redraw or not, default true
 */
Genome.dataViewer.prototype.clearData = function (redraw) {
    redraw = redraw === undefined ? true : redraw;
    this.data = [];
    this.prepare();
    if (redraw) this.redraw();
}

/**
 * always return false (no mouse event)
 */
Genome.dataViewer.prototype.isPointInView = function () {
    return false;
}



// module gene
;(function(){
    var Gene = Gene || {};
    
    Gene.construct = function (data, ctx) {
        for(var i in data) {
            if (data.hasOwnProperty(i)) {
                this[i] = data[i];
            }
        }
        // set options
        this.setDefaultOptions(Object.assign({resolution: Genome.defaultOptions.resolution}, Genome.defaultOptions.gene), false);
        this.ctx = ctx;
    }
    
    /**
     * draw the gene
     * @param {object} o full options for drawing
     */
    Gene.draw = function (o) {
        if (this.visibility) {
            var o = o || this.getOptions();
            this.y = this.strand == 1 ? o.layout.plus : o.layout.minus;
            this.x = o.resolution * this._start;
            this.width = o.resolution * (this.stop - this.start);
            this.drawPolygon(o);
            this.drawText(o);
        }
    }
    
    /**
     * draw the polygon background
     */
    Gene.drawPolygon = function (o) {
        var self = this;
        var o = o || self.getOptions();
        var pts = [];
        
        var w = (self.stop - self.start) * o.resolution;
        var h = o.height;
        
        // calculate the points for path
        if (w > o.arrowLength) {
            /* set attrs of polygon according to options
                
                0				1	
                ----------------
                |				>	2    // strand = 1
                ----------------
                4				3
        
                    1			0
                    -------------
                2 <				|        // strand = 0
                    -------------
                    3			4
            */
            if (self.strand == 1) {
                pts[0] = [0,0];
                pts[1] = [w-o.arrowLength,0];
                pts[2] = [w, h/2];
                pts[3] = [w-o.arrowLength,h];
                pts[4] = [0,h];
            } else {
                pts[0] = [w,0];
                pts[1] = [o.arrowLength,0];
                pts[2] = [0, h/2];
                pts[3] = [o.arrowLength,h];
                pts[4] = [w,h];
            }
        } else {
            /* set attrs of polygon according to options
               0
               |>1      // strand = 1
               2
                  1
               0 <|     // strand = 0
                  2
            */
            if (self.strand == 1) { // + strand
                pts[0] = [0,0];
                pts[1] = [w,h/2];
                pts[2] = [0,h];
            } else if (self.strand == 0) { // -strand
                pts[0] = [0,h/2];
                pts[1] = [w,0];
                pts[2] = [w,h];
            }
        }
        // begin path
        this.polygonPath = new Path2D();
        // set the styles
        self.ctx.strokeStyle = o.borderColor;
        self.ctx.lineWidth = o.borderWidth;
        self.ctx.fillStyle = self.state == "hover" ? o.hoverColor : o.color;
        
        
        // now drawing
        this.polygonPath.moveTo(pts[0][0] + self.x, pts[0][1] + self.y);
        for(var i = 1; i < pts.length; i++) {
            this.polygonPath.lineTo(pts[i][0] + self.x, pts[i][1] + self.y);
        }

        this.polygonPath.closePath();
        self.ctx.stroke(this.polygonPath);
        self.ctx.fill(this.polygonPath);
    }
    
    /**
     * draw the text over the polygon background
     * @param {object} o extra full set of options for drawing
     */
    Gene.drawText = function (o) {
        var ctx = this.ctx;
        var self = this;
        var o = o || self.getOptions();
    
        // set styles
        ctx.font = o.height - 7.5 + "px sans-serif";
        ctx.fillStyle = o.textColor;
        ctx.textBaseline = "middle";
    
        var leadingSpace = (self.strand == 1? 5: o.arrowLength + 5)
        if (ctx.measureText(self.label).width + leadingSpace < self.width) {
            // draw text
            ctx.fillText(self.label, self.x + leadingSpace, self.y + o.height/2, this.width);
        } else {
        } // do not draw text when it is too long;
    }
    
    /**
     * determine whether is the point in the gene
     * @param {number} x x-coordinate
     * @param {number} y y-coordinate
     */
    Gene.isPointInView = function (x,y) {
        if (this.visibility) {
            return this.ctx.isPointInPath(this.polygonPath, x,y);
        } else return false;
    }

    /**
     * set the state of current gene
     * @param {string} s state
     */
    Gene.setState = function (s) {
        this.__super.setState.apply(this, arguments);
        if (s == "hover") {
            if (!this.hoverBox) {
                var div = document.createElement("div");
                div.style = "border-radius: 4px;background: black; opactiy: 0.6; color: white; padding: 5px 10px; position: absolute;line-height:1.5em";div.innerHTML = this.label + "(" + (this.stop - this.start + 1) + "bp)" + "<br/>" + this.start + (this.strand == 1 ? " -> ":  " <- ") + this.stop;
                this.hoverBox = div;
            }
            if (!this.hoverBox.parentNode) {
                document.body.appendChild(this.hoverBox);
                this.hoverBox.style.top = window.mousePosition.y + "px";
                this.hoverBox.style.left = window.mousePosition.x + "px";
            }
        } else {
            this.hoverBox.remove();
        }
    }

    Genome.registerModule("gene", Gene);

    Genome.defaultOptions.gene = {
        visibility: 'visible',
		arrowLength: 10,
		height: 25,
		textSize: 20,
        color: '#8BC34A',
        hoverColor: 'red',
		textColor: 'white',
		borderColor: 'transparent',
        borderWidth: 0,
        layout: {
            plus: 0, minus: 100
        }
    }

    var recordMousePosition = function (evt) {
        window.mousePosition = {
            x: evt.pageX,
            y: evt.pageY
        }
    };

    document.body.addEventListener("mousemove", recordMousePosition);
    document.body.addEventListener("mouseover", recordMousePosition);
}());

// module upshift/downshift/tss
;(function(){
    var Position = {};

    Position.construct = function (data, ctx) {
        for(var i in data) {
            if (data.hasOwnProperty(i)) {
                this[i] = data[i];
            }
        }
        // set options
        if (!(data.type in Genome.defaultOptions)) {
            console.error("no style defined for type: " + data.type);
        } 
        this.setDefaultOptions(Object.assign({resolution: Genome.defaultOptions.resolution}, Genome.defaultOptions[data.type] || {}), false);
        this.ctx = ctx;
    }

    Position.draw = function (o) {
        if (this.visibility) {
            // set styles
            o = o || this.getOptions();
            this.y = this.strand == 1 ? o.layout.plus : o.layout.minus;
            this.x = this._start * o.resolution;

            this.ovalPath = new Path2D();
            this.ctx.save();
            this.ctx.scale(1,2);
            this.ovalPath.arc(this.x, this.y / 2, 3, 0, 2 * Math.PI, false);
            
            
            this.ctx.fillStyle = o.color;
            this.ctx.strokeStyle = o.borderColor;
            this.ctx.lineWidth = o.borderWidth;
            
            // fill
            this.ctx.fill(this.ovalPath);
            this.ctx.stroke(this.ovalPath);

            this.ctx.restore();
        }
    }

    Position.isPointInView = function (x,y) {
        return this.ctx.isPointInPath(this.ovalPath, x, y / 2);
    }

    Position.setState = function (s) {
        this.__super.setState.call(this,s);
        if (s == "hover") {
            if (!this.hoverBox) {
                var div = document.createElement("div");
                div.style = "border-radius: 4px;background: black; opactiy: 0.6; color: white; padding: 5px 10px; position: absolute;line-height:1.5em";
                div.innerHTML = this.type + ": " + this.start + " (" + (this.strand == 1 ? "+":"-") + ")";
                this.hoverBox = div;
            }
            if (!this.hoverBox.parentNode) {
                document.body.appendChild(this.hoverBox);
                this.hoverBox.style.top = window.mousePosition.y + "px";
                this.hoverBox.style.left = window.mousePosition.x + "px";
            }
        } else {
            this.hoverBox.remove();
        }
    }

    Genome.registerModule("upshift", Position);
    
    Genome.registerModule("downshift", Position);
    
    Genome.registerModule("TSS", Position);

    Genome.defaultOptions.upshift = {
        color: '#A9F5A9',
        borderColor: "transparent",
        borderWidth: 0,
        layout: {
            plus: 40,
            minus: 85,
        }
    }

    Genome.defaultOptions.TSS = {
        color: '#A9F5A9',
        borderColor: "transparent",
        borderWidth: 0,
        layout: {
            plus: 40,
            minus: 85,
        }
    }

    Genome.defaultOptions.downshift = {
        color: '#F78181',
        borderColor: "transparent",
        borderWidth: 0,
        layout: {
            plus: 40,
            minus: 85,
        }
    }
}());

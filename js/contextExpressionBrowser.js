window.ContextExpressionBrowser = window.ContextExpressionBrowser || function (container, conditions) {
    // super class call
    ContextBrowser.call(this, container);
    this.span = 20000;
    this.conditions = conditions;
}

ContextExpressionBrowser.prototype = Object.create(ContextBrowser.prototype);

ContextExpressionBrowser.constructor = ContextExpressionBrowser;

ContextExpressionBrowser.prototype.__super = Object.create(ContextBrowser.prototype);

ContextExpressionBrowser.prototype.createView = function () {
    this.__super.createView.call(this);
    this.view.css({
        border:"none"
    });
    this.diagramContainer.css({
        height: "480px"
    });
}

ContextExpressionBrowser.prototype.setData = function (data, genomeLength, callback) {
    var self = this;
    
    this.__super.setData.apply(this, [data, genomeLength]);
    
    this.viewerPlus = new Genome.dataViewer({
        diagram: self.diagram,
        options: {
            layout: {
                y: 0
            },
        },
        label: "Plus strand",
        enableSamping: false
    });
    
    this.viewerMinus = new Genome.dataViewer({
        diagram: self.diagram,
        options: {
            layout: {
                y: 170
            },
        },
        label: "Minus strand",
        enableSamping: false
    });

    // relayout
    this.diagram.setLayout({
        gene: {
            plus: 380,
            minus: 420
        },
        axis: {
            y: 340
        },
        TSS: {
            plus: 360,
            minus: 470
        },
<<<<<<< HEAD
        Upshift: {
            plus: 360,
            minus: 470
        },
        Downshift: {
=======
        upshift: {
            plus: 360,
            minus: 470
        },
        downshift: {
>>>>>>> b41552964b02c991b1b64b8ece6816ca6a614dda
            plus: 360,
            minus: 470
        }
    });

    

    this.diagram.addView(this.viewerMinus);
    this.diagram.addView(this.viewerPlus);

    this.getExpressionData(callback);
}

ContextExpressionBrowser.prototype.addExpressionData = function (dataPlus, dataMinus) {
    if (dataPlus) this.viewerPlus.addDataSet(dataPlus);
    if (dataMinus) this.viewerMinus.addDataSet(dataMinus);
}

ContextExpressionBrowser.prototype.getExpressionData = function (callback) {
    var self = this;
    var sampling = Math.ceil(1 / self.diagram.getOptions().resolution);
    
    var isLoaded = {}
    for(var id in self.conditions) {
        isLoaded[id] = false;
        var condition = self.conditions[id];
        self.getExpressionDataSet(condition, self.dataSet._min, self.dataSet._max, sampling, function(con){
            isLoaded[con.id] = true;
        });
    }

    var interval = setInterval(function(){
        var allLoaded = true;
        for(var i in isLoaded) {
            allLoaded = allLoaded && isLoaded[i];
        }
        if (allLoaded) {
            clearInterval(interval);
            if (callback) callback.apply(self);
        }
    }, 50);
}

ContextExpressionBrowser.prototype.processExpressionDataSet = function (data, condition) {
    var self = this;
    var parsed = Papa.parse(data);
    var colors = ["red", "blue", "green"];
    if (parsed.errors.length) {
        self.showMessage("Data parsing error");
        console.error(parsed.errors);
    } else {
        data = parsed.data;
        var header = data.shift();
        var idxPos = header.indexOf("position");
        var idxVal = header.indexOf("value");
        if (idxPos > -1 && idxVal > -1) {
            var viewerDataset = {
                color: colors[condition.id % colors.length],
                label: condition.title,
                data: {}
            }           
            data.forEach(function(row){
                var pos = row[idxPos];
                var val = row[idxVal];
                viewerDataset.data[pos] = parseInt(val); // parse to Int
            });
            return viewerDataset;
        } else {
            self.showMessage("Data format error");
        }
    }
}

ContextExpressionBrowser.prototype.getExpressionDataSet = function (condition, start, stop, sampling, callback) {
    var self = this;
    var isLoadedPlus = false, isLoadedMinus = false;
    
    ajax.get({
        url: "expression?range=" + start + "_" + stop + "_1" + "&condition=" + condition.id + "&sampling=" + sampling,
        headers: {Accept: "text/csv"}
    }).done(function(status, data, error, xhr){
        if (status == 200) {
            var dataPlus = self.processExpressionDataSet(data, condition);
            self.addExpressionData(dataPlus, false);
            isLoadedPlus = true;
        } else {
            self.showMessage("Loading error");
        }
    })
    
    ajax.get({
        url: "expression?range=" + start + "_" + stop + "_0" + "&condition=" + condition.id + "&sampling=" + sampling,
        headers: {Accept: "text/csv"}
    }).done(function(status, data, error, xhr){
        if (status == 200) {
            var dataMinus = self.processExpressionDataSet(data, condition);
            self.addExpressionData(false, dataMinus);
            isLoadedMinus = true;
        } else {
            self.showMessage("Loading error");
        }
    });

    var interval = setInterval(function(){
        if (isLoadedMinus && isLoadedPlus) {
            clearInterval(interval);
            if (callback) callback.call(self, condition);
        }
    }, 50);


}

ContextExpressionBrowser.prototype.configDiagram = function () {
    this.__super.configDiagram.apply(this, arguments);
    this.diagram.on("click", function (ev) {
        if (ev.currentViews.length) {
            var top = ev.currentViews[0];
            if (top.type == "gene") {
                window.location = "expression?gene=" + top.id;
            }
        }
    })
}
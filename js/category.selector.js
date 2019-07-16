var CategorySelector = CategorySelector || function (domSelector) {
    this.domSelector = domSelector;
    this.view = $("<div></div>");
    this.subViews = [];
    this.root = {
        id: "SW",
        title: "SW",
        _children: [],
        _parent: null
    };
    this.hashTable = {};

    var self = this;

    this.loadData(function(){
        self.createSubView(self.root);
        $(domSelector).append(self.view);
    });
}

CategorySelector.prototype.loadData = function (callback) {
    var self = this;
    $.ajax({
        url: "category",
        dataType:"json",
        success: function (data) {
            data.forEach(function(category){
                self.hashTable[category.id] = category;
            });
            for(id in self.hashTable) {
                var category = self.hashTable[id];
                var parentId = CategorySelector.getParentId(id);
                if (parentId == "SW") {
                    self.root._children.push(category);
                } else {
                    var parent = self.hashTable[parentId];
                    if (!("_children" in parent)) {
                        parent._children = [];
                    }
                    parent._children.push(category);
                    category._parent = parent;
                }
            }
            callback();
        },
        error: function () {
            $(self.domSelector).html("Error loading the category selector");

        }
    })
}

CategorySelector.getParentId = function (id) {
    id = id.split(".");
    id.pop();
    return id.join(".");
}

CategorySelector.createSelectTag = function(name, data) {
    var select = $("<select></select>");
    select.prop("name", name);
    select.append("<option value='-1'>Please select</option>");
    data.forEach(function(each){
        var option = $("<option></option>");
        option.html(each.title);
        option.val(each.id);
        select.append(option);
    });
    return select;
}

CategorySelector.prototype.createSubView = function (parent) {
    var self = this;
    var data = parent._children;
    if (data) {
        var select = CategorySelector.createSelectTag("", data);
        var wrapper = $("<div></div>");
        wrapper.css({
            border: "solid 1px #aaa",
            display: "-moz-inline-stack",
            display: "inline-block",
            padding: "0",
            "border-radius": "4px",
            margin: "10px 10px 10px 0"
        });
        var cancelBtn = $("<div></div>");
        cancelBtn.css({
            padding: "0 15px",
            display: "-moz-inline-stack",
            display: "inline-block",
            cursor: "pointer"
        });
        cancelBtn.html("X");
        wrapper.append(select, cancelBtn);
        var index = self.subViews.length;
        self.subViews.push(wrapper);
        select.attr("index", index);
    
        self.view.append(wrapper);

        wrapper.select = select;
    
        // event listeners
        // on select change
        // 1. remove the select behinde the current select tag
        // 2. create a new select tag based on the user input
        select.on("change", function(){
            var index = Number(this.getAttribute("index"));
            for (var i = index + 1; i < self.subViews.length; i++) {
                self.subViews[i].remove();
            }
            self.subViews = self.subViews.slice(0, index + 1);
            if (this.value != "-1") {
                var selectedCategory = self.hashTable[this.value];
                self.createSubView(selectedCategory);
            }
        });
    
        cancelBtn.on("click", function(){
            select.val("-1");
            var index = Number(select.attr("index"));
            for (var i = index + 1; i < self.subViews.length; i++) {
                self.subViews[i].remove();
            }
            self.subViews = self.subViews.slice(0, index + 1);
        });
    }
}

CategorySelector.prototype.getValue = function (){
    var self = this;
    if (self.subViews.length) {
        var value = self.subViews[self.subViews.length - 1].select.val();
        var category = self.hashTable[value];
        if (!("_children" in category)) return category.id;
    }
    return null;
}

CategorySelector.prototype.getPresentation = function () {
    var self = this;
    var presentation = [];
    self.subViews.forEach(function(each){
        var title = each.select.find(":selected").text();
        presentation.push(title);
    });
    return presentation.join(" → ");
}

CategorySelector.prototype.reset = function () {
    var self = this;
    if (self.subViews.length) {
        for (var i = 1; i < self.subViews.length; i++) {
            self.subViews[i].remove();
        }
        self.subViews = self.subViews.slice(0, 1);
        self.subViews[0].select.val(-1);
    }
}
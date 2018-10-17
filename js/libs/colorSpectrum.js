var ColorSpectrum = ColorSpectrum || function (title, min, max, type) {
    this.title = title;
    this.min = min;
    this.max = max;
    this.type = type;
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
    var max, min;
    if (this.type == "log") {
        // log color
        max = Math.log(this.max);
        min = Math.log(this.min);
        value = Math.log(value);
    } else {
        max = this.max;
        min = this.min;
    }
    // linear color
    // green, yellow, red
    var start, end, alpha;
    var middle = (max + min) / 2;
    if (value < middle) {
        start = [0,255,0]; end = [255,255,0];
        alpha = (value - min) / (max - min) * 2;
    } else {
        start = [255,255,0]; end = [255,0,0];
        alpha = (value - middle) / (max - min) * 2;
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
    title.html(this.title + (this.type == "log" ? " (log scale)" : ""));
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
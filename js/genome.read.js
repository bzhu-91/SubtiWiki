function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
    return null;
}

$(document).on("submit", "#gene", function (ev) {
	ev.stopPropagation();
	ev.preventDefault();

	var geneName = this.geneName.value.trim();
	if (geneName.length > 1) {
		ajax.get({
			url: "gene?mode=title&keyword=" + encodeURIComponent(geneName),
			headers: {Accept: "application/json"}
		}).done(function(status, data, error, xhr) {
			if (error) {
				SomeLightBox.error("Connection to server lost.");
			} else if (status == 200) {
				if (data.length == 1) {
					window.location = $("base").attr("href") + "genome?gene=" + data[0].id
				} else {
					SomeLightBox.error("Gene " + geneName + " is ambigious");
				}
			} else {
				SomeLightBox.error("Gene " + geneName + " is not found");
			}
		})
	}

});

$(document).on("submit", "#flanking", function (ev) {
	ev.stopPropagation();
	ev.preventDefault();

	var geneName = this.geneName.value.trim();
	var up = this.up.value == "" ? 0 : this.up.value;
	var down = this.down.value == "" ? 0 : this.down.value;
	if (geneName.length > 1) {
		ajax.get({
			url: "gene?mode=title&keyword=" + encodeURIComponent(geneName),
			headers: {Accept: "application/json"}
		}).done(function(status, data, error, xhr) {
			if (error) {
				SomeLightBox.error("Connection to server lost.");
			} else if (status == 200) {
				if (data.length == 1) {
					window.location = $("base").attr("href") + "genome?gene=" + data[0].id + "&up=" + up + "&down=" + down;
				} else {
					SomeLightBox.error("Gene " + geneName + " is ambigious");
				}
			} else {
				SomeLightBox.error("Gene " + geneName + " is not found");
			}
		})
	}
});

$(document).on("change", "#dna-sequence-show-reverse", function(){
	browser.toogleRerverseComplement(this.checked);
});

$(document).on("submit", "#dna-sequence-search", function(ev){
	ev.stopPropagation();
	ev.preventDefault();

	var keyword = this.keyword.value.trim();
	if (keyword.match(/^[ATCG]{2,}$/i)) {
		browser.showSearch(keyword);
	} else {
		SomeLightBox.error("Invalid keyword");
	}

	return false;
});

$(document).on("click", "#dna-sequence-search-clear", function(ev){
	browser.restore();
});

$(document).ready(function(){
	var geneId = getQueryVariable("gene");
	var up = getQueryVariable("up");
	var down = getQueryVariable("down");

	if (!window.codonTable) {
		/*const*/  window.codonTable = {"T":{"T":{"T":"F","C":"F","A":"L","G":"L"},"C":{"T":"S","C":"S","A":"S","G":"S"},"A":{"T":"Y","C":"Y","A":"*","G":"*"},"G":{"T":"C","C":"C","A":"*","G":"W"}},"C":{"T":{"T":"L","C":"L","A":"L","G":"L"},"C":{"T":"P","C":"P","A":"P","G":"P"},"A":{"T":"H","C":"H","A":"Q","G":"Q"},"G":{"T":"R","C":"R","A":"R","G":"R"}},"A":{"T":{"T":"I","C":"I","A":"I","G":"M"},"C":{"T":"T","C":"T","A":"T","G":"T"},"A":{"T":"N","C":"N","A":"K","G":"K"},"G":{"T":"S","C":"S","A":"R","G":"R"}},"G":{"T":{"T":"V","C":"V","A":"V","G":"V"},"C":{"T":"A","C":"A","A":"A","G":"A"},"A":{"T":"D","C":"D","A":"E","G":"E"},"G":{"T":"G","C":"G","A":"G","G":"G"}}};
		/*const*/  window.startTable = {"T":{"T":{"T":"-","C":"-","A":"-","G":"M"},"C":{"T":"-","C":"-","A":"-","G":"-"},"A":{"T":"-","C":"-","A":"*","G":"*"},"G":{"T":"-","C":"-","A":"*","G":"-"}},"C":{"T":{"T":"-","C":"-","A":"-","G":"M"},"C":{"T":"-","C":"-","A":"-","G":"-"},"A":{"T":"-","C":"-","A":"-","G":"-"},"G":{"T":"-","C":"-","A":"-","G":"-"}},"A":{"T":{"T":"M","C":"M","A":"M","G":"M"},"C":{"T":"-","C":"-","A":"-","G":"-"},"A":{"T":"-","C":"-","A":"-","G":"-"},"G":{"T":"-","C":"-","A":"-","G":"-"}},"G":{"T":{"T":"-","C":"-","A":"-","G":"M"},"C":{"T":"-","C":"-","A":"-","G":"-"},"A":{"T":"-","C":"-","A":"-","G":"-"},"G":{"T":"-","C":"-","A":"-","G":"-"}}};
		alert("codon table not set in the Configuration file, using table 11 (general for bacteria) instead");
	}

	up = up == "" ? 0 : up;
	down = down == "" ? 0 : down;

	var start = getQueryVariable("start");
	var stop = getQueryVariable("stop");

	browser = new GenomeBrowser();
	
	if (geneId && up !== null && down !== null && up.match(/\d+/) && down.match(/\d+/)) {
		browser.loadByFlanking(geneId, parseInt(up), parseInt(down));
	} else if (geneId) {
		browser.loadByGene(geneId);
	} else if (start !== null && stop !== null && start.match(/\d+/) && stop.match(/\d+/) && start > 0 && stop > 0 && start < stop) {
		browser.loadByRegion(parseInt(start), parseInt(stop));
	} else if (window.location.search !== "") {
		SomeLightBox.error("Invalid parameters given");
	}
})

var Sequence = Sequence || function (string){
	var str = string;
	var spans = [];
	var ends = [];
	this.getString = function() {
		return str;
	}

	this.addSpan = function(s, e, style) {
		spans.push([s,style]);
		ends.push(e);
	}

	this.clear = function(){
		spans = [];
		ends = [];
	}
	this.toString = function(charCount, columnCount){
		spans.sort(function(a,b){
			return a[0] - b[0];
		});
		ends.sort(function(a,b){
			return a - b;
		});
		var tmps = spans.slice();
		var tmpe = ends.slice();
		var re = "";
		for(var i = 0; i < str.length; i++){
			if (tmps.length >= 1 && tmps[0][0] == i) {
				re += "<span style='" + tmps[0][1] + "'>";
				tmps.shift()
			}
			if (tmpe.length >= 1 && tmpe[0] == i) {
				re  += "</span>";
				tmpe.shift();
			}
			if( (i % charCount) == 0 && i != 0) {
	 			re += " ";
	 		}
	 		if (i % (10 * columnCount) == 0 && i != 0) {
	 			re += "\n";
	 		}
			re += str[i];
		}
		return re;
	}
}

var GenomeBrowser = GenomeBrowser || function () {
	this.contextData;
	this.contextBrowser;
	this.plainDNASequence;
	this.plainProteinSequence;
	this.DNASequence;
	this.proteinSequence;
	this.showingReverse = false;
	this.keyword;

	self.mode;
	this.geneId;
	this.gene;
	this.start;
	this.stop;
	this.up;
	this.down;
}

GenomeBrowser.prototype.loadByGene = function (geneId) {
	var self = this;
	self.geneId = geneId;
	self.mode = "gene";
	self.loadContextData(function(){
		self.createContextBrowser();
		self.loadSequenceDataByGene(function() {
			self.showDNASequence();
			self.showProteinSequence();
		});
	});
}

GenomeBrowser.prototype.loadByFlanking = function (geneId, up, down) {
	var self = this;
	self.geneId = geneId;
	self.up = up;
	self.down = down;
	self.mode = "flanking";
	self.loadContextData(function(){
		self.createContextBrowser();
		self.loadSequenceDataByGene(function() {
			self.showDNASequence();
			self.showProteinSequence();
		});
	});
}

GenomeBrowser.prototype.loadByRegion = function (start, stop) {
	var self = this;
	self.start = start;
	self.stop = stop;
	self.mode = "region";
	self.loadContextData(function() {
		self.createContextBrowser();
		self.loadSequenceData(start, stop, 1, function() {
			self.showDNASequence();
		})
	})
}

GenomeBrowser.prototype.loadContextData = function (callback) {
	var self = this;

	var onDone = function (status, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if(status == 200) {
			self.contextData = data;
			switch (self.mode) {
				case "gene":
				case "flanking":
					data.forEach(function(each){
						if (each.id == self.geneId) {
							self.gene = each;
						}
					})
			}
			$("#data").show();
			callback();
		} else {
			SomeLightBox.error(data.message);
		}
	}

	var span = 30000;

	switch (self.mode) {
		case "flanking":
			span = Math.max(span, Math.max(self.up, self.down));
		case "gene":
			ajax.get({
				url: "genome/context?gene=" + self.geneId + "&span=" + span,
				headers: {Accept: "application/json"}
			}).done(onDone);
			break;
		case "region":
			var mid = Math.ceil((self.start + self.stop) / 2);
			var halfLen = Math.ceil((self.stop - self.start) / 2);
			span = Math.max(span, halfLen);
			ajax.get({
				url: "genome/context?position=" + mid + "&span=" + span,
				headers: {Accept: "application/json"}
			}).done(onDone);
	}
}

GenomeBrowser.prototype.createContextBrowser = function () {
	var self = this;
	self.contextBrowser = new ContextBrowser($("#context-browser"));
	self.contextBrowser.setData(self.contextData, genomeLength);
	switch (self.mode) {
		case "gene":
		case "flanking":
			self.contextBrowser.diagram.focus(self.geneId);
			break;
		case "region":
			self.contextBrowser.diagram.moveTo(self.start, "middle");
			break;
	}

	self.contextBrowser.diagram.on("click", function(ev){
		if (ev.currentViews) {
			var view = ev.currentViews[0];
			if (view.id && view.type == "gene") {
				self.gene = view;
				self.clearDNASequence();
				self.clearProteinSequence();
				self.loadSequenceDataByGene(function(){
					self.showDNASequence(ev.currentGene);
					self.showProteinSequence(ev.currentGene);
				});
			}
		}
	});
}

GenomeBrowser.prototype.loadSequenceDataByGene = function (callback) {
	var l,r,s; var self = this;
	switch (self.mode) {
		case "gene":
		case "region":
			l = self.gene.start;
			r = self.gene.stop;
			s = self.gene.strand;
			break;
		case "flanking":
			l = self.gene.start - self.up;
			if (l < 1) {
				l += genomeLength;
			}
			r = self.gene.stop + self.down;
			if (r > genomeLength) {
				r -= genomeLength;
			}
			s = self.gene.strand;
			break;
	}
	self.loadSequenceData(l,r,s,callback);
}

GenomeBrowser.prototype.loadSequenceData = function (l, r, s, callback){
	var self = this;
	self.contextBrowser.diagram.selectRange(l,r);
	ajax.get({
		url: "genome/sequence?position=" + l + "_" + r + "_" + s,
		headers: {Accept: "application/json"}
	}).done(function(status, data, error, xhr){
		if (error) {
			SomeLightBox.error("Connection to server lost");
		} else if (status == 200) {
			self.plainDNASequence = data.sequence;
			callback();
		}
	});
}

GenomeBrowser.prototype.translateDNA = function () {
	var self = this;
	if (self.plainDNASequence.length % 3 != 0) {
		self.plainProteinSequence = null;
		return;
	}
	var aaSeq = "";
	var a = self.plainDNASequence[0];
	var b = self.plainDNASequence[1];
	var c = self.plainDNASequence[2];
	var S = startTable[a][b][c];
	if (S == '-' || S == '*') {
		self.plainProteinSequence = null;
		return;
	} else {
		aaSeq += S;
	}

	for (var i = 3; i + 2 < self.plainDNASequence.length; i+=3) {
		a = self.plainDNASequence[i];
		b = self.plainDNASequence[i + 1];
		c = self.plainDNASequence[i + 2];
		var P = codonTable[a][b][c];
		if (P == '*') {
			break;
		}
		aaSeq += P
	}

	// premature termination, should not happen if gene's sequence is given
	if (aaSeq.length == self.plainDNASequence.length / 3 - 1) {
		self.plainProteinSequence = aaSeq;
	}
}

GenomeBrowser.prototype.reverseComplementDNA = function () {
	var self = this;
	var re = "";
	var dic = {A:"T", T:"A", C:"G", G:"C"};
 	for(var i = self.plainDNASequence.length - 1; i >= 0; i--){
 		if (dic[self.plainDNASequence[i]]) {
	 		re += dic[self.plainDNASequence[i]];
 		} else {
 			re += self.plainDNASequence[i];
 		}
 	}
 	self.plainDNASequence = re;
 	self.showingReverse = !self.showingReverse;
}

GenomeBrowser.prototype.showDNASequence = function (gene) {
	var self = this;
	self.DNASequence = new Sequence(self.plainDNASequence);

	if (self.showingReverse) {
		$("#dna-sequence-reversed").css("display", "inline");
	} else {
		$("#dna-sequence-reversed").hide();
	}

	var label = "", header = "", strand = "";
	switch (self.mode) {
		case "region":
		 	if (!self.gene) {
		 		label = self.start + ".." + self.stop;
		 		if (self.showingReverse) {
		 			strand = "+";
		 		} else {
		 			strand = "-";
		 		}
		 		header = "> " + window.organismName + " " + window.strainName + " | " + label + " | " + strand + " strand";
		 		break;
		 	}
		case "gene":
			label = self.gene.title;
			header = "> " + window.organismName + " " + window.strainName + " | " + self.gene.locus + " | " + self.gene.title;
			if (self.showingReverse) {
				header += " (reverse complement)";
			}
			if (self.showingReverse) {
				strand = self.gene.strand == 0 ? '+' : '-';
			} else {
				strand = self.gene.strand == 1 ? '+' : '-';
			}
			break;
		case "flanking":
			if(!self.showingReverse){
				self.DNASequence.addSpan(self.up, self.up + (self.gene.stop - self.gene.start), "background:blue;color:white");
			}
			label = self.gene.title + " with flanking";
			header = "> " + window.organismName + " " + window.strainName + " | " + self.gene.locus + " | " + self.gene.title + " | flanking: " + self.up + "bp upstream, " + self.down + "bp downstream";
			if (self.showingReverse) {
				header += " | reverse complement";
			}
			if (self.showingReverse) {
				strand = self.gene.strand == 0 ? '+' : '-';
			} else {
				strand = self.gene.strand == 1 ? '+' : '-';
			}
			break;

	}
	var colNum = Math.ceil($("#dna-sequence").width() / 100);
	$("#dna-sequence").html(self.DNASequence.toString(10, colNum));
	$("#dna-sequence-label").html(label);
	$("#dna-sequence-header").html(header);
	$("#dna-sequence-strand").html(strand);
}

GenomeBrowser.prototype.clearDNASequence = function () {
	var self = this;
	self.plainDNASequence = null;
	self.DNASequence = null;
	self.plainProteinSequence = null;
	self.proteinSequence = null;
	$("#dna-sequence").html("");
	$("#dna-sequence-header").html("");
}

GenomeBrowser.prototype.showProteinSequence = function () {
	var self = this;
	switch (self.mode) {
		case "region":
			if (!self.gene) {
				break;
			}
		case "gene":
			self.translateDNA();
			if (self.plainProteinSequence) {
				self.proteinSequence = new Sequence(self.plainProteinSequence);
				$("#protein-sequence-container").show();
				$("#protein-sequence-header").html("> " + window.organismName + " " + window.strainName + " | " + self.gene.locus + " | " + self.gene.title);
				var colNum = Math.ceil($("#protein-sequence").width() / 100);
				$("#protein-sequence").html(self.proteinSequence.toString(10, colNum));
			}
			break;
	}
}

GenomeBrowser.prototype.clearProteinSequence = function () {
	var self = this;
	self.plainDNASequence = null;
	self.DNASequence = null;
	self.plainProteinSequence = null;
	self.proteinSequence = null;
	$("#protein-sequence").html("");
	$("#protein-sequence-header").html("");
}

GenomeBrowser.prototype.toogleRerverseComplement = function (showingReverse) {
	var self = this;
	if (self.showingReverse != showingReverse) {
		self.reverseComplementDNA();
		self.showDNASequence();
	}
}

GenomeBrowser.prototype.showSearch = function(keyword) {
	var self = this;
	var regexp = new RegExp(keyword, "gi");
	var match = {}
	var c = 0;
	var sequence = new Sequence(self.plainDNASequence);
	while((match = regexp.exec(self.plainDNASequence)) != null) {
		sequence.addSpan(match.index, match.index + keyword.length, "color: black; background: #ffff00");
		c++;
	}

	if (c == 0) {
		SomeLightBox.error("Not found");
	} else {
		var colNum = Math.ceil($("#dna-sequence").width() / 100);
		$("#dna-sequence").html(sequence.toString(10, colNum));
	}
}

GenomeBrowser.prototype.restore = function () {
	var self = this;
	var colNum = Math.ceil($("#dna-sequence").width() / 100);
	$("#dna-sequence").html(self.DNASequence.toString(10, colNum));
}
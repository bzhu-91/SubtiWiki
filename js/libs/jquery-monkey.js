(function($){
	var monkey = monkey || {
		clear: function(src){
	
		},
		isEmpty: function(src){
			var lines = src.split('\n');
			var isEmpty = true
			lines.forEach(function(el){
				el = el.trim("' ")
				if (!el.startsWith("*")) isEmpty = false 
			});
			return isEmpty;
		},	
	
		decode: function (src) {
			var parseLine = function (line){
				line = line.trim();
				if (line[0] == "*" && line.indexOf("* ") >=0 ) {
					var depth = line.indexOf("* ") + 1;
					if(line.indexOf(": ") > 0 && line.indexOf(": ") != line.length - 2) {
						var segs = line.split(": ");
						var k = segs.shift();
						var v = segs.join(": ");
						if (!isNaN(Number(v))) {
							v = Number(v);
						} else if (v.toLowerCase() == "true") {
							v = true;
						} else if(v.toLowerCase() == "false") {
							v = false;
						}
						return [0, k.substr(depth + 1), v, depth];
					} else {
						return [1,line.substr(depth+1), depth];
					}
				} else if (line.length) {
					if (!isNaN(Number(line))) {
							line = Number(line);
						} else if (line.toLowerCase() == "true") {
							line = true;
						} else if(line.toLowerCase() == "false") {
							line = false;
						}
					return [2, line];
				};
				return -1;
			};
	
			var initObj = function (curState){
				var tmpcur = curState.obj;
				for(var i = 0; i < curState.keyChain.length; ++i){
					if (tmpcur[curState.keyChain[i]]) {
						tmpcur = tmpcur[curState.keyChain[i]];
					} else {
						tmpcur[curState.keyChain[i]] = {};
						tmpcur = tmpcur[curState.keyChain[i]];
					}
				}
				return tmpcur;
			};
	
			var initArray = function (curState){
				var tmpcur = curState.obj;
				for(var i = 0; i < curState.keyChain.length; ++i){
					if (tmpcur[curState.keyChain[i]]) {
						tmpcur = tmpcur[curState.keyChain[i]];
					} else {
						if (i == curState.keyChain.length - 1) {
							tmpcur[curState.keyChain[i]] = [];
						} else {
							tmpcur[curState.keyChain[i]] = {};
						}
						tmpcur = tmpcur[curState.keyChain[i]];
					}
				}
				return tmpcur;
			};
	
			var popKeychain = function (curState, depth){
				var popl = curState.keyChain.length - depth;
				if (popl >= 0) {
					curState.keyChain.splice(depth - 1, popl + 1);
					curState.cur = curState.obj;
					for(var i = 0; i < curState.keyChain.length; ++i){
						curState.cur = curState.cur[curState.keyChain[i]];
					}
					return true;
				} else if (popl == -1) {
					return true;
				} else {
					return false;
				}
			};
	
			var getStatOnInput = function (curState, line, lineNubmer){
				var parsed = parseLine(line);
				var ev = parsed[0];
				switch(ev) {
					case 0:
						var key = parsed[1];
						var value = parsed[2];
						if(!popKeychain(curState, parsed[3])){
							throw {message: "structural error", line: lineNubmer, text: src};
						};
						var tmpcur = initObj(curState);
						if (tmpcur instanceof Array) throw {message: "structural error", line: lineNubmer, text: src};
						tmpcur[key] = value;
						break;
					case 1:
						var key = parsed[1];
						if(!popKeychain(curState, parsed[2])){
							return false;
						};
						curState.keyChain.push(key);
						break;
					case 2:
						var value = parsed[1];
						var tmpcur = initArray(curState);
						if (tmpcur instanceof Array) tmpcur.push(value);
						else throw {message: "structural error", line: lineNubmer, text: src};
						break;
				}
				return true;
			};
	
			var curState = {};
			curState.obj = {};
			curState.keyChain = [];
			var idx = idx2 = src.indexOf("<html>");
			while(idx >= 0){
				var idx2 = src.indexOf("</html>", idx + 1)
				if (idx2 == -1) {
					throw new Error("html tag not correctly closed");
				} else {
					src = src.substr(0,idx) + src.substring(idx, idx2 + 7).replace(/\n/g,"::newline::") + src.substring(idx2 + 7)
				}
				idx = src.indexOf("<html>", idx2 + 1);
			}
			var list = src.split("\n");
			for (var i = 0; i < list.length; i++) {
				if(!getStatOnInput(curState, list[i], i)) {
					throw new Error(i);
				};
			};
			var ostr = JSON.stringify(curState.obj)
			
			ostr = ostr.replace(/\:\:newline\:\:/g,"\\n")
			if (ostr == "{}") return null;
			var o = JSON.parse(ostr);
			return o;
		},
	
		encode: function(obj){
			var indent_global = 0;
			var inline_flag = false;
			var str = "";
			var print_function = function (value) {
				if (value instanceof Function) return;
				if (value instanceof Array) {
					indent_global++;
					for (var i = 0; i < value.length; i++) {
						print_function(value[i]);
						inline_flag = false;
					};
					indent_global--;
				} else if (value instanceof Object) {
					indent_global++;
					for(var k in value){
						if (k instanceof Function) continue;
						if (!value.hasOwnProperty(k)) continue;
						if (value[k] instanceof Array || value[k] instanceof Object) {
							inline_flag = false;
							str += Array(indent_global + 1).join("*") + " " + k + "\n";
						} else {
							inline_flag = true;
							str +=  Array(indent_global + 1).join("*") + " " + k;
						}
						print_function(value[k]);
					}
					indent_global--;
				} else {
					if (inline_flag) {
						str += ": " + value + "\n";
					} else {
						str += value + "\n";
					}
				}
			}
	
			print_function(obj);
			return str;
		},
		
		handleReferences: function(match, src, offset, string) {
			
			return "<div id=\"ref_src\"></div>"
		},
		
		parse: function (txt) {
			var rules = {
				italic: {
					regex:/''([\u00C0-\u017F\w\s\[\]\|\-\_]{1,})''/g,
					replace: "<i>$1</i>"
				},
				SW: {
					regex:/\[SW\|([\u00C0-\u017F\w\s\d-\_]+)\]/g,
					replace:"<a target=\"_blank\" href=\"http://subtiwiki.uni-goettingen.de/wiki/index.php/$1\">$1</a>"
				},
				SW1 :{
					regex:/\[SW\|([\u00C0-\u017F\w \-\_]+?)\|([\u00C0-\u017F\w\s\-\_]+?)\]/g,
					replace:"<a target=\"_blank\" href=\"http://subtiwiki.uni-goettingen.de/wiki/index.php/$1\">$2</a>"
				},
				Gene: {
					regex:/\[Gene\|([\u00C0-\u017F\w\d\-\/]+?)\]/g,
					replace:"<a target=\"_blank\" href=\"http://subtiwiki.uni-goettingen.de/bank/index.php?gene=$1\">$1</a>"
				},
				Gene1: {
					regex:/\[Gene\|([\u00C0-\u017F\w\d\-\/]+?)\|([\u00C0-\u017F\w\s\-\_]+?)\]/g,
					replace:"<a target=\"_blank\" href=\"http://subtiwiki.uni-goettingen.de/bank/index.php?gene=$1\">$2</a>"
				},
				gene: {
					regex:/\[gene\|([\u00C0-\u017F\w\d-\/]+?)\]/g,
					replace:"<a target=\"_blank\" href=\"http://subtiwiki.uni-goettingen.de/bank/index.php?gene=$1\">$1</a>"
				},
				gene1: {
					regex:/\[gene\|([\u00C0-\u017F\w\d-\/]+?)\|([\u00C0-\u017F\w\s\-\_]+?)\]/g,
					replace:"<a target=\"_blank\" href=\"http://subtiwiki.uni-goettingen.de/bank/index.php?gene=$1\">$2</a>"
				},
				pubmed: {
					regex:/\[PubMed\|([\d,]{4,}?)\]/gi,
					replace:"<a target=\"_blank\" href=\"http://www.ncbi.nlm.nih.gov/pubmed/$1\"><span class=\"default_pubmed\">PubMed</span></a>"
				},
				PDB: {
					regex:/\[pdb\|([a-z0-9,]{4,}?)\]/gi,
					replace:"<a target=\"_blank\" href=\"http://www.rcsb.org/pdb/explore.do?structureId=$1\"><span class=\"default_pdb\">PDB</span></a>"
				},
				PDB1: {
					regex:/\[pdb\|([a-z0-9,]{4,}?)\|([\u00C0-\u017F\w\s-\_]+?)\]/gi,
					replace:"<a target=\"_blank\" href=\"http://www.rcsb.org/pdb/explore.do?structureId=$1\"><span class=\"default_pubmed\">$2</a>"
				},
				url: {
					regex:/\[(https?:[a-z0-9\?=\+\/%\.\-]+?) ([\u00C0-\u017F\w\s\-\_]+?)\]/gi,
					replace:"<a target=\"_blank\" href=\"$1\">$2</a>"
				},
				refs: {
					regex:/<pubmed>(.+?)<\/pubmed>/gi,
					replace: monkey.handleReferences
				}
	
			}
			for(var i in rules){
				var e = rules[i];
				txt = txt.replace(e.regex, e.replace);
			}
			return txt;
		},
		
		print: function(obj) {
			var indent_unit = 20;
	
			var p_key_default = function(k, indent) {
				return "<p style=\"margin-left:" + indent_unit * indent + "px\" class=\"default_key_" + indent + "\">" + k + "</p>";
			}
	
			var p_key_inline = function(k, indent) {
				return "<p style=\"margin-left:" + indent_unit * indent + "px\"><span class=\"default_key_" + indent + "\">" + k + ": </span>";
			}
	
			var p_value_default = function(v, indent) {
				return "<p style=\"margin-left:" + indent_unit * indent + "px\" class=\"default_value\">" + v + "</p>";
			}
	
			var p_value_inline = function(v) {
				return "<span class=\"default_value_inline\">" + v + "</span></p>";
			}
	
			var decode_key = function (k) {
				return k.replace(/\_/g, " ");
			}
	
			var indent_global = -1;
			var inline_flag = false;
			var str = "";
	
			var print_function = function (value) {
				if (value instanceof Function) return;
				if (value instanceof Array) {
					indent_global++;
					for (var i = 0; i < value.length; i++) {
						print_function(value[i]);
						inline_flag = false;
					};
					indent_global--;
				} else if (value instanceof Object) {
					indent_global++;
					for(var k in value){
						if (k instanceof Function) continue;
						if (!value.hasOwnProperty(k)) continue;
						if (value[k] instanceof Array || value[k] instanceof Object) {
							inline_flag = false;
							str += p_key_default(decode_key(k), indent_global);
						} else {
							inline_flag = true;
							str += p_key_inline(decode_key(k), indent_global)
						}
						print_function(value[k]);
					}
					indent_global--;
				} else {
					if (inline_flag) {
						str += p_value_inline(value);
					} else {
						str += p_value_default(value, indent_global);
					}
				}
			}
	
			print_function(obj);
			str = monkey.parse(str);
			return str;
		}
	}

	// first line
	buttons = [
		[
			{label: "Gene", type: 2, target: "gene"},
			{label: "Protein", type: 2, target: "protein"},
			{label: "Wiki", type: 2, target: "wiki"},
			{label: "PDB", type: 1, target: "PDB"},
			{label: "Pubmed(inline)", type: 1, target: "pubmed"},
			{label: "Regulon", type: 2, target: "regulon"},
			{label: "Category", type: 2, target: "category"},
			{label: "External URL", type: "text", target: "[external_url text_to_be_shown]"},
		],[
			{label: "Add *", type: "*"},
			{label: "Pubmed(block)", type: "html", target: "pubmed"},
			{label: "<i>I</i>", type: "html", target: "i"},
			{label: "<b>B</b>", type: "html", target: "b"},
			{label: "X<sub>2</sub>", type: "html", target: "sub"},
			{label: "X<sup>2</sup>", type: "html", target: "sup"},
			{label: "<u>U</u>", type: "html", target: "u"},
		]

	];

	var uuidv4 = function() {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
			return v.toString(16);
		});
	}

	var uuid = uuidv4();
	var styleRules = {};

	styleTag = $("<style></style>").prop("id", uuid);
	$("head").append(styleTag);

	var addCSSRule = function (selector, rule) {
		styleRules[selector] = rule;
		var cssString = getCSSString(styleRules);
		styleTag.html(cssString);
	}

	var removeCSSRule = function (selector) {
		delete styleRules[selector];
		var cssString = getCSSString(styleRules);
		styleTag.html(cssString);
	}

	var getCSSString = function (defs) {
		var cssString = "";
		for(var selector in defs) {
			var rule = defs[selector];
			selector = "[uuid='" + uuid + "'] " + selector; // add uuid prefix
			if (typeof rule == "string" || rule instanceof String) {
				cssString += selector + "{" + rule + "}"
			} else if (typeof rule == "object" || rule instanceof Object) {
				// if objects
				var dummy = $("<div></div>").css(defs[selector]);
				cssString += selector + "{" + dummy.prop("style").cssText + "}"
			}
			cssString += "\n";
		}
		return cssString;
	}

	addCSSRule(".button", "background: #1976d2; padding: 5px; margin:0 5px 5px 0;display:inline-block;border-radius:3px;color:white; font-family: sans-serif; cursor:pointer;font-size:10pt;vertical-align:middle");
	addCSSRule(".button:hover", "background: #1e88e5")

	var getSelectedText = function (textarea) {
		return textarea.value.substr(textarea.selectionStart, textarea.selectionEnd - textarea.selectionStart);
	}

	var setSelection = function (textarea,start, end) {
		textarea.selectionStart = start;
		if (arguments.length == 3) {
			textarea.selectionEnd = end;
		} else {
			textarea.selectionEnd = start;
		}
	}

	var setContent = function (textarea, content) {
		textarea.value = content;
	}

	var insertText = function (textarea, text, cursor_offset, start, end) {
		var isFirefox = typeof InstallTrigger !== 'undefined';
		var content = textarea.value;
		start = (start == undefined ? textarea.selectionStart : start) || 0;
		end = (end == undefined ? textarea.selectionEnd : end) || 0;
		textarea.focus();
		setSelection(textarea, start,end);
		if (start == end) {
			if (document.queryCommandSupported('insertText') && !isFirefox) {
			    document.execCommand('insertText', false, text);
			} else {
				setContent(textarea, content.substr(0,start) + text + content.substr(end));
			}
			if (cursor_offset != -1) setSelection(textarea, start+cursor_offset);
		} else {
			if (document.queryCommandSupported('delete') && document.queryCommandSupported('insertText') && !isFirefox) {
				document.execCommand('delete');
			  	document.execCommand('insertText', false, text);
			} else {
				setContent(textarea, content.substr(0,start) + text + content.substr(end));
			}
			if (cursor_offset != -1) setSelection(textarea, start+cursor_offset);
		}
	}

	var btnOnClick = function (data, textarea) {
		var selected = getSelectedText(textarea);
		var insertion = "";
		var offset = 0;
		switch (data.type) {
			case "html":
			insertion = "<" + data.target + ">" + selected + "</" + data.target + ">";
			offset = selected.length ? insertion.length : data.target.length + 2;
			break;
			case 1:
			insertion = "[" + data.target + "|" + selected + "]";
			offset = selected.length ? insertion.length : data.target.length + 2;
			break;
			case 2:
			insertion = "[[" + data.target + "|" + selected + "]]";
			offset = selected.length ? insertion.length : data.target.length + 3;
			break;
			case "text":
			insertion = data.target;
			offset = 0;
			break;
			case "*":
			if (selected.length == 0) {
				var content = textarea.value;
				var s = textarea.selectionStart || 0;
				var e = textarea.selectionEnd || 0;
				var c = s - 1;
				while(content[c] != "\n" && c >= 0){
					c--;
				}
				if (content[c+1] == "*") {
					insertText(textarea, "*", s+1, c+1,c+1);
				} else {
					insertText(textarea, "* ", s+2, c+1,c+1);
				}
			}
			return;
		}
		insertText(textarea, insertion, offset);
	}

	var rewrite = function (textarea) {
		var content = textarea.value.trim();
		var empty = [
			"insert text here",
			"<pubmed></pubmed>",
			"\\[\\[gene\\|\\]\\]",
			"\\[\\[protein\\|\\]\\]",
			"\\[SW\\|\\]",
			"\\[PDB\\|\\]",
			"\\[\\]",
			"\\[external_url text_to_be_shown\\]"
		];
		if (content.length) {
			empty.forEach(function(s){
				content = content.replace(new RegExp(s, "gi"), "");
			});
			try {
				var obj = monkey.decode(content);
				var content = JSON.stringify(obj);
				return content;
			} catch (e) {
				return null;
			}
		} else return null;
	}

	var init = function (textarea) {
		if (textarea.tagName.toLowerCase() !== "textarea") {
			throw new Error("Can only apply to textarea tag")
		}
		var $wrapper = $("<div></div>").attr("uuid", uuid).addClass("wrapper")
		var $toolbar = $("<div></div>");
		var $hiddenInput = $("<input></input>").attr("type", "hidden").attr("name", $(textarea).attr("name"));
		$(textarea).after($wrapper);
		$(textarea).remove().addClass("textarea").removeAttr("name");
		$wrapper.append($toolbar, textarea, $hiddenInput)

		// append buttons
		buttons.forEach(function(btns){
			var $line = $("<div style='margin-bottom: 5px'></div>");
			$toolbar.append($line);
			btns.forEach(function(each){
				var $btn = $("<a class='button'></a>")
					.html(each.label)
					.prop("data", each)
					 .on("click", function() {
						btnOnClick(this.data, textarea);
					})				
				$line.append($btn);
			});
		});

		$(textarea).on("change", function(){
			$hiddenInput.val(this.value);
		});

		$($hiddenInput).val(textarea.value);

		$(textarea).on("blur", function(){
			if ($(textarea).attr("type") == "monkey") {
				var content = rewrite(this);
				if (content) {
					$hiddenInput.val(rewrite(this));
				} else {
					$(textarea).css({
						border: "1px solid red"
					});
					$hiddenInput.val("").attr("error", "decode");
				}
			}
		});

		$(textarea).parents("form").on("submit", function(ev){
			ev.preventDefault();
			if ($hiddenInput.attr("error")) {
				ev.stopPropagation();
				return false;
			}
		});

		textarea.monkey = 1;
	}

	$.fn.monkey = function(){
		if (this.length) this.each(function(){
			if (!this.monkey) {
				init(this);
			}
		});
		return this;
	}
})(jQuery);
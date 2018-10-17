(function(){

	function isElement(obj) {
	  try {
	    //Using W3 DOM2 (works for FF, Opera and Chrome)
	    return obj instanceof HTMLElement;
	  }
	  catch(e){
	    //Browsers not supporting W3 DOM2 don't have HTMLElement and
	    //an exception is thrown and we end up here. Testing some
	    //properties that all elements have (works on IE7)
	    return (typeof obj==="object") &&
	      (obj.nodeType===1) && (typeof obj.style === "object") &&
	      (typeof obj.ownerDocument ==="object");
	  }
	}

	var buttons = [];

	// first line
	buttons[0] = [
		{label: "Gene", type: 2, target: "gene"},
		{label: "Protein", type: 2, target: "protein"},
		{label: "Wiki", type: 2, target: "wiki"},
		{label: "PDB", type: 1, target: "PDB"},
		{label: "Pubmed(inline)", type: 1, target: "pubmed"},
		{label: "Regulon", type: 2, target: "regulon"},
		{label: "Category", type: 2, target: "category"},
		{label: "External URL", type: "text", target: "[external_url text_to_be_shown]"},
	];

	buttons[1] = [
		{label: "Add *", type: "*"},
		{label: "Pubmed(block)", type: "html", target: "pubmed"},
		{label: "<i>I</i>", type: "html", target: "i"},
		{label: "<b>B</b>", type: "html", target: "b"},
		{label: "X<sub>2</sub>", type: "html", target: "sub"},
		{label: "X<sup>2</sup>", type: "html", target: "sup"},
		{label: "<u>U</u>", type: "html", target: "u"},
	];

	// toolbox
	window.Editor = window.Editor || function (container, name, type, replace) {
		this.editor;
		this.name = name;
		this.type = type ? type : "normal";
		this.view;
		this.createView();
		if (replace) this.view.insertAfter(replace);
		else $(container).append(this.view);
		this.highjack();
	};

	window.Editor.init = function (selector) {
		var container = $(selector);
		if (container.attr("adapted")) {
			return;
		} else {
			container.find("textarea").each(function(idx, each){
				var type = each.getAttribute("type");
				var name = each.getAttribute("name");
				var content = each.value;
				var editor = new window.Editor(null, name, type, each);
				editor.setContent(content);
				each.remove();
			});
			container.attr("adapted", "true");
		}
	}

	window.Editor.prototype.highjack = function () {
		var forms = this.view.parents("form");
		var self = this;
		if (forms.length) {
			var form = forms[0];
			if (form.serialize) {
				var func = form.serialize;
				form.serialize = function () {
					var query = func.call(this);
					var name = self.name;
					var value = self.type == "monkey" ? self.editor.rewrite() : self.editor.getContent();
					query += "&" + encodeURIComponent(name)+"="+encodeURIComponent(value);
				}
			} else {
				form.serialize = function () {
					var query = [];
					for (var i in this) {
						if (this.hasOwnProperty(i) && "serialize" != i) {
							var el = this[i];
							if (el.getAttribute) {
								var name = el.getAttribute("name");
								if (name) {
									query.push(encodeURIComponent(name)+"="+encodeURIComponent(el.value));
								}
							}
						}
					}
					var name = self.name;
					var value = self.type == "monkey" ? self.editor.rewrite() : self.editor.getContent();
					query.push(encodeURIComponent(name)+"="+encodeURIComponent(value));
					return query.join("&");		
				}
			}
		}
	}

	window.Editor.prototype.createView = function(selector) {
		var self = this;
		// create toolbar and textarea box
		this.view = $("<div><label style='width:5%;vertical-align:top;max-width: 100px; min-width:50px'>Toolbox</label><div style='display:inline-block; width:90%; padding:0; vertical-align:top' id='toolbar'></div></div>");
		buttons.forEach(function(btns, i){
			var line = $("<div style='margin-bottom: 5px'></div>");
			self.view.find("#toolbar").append(line);
			btns.forEach(function(each, j){
				var btn = $("<a class='button' style='vertical-align: top; height: 1.8em'></a>");
				btn.html(each.label);
				btn[0].data = each;
				btn.on("click", function() {
					self.handle(this.data);
				})				
				line.append(btn);
			});
		});
		this.editor = new TEXTAREA(this.type);
		this.editor.appendTo(this.view);
	};

	window.Editor.prototype.handle = function (data) {
		var selected = this.editor.getSelectedText();
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
				var content = this.editor.getContent();
				var s = this.editor.textarea.selectionStart || 0;
				var e = this.editor.textarea.selectionEnd || 0;
				var c = s - 1;
				while(content[c] != "\n" && c >= 0){
					c--;
				}
				if (content[c+1] == "*") {
					this.editor.insertText("*", s+1, c+1,c+1);
				} else {
					this.editor.insertText("* ", s+2, c+1,c+1);
				}
			}
			return;
		}
		this.editor.insertText(insertion, offset);
	}

	window.Editor.prototype.setContent = function (content) {
		this.editor.setContent(content);
	}

	window.Editor.prototype.getContent = function () {
		return this.editor.getContent();
	}

	// wrapper class
	var TEXTAREA = function (type) {
		this.wrapper = document.createElement("div");
		this.wrapper.style = "border: 1px solid #aaa; border-radius: 4px;padding: 10px; margin-bottom: 10px; background: white";
		this.textarea = document.createElement("textarea");
		this.wrapper.appendChild(this.textarea);
		this.textarea.style = "width: 100%; min-height: 100px; border:none; outline: none";
		this.textarea.contentedtitable = true;
	}

	TEXTAREA.prototype.getSelectedText = function () {
		return this.getContent().substr(this.textarea.selectionStart, this.textarea.selectionEnd - this.textarea.selectionStart);
	}

	TEXTAREA.prototype.getContent = function () {
		return this.textarea.value;
	}

	TEXTAREA.prototype.setContent = function (content) {
		this.textarea.value = content;
	}

	TEXTAREA.prototype.setSelection = function (start, end) {
		this.textarea.selectionStart = start;
		if (arguments.length == 2) {
			this.textarea.selectionEnd = end;
		} else {
			this.textarea.selectionEnd = start;
		}
	}

	// throws
	TEXTAREA.prototype.rewrite = function () {
		var content = this.getContent().trim();
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
			var obj = monkey.decode(content);
			var content = JSON.stringify(obj);
			return content;
		} else return null;
	}

	TEXTAREA.prototype.appendTo = function(parent) {
		if (parent.append) {
			parent.append(this.wrapper);
		} else {
			parent.appendChild(this.wrapper);
		}
	}

	TEXTAREA.prototype.insertText = function (text, cursor_offset, start, end) {
		var isFirefox = typeof InstallTrigger !== 'undefined';
		var content = this.getContent();
		start = (start == undefined ? this.textarea.selectionStart : start) || 0;
		end = (end == undefined ? this.textarea.selectionEnd : end) || 0;
		this.textarea.focus();
		this.setSelection(start,end);
		if (start == end) {
			if (document.queryCommandSupported('insertText') && !isFirefox) {
			    document.execCommand('insertText', false, text);
			} else {
				this.setContent(content.substr(0,start) + text + content.substr(end));
			}
			if (cursor_offset != -1) this.setSelection(start+cursor_offset);
		} else {
			if (document.queryCommandSupported('delete') && document.queryCommandSupported('insertText') && !isFirefox) {
				document.execCommand('delete');
			  document.execCommand('insertText', false, text);
			} else {
				this.setContent(content.substr(0,start) + text + content.substr(end));
			}
			if (cursor_offset != -1) this.setSelection(start+cursor_offset);
		}
	}
})();
(function(){
	var Wrapper = function (parameters) {
		this.xhr = new XMLHttpRequest();
		this.doneCallback = function() {};
		this.headers = parameters.headers;
		this.parameters = parameters;

		var self = this;
		this.xhr.onreadystatechange = function () {
			if (self.xhr.readyState == XMLHttpRequest.DONE) {
				var data = self.xhr.responseText;
				var contentType = self.xhr.getResponseHeader("Content-Type");
				if (contentType == "application/json") {
					try {
						data = JSON.parse(data);
					} catch (error) {
						self.doneCallback(self.xhr.status, null, {message: "JSON parse error"}, self.xhr);
						return;
					}
					self.doneCallback(self.xhr.status, data, null, self.xhr);
				} else {
					self.doneCallback(self.xhr.status, data, null, self.xhr);
				}
			}
		}
	}

	Wrapper.prototype.done = function (callback) {
		this.doneCallback = callback;
	}

	Wrapper.prototype.open = function (method, url, async, user, password) {
		if (async === undefined) async = true;
		this.xhr.open(method, url, async, user, password);
		if (this.headers) {
			for(var i in this.headers) {
				this.setRequestHeader(i, this.headers[i]);
			}
		}
	}

	Wrapper.prototype.setRequestHeader = function (key, val) {
		this.xhr.setRequestHeader(key, val);
	}

	Wrapper.prototype.send = function (data) {
		this.xhr.send(data);
	}

	window.ajax = window.ajax || {};

	ajax.get = function (parameters){
		var ajax = new Wrapper(parameters);
		if (parameters.url) {
			ajax.open("get", parameters.url, parameters.user, parameters.password);
			ajax.send(parameters.data);
		} else throw new Error("Should specify url");
		return ajax;
	}

	ajax.bigGet = function (parameters) {
		var ajax = new Wrapper(parameters);
		if (parameters.url) {
			ajax.open("post", parameters.url, parameters.user, parameters.password);
			ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajax.send(parameters.data + "&__method=get");
		} else throw new Error("Should specify url");
		return ajax;
	}

	ajax.post = function (parameters){
		var ajax = new Wrapper(parameters);
		if (parameters.url) {
			ajax.open("post", parameters.url, parameters.user, parameters.password);
			ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajax.send(parameters.data);
		} else throw new Error("Should specify url");
		return ajax;
	}
	
	ajax.delete = function (parameters){
		var ajax = new Wrapper(parameters);
		if (parameters.url) {
			ajax.open("delete", parameters.url, parameters.user, parameters.password);
			ajax.send(parameters.data);
		} else throw new Error("Should specify url");
		return ajax;
	}

	ajax.put = function (parameters){
		var ajax = new Wrapper(parameters);
		if (parameters.url) {
			ajax.open("put", parameters.url, parameters.user, parameters.password);
			ajax.send(parameters.data);
		} else throw new Error("Should specify url");
		return ajax;
	}

	ajax.serialize = function (form) {
		var query = [];
		for (var i in form) {
			if (form.hasOwnProperty(i)) {
				var el = form[i];
				if (el.getAttribute) {
					var name = el.getAttribute("name");
					if (name) {
						switch (el.type) {
							case "checkbox":
							case "radio":
								if (el.checked) {
									query.push(encodeURIComponent(name)+"=on");
								} else {
									query.push(encodeURIComponent(name)+"=off");
								}
								break;
							default:
								var value = el.value;
								query.push(encodeURIComponent(name)+"="+encodeURIComponent(value));
						}
					}
				} else query.push(encodeURIComponent(i)+"="+encodeURIComponent(el));
				
			}
		}
		return query.join("&");
	}

})()
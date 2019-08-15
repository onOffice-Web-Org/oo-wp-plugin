var onOffice = onOffice || {};

/**
 *
 * @param {string} cookieName
 * @returns {onOffice.favorites}
 *
 */

onOffice.favorites = function(cookieName) {
	this._favorized = [];
	this._cookieName = cookieName;
	this.loadCookies();
};

(function() {
	var static;
	this.add = function(estateId) {
		estateId = static._makeInt(estateId);
		if (this._favorized.indexOf(estateId) === -1) {
			this._favorized.push(estateId);
			static._save(this._favorized, this._cookieName);
		}
	};

	this.remove = function(estateId) {
		estateId = static._makeInt(estateId);
		var index = this._favorized.indexOf(estateId);
		if (index > -1) {
			this._favorized.splice(index, 1);
			static._save(this._favorized, this._cookieName);
		}
	};

	this.favoriteExists = function(estateId) {
		estateId = static._makeInt(estateId);
		return this._favorized.indexOf(estateId) !== -1;
	};

	this.loadCookies = function() {
		var cookies = static._readCookies();
		this._favorized = [];
		if (cookies.length > 0) {
			var cookiePos = static._getCookiePositionOf(this._cookieName, cookies);
			if (cookiePos !== null) {
				var cookie = cookies[cookiePos].split('=')[1];
				try {
					this._favorized = JSON.parse(cookie);
				} catch (ex) {}
			}
		}
	};

	static = {
		_readCookies: function() {
			var allcookies = document.cookie;
			var cookiearray = allcookies.split('; ');

			return cookiearray;
		},

		_getCookiePositionOf: function(name, cookiearray) {
			for (var i = 0; i < cookiearray.length; i++) {
			   var cookieName = cookiearray[i].split('=')[0].trim();
			   if (cookieName === name) {
				   return i;
			   }
			}
			return null;
		},

		_save: function(favorized, cookieName) {
			var favStringified = JSON.stringify(favorized);

			var dateNow = new Date();
			var year = dateNow.getFullYear();
			var month = dateNow.getMonth();
			var day = dateNow.getDate();
			var expiryDateString = new Date(year + 1, month, day).toUTCString();

			document.cookie = cookieName + "=" + favStringified + "; expires=" + expiryDateString + "; path=/";
		},

		_makeInt: function(input) {
			if (typeof input === "string") {
				input = Number(input);
			}
			return input;
		}
	};
}).call(onOffice.favorites.prototype);

// export $ globally since jquery-latest is not in use anymore
$ = jQuery;
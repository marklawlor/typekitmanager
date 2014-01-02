function AppViewModel(kits) {
	var self = this;

	self.kits = ko.observableArray((function(kits){
		for (var i = kits.length - 1; i >= 0; i--) {
			kits[i].domains = ko.observableArray(kits[i].domains);
			kits[i].addNewDomain = function(kit){
				kit.domains.push('');
			}
		};

		return kits;
	})(kits));


	self.searchTerm = ko.observable();


	self.filteredKits = ko.computed(function(){
		var searchTerm = self.searchTerm();
		if(searchTerm){
			searchTerm = searchTerm.toLowerCase();

			return self.kits().filter(function(kit) {

				return (kit.name.toLowerCase().indexOf(searchTerm) != -1) ||
					kit.id.toLowerCase().indexOf(searchTerm) != -1 ||
					arrayContainsSubstring(kit.domains(), searchTerm) ||
					arrayContainsSubstring(kit.families.map(function(family){
						return family['css_stack'];
					}), searchTerm) ||
					arrayContainsSubstring(kit.families.map(function(family){
						return family['name'];
					}), searchTerm)
					;
			}).sort(function(left, right) { return left.name == right.name ? 0 : (left.name < right.name ? -1 : 1) });
		}
		else {
			return self.kits().sort(function(left, right) { return left.name == right.name ? 0 : (left.name < right.name ? -1 : 1) });
		}
	});

	function arrayContainsSubstring(array, searchTerm){
		var result = false;
		for (var i = 0; i < array.length; ++i) {
			if (array[i].toLowerCase().indexOf(searchTerm) != -1) {
				result = true;
				break;
			}
		}

		return result
	};
}

function validate(form) {
	if(form.showDelConfirm){
		return confirm('Do you really want to delete this kit?');
	}
}


$(function(){
	vm = new AppViewModel(typekitKits);
	ko.applyBindings(vm);

	$(":submit").click(function () { $("#action").val(this.name); });
})


/*	==========================================================================
	 Polyfills
	 ========================================================================== */

if (!Array.prototype.map) {
	Array.prototype.map = function(fun /*, thisArg */) {
		"use strict";

		if (this === void 0 || this === null)
			throw new TypeError();

		var t = Object(this);
		var len = t.length >>> 0;
		if (typeof fun !== "function")
			throw new TypeError();

		var res = new Array(len);
		var thisArg = arguments.length >= 2 ? arguments[1] : void 0;
		for (var i = 0; i < len; i++) {
			// NOTE: Absolute correctness would demand Object.defineProperty
			//       be used.  But this method is fairly new, and failure is
			//       possible only if Object.prototype or Array.prototype
			//       has a property |i| (very unlikely), so use a less-correct
			//       but more portable alternative.
			if (i in t)
				res[i] = fun.call(thisArg, t[i], i, t);
		}

		return res;
	};
}

if (!Array.prototype.filter)
{
  Array.prototype.filter = function(fun /*, thisArg */)
  {
    "use strict";

    if (this === void 0 || this === null)
      throw new TypeError();

    var t = Object(this);
    var len = t.length >>> 0;
    if (typeof fun != "function")
      throw new TypeError();

    var res = [];
    var thisArg = arguments.length >= 2 ? arguments[1] : void 0;
    for (var i = 0; i < len; i++)
    {
      if (i in t)
      {
        var val = t[i];

        // NOTE: Technically this should Object.defineProperty at
        //       the next index, as push can be affected by
        //       properties on Object.prototype and Array.prototype.
        //       But that method's new, and collisions should be
        //       rare, so use the more-compatible alternative.
        if (fun.call(thisArg, val, i, t))
          res.push(val);
      }
    }

    return res;
  };
}
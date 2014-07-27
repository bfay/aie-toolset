//Override Underscore templates settings to prevent errors when asp_tags=on

if( typeof _ !== 'undefined' && _.templateSettings )
{
    _.templateSettings = {
        escape: /\{\{([^\}]+?)\}\}(?!\})/g,
        evaluate: /<#([\s\S]+?)#>/g,
        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g
    };
}


//Backbone.Model Overrides
if( Backbone && Backbone.Model )
{
	Backbone.Model.prototype._super = function(funcName){
		if( funcName === undefined ) return null;
	    return this.constructor.prototype[funcName].apply(this, _.rest(arguments) );
	};
	// nested models!  Might just override the internal representation of this...
	_.extend(Backbone.Model.prototype, {
	  // Version of toJSON that traverses nested models
	  toJSON: function() {
	    var obj = _.clone(this.attributes);
	    _.each(_.keys(obj), function(key) {
	      if(!_.isUndefined(obj[key]) && !_.isNull(obj[key]) && _.isFunction(obj[key].toJSON)) {
	        obj[key] = obj[key].toJSON();
	      }
	    });
	    return obj;
	  }
	});

	_.extend(Backbone.Collection.prototype, {
	  // Version of toJSON that traverses nested models in collections
	  toJSON: function() {
	    return this.map(function(model){ return model.toJSON(); });
	  }
	});
}
//Backbone.View Overrides
if( Backbone && Backbone.View )
{
    Backbone.View.prototype.eventDispatcher = _.extend({}, Backbone.Events);
}

/* USEFUL PROTOTYPES */

/**
 * courtesy from: http://monocleglobe.wordpress.com/2010/01/12/everybody-needs-a-little-printf-in-their-javascript/
 */
if( !String.prototype.printf )
{
	String.prototype.printf = function (obj) {
		var useArguments = false;
		var _arguments = arguments;
		var i = -1;
		if (typeof _arguments[0] == "string") {
			useArguments = true;
		}
		if (obj instanceof Array || useArguments) {
			return this.replace(/\%s/g,
				function (a, b) {
					i++;
					if (useArguments) {
						if (typeof _arguments[i] == 'string') {
							return _arguments[i];
						}
						else {
							throw new Error("Arguments element is an invalid type");
						}
					}
					return obj[i];
				});
		}
		else {
			return this.replace(/{([^{}]*)}/g,
				function (a, b) {
					var r = obj[b];
					return typeof r === 'string' || typeof r === 'number' ? r : a;
				});
		}
	};
}
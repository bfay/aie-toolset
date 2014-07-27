if( typeof WPV_Toolset == 'undefined' )
{
	var WPV_Toolset = {};
	WPV_Toolset.message = {};
	WPV_Toolset.message.container = null;
}

if( typeof WPV_Toolset.Utils == 'undefined' ) WPV_Toolset.Utils = {};

WPV_Toolset.Utils.eventDispatcher = _.extend({}, Backbone.Events);

WPV_Toolset.Utils.do_ajax_post = function( params, callback_object )
{
	jQuery.post(ajaxurl, params, function (response) {

		if ( (typeof(response) !== 'undefined') && response !== null && ( response.message || response.Data )  ) {

			if( callback_object && callback_object.success && typeof callback_object.success == 'function'  )
				callback_object.success.call( this, response );
				WPV_Toolset.Utils.eventDispatcher.trigger('on_ajax_success', response);
		}
		else if( (typeof(response) !== 'undefined') && response !== null && response.error )
		{

			if( callback_object && callback_object.error && typeof callback_object.error == 'function'  )
				callback_object.error.call(this);
				WPV_Toolset.Utils.eventDispatcher.trigger('on_ajax_error', response);
		}
	}, 'json')
		.fail(function (jqXHR, textStatus, errorThrown) {
			console.log('Ajax call failed', textStatus, errorThrown)
			if( callback_object && callback_object.fail && typeof callback_object.fail == 'function'  )
				callback_object.fail.call(this, errorThrown);
				WPV_Toolset.Utils.eventDispatcher.trigger('on_ajax_fail', textStatus, errorThrown );
		})
		.always(function () {
			//console.log( arguments );
			WPV_Toolset.Utils.eventDispatcher.trigger('on_ajax_complete', arguments);
		});
};

;(function ( $, window, document, undefined ) {

	// Create the defaults once
	var pluginName = "wpvToolsetMessage",
		dataPlugin = "plugin_" + pluginName,
		defaults = {
			text : "Enter a customized text to be displayed",
			type: '',
			inline: false,
			header: false,
			headerText: false,
			close: false,
			use_this: true,
			fadeIn: 100,
			fadeOut: 100,
			stay: false,
			onClose: false,
			onOpen: false,
			onDestroy:false,
			args:[],
			referTo: null,
			offestX: -20,
			offsetY: 0,
			classname: '',
			stay_for: 1200, // Ignored when 'msPerCharacter is given.
			msPerCharacter: 50 // Ignered when 'stay_for' is given. This value is multiplied by the number of defaults.text characters count.
		},
		has_stay = false,
		is_open = false,
		prev = null,
		prev_text = '';

	// The actual plugin constructor
	function Plugin(element, options) {
		var self = this;

		self.container = $(element);

		self.prms = $.extend({}, defaults, options);
		self._defaults = defaults;
		self._name = pluginName;

		self.box = null;
		self.header = null;
		self.remove = null;
		self.tag = self.prms.inline ? 'span' : 'p';
		self.bool = false;

		if ( typeof (options.stay_for) === 'undefined' && typeof(self.prms.msPerCharacter) === 'number' ) { // If stay_for parameter wasn't passes when the plugin wass called AND msPerCharacter has correct type
			self.prms.stay_for = self.prms.text.length * self.prms.msPerCharacter;
		}

	}

	Plugin.prototype = {
		init: function () {
			var self = this;

			if( self.container.data('has_message' )  )
			{
				self.destroy();
			}

			if( self.container.children().length > 0 )
			{
				self.container.children().each(function(i){
					if( $(this).text() == self.prms.text )
					{
						self.bool = true;
					}
				});
			}

			if( self.bool ) return;

			if( has_stay )
			{
				if( prev )
				{
					var rem = prev;
					prev = null;
					has_stay = false;
					is_open = false;
					rem.fadeTo( 0, 0, function(){
						rem.remove();
						rem = null;
					});
				}
			}

			if( self.prms.header && self.prms.headerText )
			{
				self.box = $('<div class="toolset-alert toolset-alert-'+self.prms.type+' '+self.prms.classname+'" />');
				self.header = $('<h2 class="toolset-alert-self.header" />');
				self.box.append(self.header);
				self.header.text(self.prms.headerText);
				self.box.append('<'+self.tag+'></'+self.tag+'>');
				self.box.find(self.tag).html( self.prms.text );
			}
			else
			{
				self.box = $('<'+self.tag+' class="toolset-alert toolset-alert-'+self.prms.type+' '+self.prms.classname+'" />');
				self.box.html( self.prms.text );
			}

			if( self.prms.close ){
				self.remove = $('<i class="toolset-alert-close icon-remove-sign js-icon-remove-sign"></i>');
				self.box.append( self.remove );
				self.remove.on('click', function(event){
					self.wpvMessageRemove();
				});
			}


			//if( is_open ) self.wpvMessageRemove();
			self.container.append( self.box );
			self.container.data('has_message', true );
			self.box.hide();

			if( null !== self.prms.referTo )
			{
				self.box.css({
					"position":"absolute",
					"z-index":10000,
					"top": self.prms.referTo.position().top + self.prms.offestY + "px",
					"left": self.prms.referTo.position().left + self.prms.referTo.width() + self.prms.offestX + "px"
				});
			}

			self.container.data( 'message-box', self.box );

			self.box.fadeTo( null != prev ? 0 : self.prms.fadeIn, 1, function(){
				prev = $(this);
				prev_text = self.prms.text;
				is_open = true;
				if( self.prms.onOpen && typeof self.prms.onOpen == 'function' )
				{
					self.prms.onOpen.apply( self, self.prms.args );
				}
				if( self.prms.stay ){
					has_stay = true;
				}
				else
				{
					var remove_message = _.bind(self.wpvMessageRemove, self);
					_.delay( remove_message, self.prms.stay_for );
					//self.wpvMessageRemove();
				}
			});

			return self;
		},
		wpvMessageRemove: function () {

			var self = this;

			if( self.box || self.container.data( 'message-box') )
			{
				var box = self.box || self.container.data( 'message-box');

				box.fadeTo( self.prms.fadeOut, 0, function(){
					is_open = false;
					prev = null;
					prev_text = '';
					has_stay = false;
					if( self.prms.onClose && typeof self.prms.onClose == 'function' )
					{
						self.prms.onClose.apply( self, self.prms.args );
					}

					$( this ).remove();

					self.container.data( 'message-box', null );
					self.box = null;
				});
			}

			return self;
		},
		destroy:function()
		{
			this.container.empty();
			if( this.prms.onDestroy && typeof this.prms.onDestroy == 'function' )
			{
				this.prms.onDestroy.apply( this, this.prms.args );
			}
			this.box = null;
			this.container.data( 'message-box', null );
			this.container.data('has_message', false );
		}
	};


	$.fn[ pluginName ] = function ( arg ) {

		return this.each(function(){
			var args, instance;

			if ( !( $(this).data( dataPlugin ) instanceof Plugin ) ) {
				// if no instance, create one
				$(this).data( dataPlugin, new Plugin( $(this), arg ) );
			}
			// do not use this one if you want the plugin to be a singleton bound to the DOM element
			else
			{
				// if instance delete reference and do another one
				$(this).data( dataPlugin, null );
				$(this).data( dataPlugin, new Plugin( $(this), arg ) );
			}

			instance = $(this).data( dataPlugin );

			instance.element = $(this);

			// call Plugin.init( arg )
			if (typeof arg === 'undefined' || typeof arg === 'object') {

				if ( typeof instance['init'] === 'function' ) {
					instance.init( arg );
				}

				// checks that the requested public method exists
			} else if ( typeof arg === 'string' && typeof instance[arg] === 'function' ) {

				// copy arguments & remove function name
				args = Array.prototype.slice.call( arguments, 1 );

				// call the method
				return instance[arg].apply( instance, args );

			} else {

				$.error('Method ' + arg + ' does not exist on jQuery.' + pluginName);

			}
		});
	};
})( jQuery, window, document );

(function ($) {
	$.fn.insertAtIndex = function(index,selector){
		var opts = $.extend({
			index: 0,
			selector: '<div/>'
		}, {index: index, selector: selector});
		return this.each(function() {
			var p = $(this);
			var i = ($.isNumeric(opts.index) ? parseInt(opts.index,10) : 0);
			if (i <= 0)
				p.prepend(opts.selector);
			else if( i > p.children().length-1 )
				p.append(opts.selector);
			else
				p.children().eq(i).before(opts.selector);
		});
	};
})( jQuery );

(function ($) {

	$.fn.loaderOverlay = function( action,options )
	// action: 'show'|'hide' attributes are optional.
	// options: fadeInSpeed, fadeOutSpeed, displayOverlay, class. attributes are optional
	{

		var defaults = {
			fadeInSpeed : 'fast',
			fadeOutSpeed : 'fast',
			displayLoader: true,
			class: null
		};

		var prms = $.extend( defaults, options );
		var $overlayContainer = this;
		var $overlayEl = $('<div class="loader-overlay" />');

		var showOverlay = function() {
			if ( ! $overlayContainer.data('has-overlay') ) {
				$overlayEl
					.appendTo($overlayContainer)
					.hide()
					.fadeIn(prms.fadeInSpeed, function() {
						$overlayContainer.data('has-overlay', true);
						$overlayContainer.data('overlay-el', $overlayEl);
					} );
			}
		};

		var hideOverlay = function() {
			if ( $overlayContainer.data('has-overlay') ) {
				$overlayContainer.data('overlay-el')
					.fadeOut(prms.fadeOutSpeed, function() {
						$overlayEl.remove();
						$overlayContainer.data('has-overlay', false);
				} );
			}
		};

		if ( prms.class !== null ) {
			$overlayEl.addClass(prms.class);
		}
		if ( prms.displayLoader ) {
			$('<div class="preloader" />').appendTo($overlayEl);
		}

		if ( typeof(action) !== 'undefined' ) { // When 'action' parameter is given

			if ( action === 'show' ) {
				showOverlay();
			}
			else if ( action === 'hide' ) {
				hideOverlay();
			}

		}
		else { // when the method is called without 'action' parameter

			if ( $overlayContainer.data('has-overlay') ) { // hide overlay if it's displayed
				hideOverlay();
			}
			else { // show overlay if not
				showOverlay();
			}

		}

		return this;
	};

})( jQuery );

(function ($) {
	/*
	Basic usage:
	$element.ddlWpPointer(); // will show a pointer if it's hidden OR hide a pointer if it's shown

	1. $element have to be valid jQuery selector
	2. data-toolipt-header HTML attribute is required to display the header
	3. data-tooltip-content HTML attribute is required to display the content

	Customization:
	$element.ddlWpPointer('action', // action: 'show' | 'hide'
	{
		content: $element // $element have to be valid jQuery selector content element should contain H3 for the header and P for the content. Example: <div><h3>Header</h3><p>Content</p></div>
		edge: 'left' // 'left' | 'right' | 'top' | 'bottom'
		align: 'center' // 'center' | 'right' | 'left'
		offset: 'x y' // example: '0 15'
	})

	 */
	$.fn.ddlWpPointer = function( action, options )
	{
		var $el = this;

		//$.jStorage.flush();

		var defaults = {
			headerText: function() {
				var header = $el.data('tooltip-header');
				if ( header ) {
					return header;
				}
				else {
					return 'use <b>data-tooltip-header="header text"</b> attribute to create a header';
				}
			},
			contentText : function() {
				var content = $el.data('tooltip-content');
				if ( content ) {
					return content;
				}
				else {
					return 'use <b>data-tooltip-content="content text"</b> attribute to create a content';
				}
			},
			content: function() { // returns string by default (data-tooltip-header and data-tooltip-content attibutes), but can be overridden by jQuery obj
				return '<h3>'+ defaults.headerText() +'</h3><p>'+ defaults.contentText() +'</p>';
			},
			edge : 'left',
			align : 'center',
			offset: '0 0',
			stay_hidden: false
		};

		var prms = $.extend( defaults, options );

		var showPointer = function() {

			if ( ! $el.data('has-wppointer') ) {
				$el
					.pointer({
						content: function() {
							return prms.content;
						},
						position: {
							edge: prms.edge,
							align: prms.align,
							offset: prms.offset
						},
						close: function() {

							$el.data('has-wppointer', false);
							$el.trigger('help_tooltip_closes', options );
						}
					})
					.pointer('open');

				$el.data('has-wppointer', true);
			}
		};

		var hidePointer = function() {

			if ( $el.data('has-wppointer') ) {

				$el.pointer('close');
				$el.data('has-wppointer', false);

			}

		};

		if ( typeof(action) !== 'undefined' ) { // When 'action' parameter is given

			if ( action === 'show' && prms.stay_hidden !== true ) {
				showPointer();
			}
			else if ( action === 'hide' ) {
				hidePointer();
			}

		}
		else { // when the method is called without 'action' parameter

			if ( $el.data('has-wppointer') ) { // hide pointer if it's displayed
				hidePointer();
			}
			else if( prms.stay_hidden !== true ) { // show it if not
				showPointer();
			}

		}

		return this;
	};

})( jQuery );

WPV_Toolset.Utils.Loader = function()
{
	//fake comment
	var self = this;

	self.loading = false; self.el = null;

	self.loader = jQuery('<div class="ajax-loader spinner"></div>');

	self.loadShow = function( el )
	{
		self.el = el;
		self.loading = true;

        self.loader.prependTo( self.el ).show();

        return self.loader;
	};
	self.loadHide = function()
	{
		self.loader.fadeOut(400, function(){

			self.loading = false;
			jQuery(this).remove();
		});

        return self.loader;
	};
};

if( typeof _ != 'undefined' )
{
	WPV_Toolset.Utils.flatten = function(x, result, prefix) {
		if(_.isObject(x)) {
			_.each(x, function(v, k) {
				WPV_Toolset.Utils.flatten(v, result, prefix ? prefix + '_' + k : k)
			})
		} else {
			result[prefix] = x
		}
		return result
	};
	WPV_Toolset.Utils.flatten_filter_by_key = function( x, result, prefix, filter )
	{
		var res = [],
		find = WPV_Toolset.Utils.flatten( x, result, prefix );

		if ( !filter ) return _.values( find );

		_.each(find, function( element, index, list ){
			if( index.indexOf( prefix ? prefix + '_'+filter : filter ) !== -1 || filter === index )
				res.push( element );
		});

		return res;
	}
	WPV_Toolset.Utils.containsObject = function (obj, list) {
		var res = _.find(list, function(val){
			return _.isEqual(obj, val);
		});
		return (_.isObject(res))? true:false;
	};
};



(function($) {
	$.fn.textWidth = function() {
		var text = this.html() || this.text() || this.val();
		return( $.textWidth( text ) );
	};
	$.textWidth = function(text) {
		var div = $('#textWidth');
		if (div.length === 0)
			div = $('<div id="textWidth" style="display: none;"></div>').appendTo($('body'));
		div.html(text);
		return(div.width());
	};
})(jQuery);

//Courtesy from http://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
WPV_Toolset.Utils.escapeHtml = function(str) {
	if (typeof(str) == "string"){
		try{
			var newStr = "";
			var nextCode = 0;
			for (var i = 0;i < str.length;i++){
				nextCode = str.charCodeAt(i);
				if (nextCode > 0 && nextCode < 128){
					newStr += "&#"+nextCode+";";
				}
				else{
					newStr += "?";
				}
			}
			return newStr;
		}
		catch(err){
		}
	}
	else{
		return str;
	}
};
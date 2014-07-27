if( typeof DDL_Helper === 'undefined' )
{
	var DDL_Helper = {};
}

DDL_Helper.SanitizeHelper = function( $ )
{
	"use strict";

	var self = this;

	self.sanitize = null;

	if(!Sanitize.Config) {
		Sanitize.Config = {};
	}

	// do a placeholder replacement for this ones
	self.NEEDS_PLACEHOLDER = ['video', 'form', 'audio', 'textarea'];

	self.CLASSNAME_WHITELIST = ["icon-facetime-video", "icon-list-alt", "icon-music", "alignleft", "alignright", "aligncenter"];

	// configuration object
	Sanitize.Config.CUSTOM = {
		elements: [
			'a', 'b', 'blockquote', 'br', 'caption', 'cite', 'code', 'col',
			'colgroup', 'dd', 'dl', 'dt', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
			'i', 'img', 'li', 'ol', 'p', 'pre', 'q', 'small', 'strike', 'strong',
			'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'u',
			'ul','ol'
		],

		attributes: {
			'a'         : ['title'],
			'blockquote': ['cite'],
			'col'       : [],
			'colgroup'  : [],
			'img'       : ['alt', 'src', 'title'],
			'ol'        : ['start', 'type'],
			'q'         : ['cite'],
			'table'     : ['summary'],
			'td'        : ['abbr', 'axis', 'colspan', 'rowspan'],
			'th'        : ['abbr', 'axis', 'colspan', 'rowspan', 'scope'],
			'ul'        : ['type']
		},

		protocols: {
			'a'         : {'href': ['ftp', 'http', 'https', 'mailto', Sanitize.RELATIVE]},
			'blockquote': {'cite': ['http', 'https', Sanitize.RELATIVE]},
			'img'       : {'src' : ['http', 'https', Sanitize.RELATIVE]},
			'q'         : {'cite': ['http', 'https', Sanitize.RELATIVE]}
		},

		transformers: [transformer_default, transformer_placeholders, transformer_fix_our_classes, transformer_safe_fix_style_attribute]
	};

	self.init = function()
	{
		self.sanitize = new Sanitize( Sanitize.Config.CUSTOM );
	};

	self.stringToDom = function( htmlString )
	{
		var fragment = document.createDocumentFragment()
			, dummy = document.createElement('div')
			, append;

		dummy.innerHTML = _.unescape( htmlString );

		while( append = dummy.firstChild )
		{
			fragment.appendChild(append);
		}

		dummy.innerHTML = '';

		dummy.appendChild( self.sanitize.clean_node( fragment ).cloneNode(true) );

		return dummy;
	};

	self.getDummyElementHeight = function ( dummy )
	{
		var height;
		document.body.appendChild(dummy);
		height = $(dummy).height();
		document.body.removeChild(dummy);

		return height;
	};

	//transformers callback functions
	function transformer_placeholders( options )
	{
		var opts = options,
		    dummy = null;

		if ( _.indexOf( self.NEEDS_PLACEHOLDER, opts.node_name ) !== -1 ) {

			var className = opts.node_name + '-placeholder';
			var cellContent = '';

			dummy = $('<div class="element-placeholder ' + className + '" />');

			if ( opts.node_name === 'video' ) {
				cellContent = $('<i class="icon-facetime-video" />');
			}
			else if ( opts.node_name === 'form' ) {
				cellContent = $('<i class="icon-list-alt" />');
			}
			else if ( opts.node_name === 'audio' ) {
				cellContent = $('<i class="icon-music" />');
			}

			dummy.append( cellContent ); // Is it a bug or a feature? I can't append <i class="icon-list-alt" />. Class are removed from <i> element. Empty <i> element is appended

			return {
				attr_whitelist: ['class'],
				node: dummy[0],
				whitelist: true,
				whitelist_nodes: ['i']
			};
		}

		return null;
	}

	function transformer_default( options )
	{
		var opts = options,
			computedStyle,
			isComputedStyleSupported = "getComputedStyle" in window;

		if( opts.allowed_elements[opts.node_name] === true  ) return null;

		if ( _.indexOf( self.NEEDS_PLACEHOLDER, opts.node_name ) !== -1  ) return null;


		document.body.appendChild(opts.node);
		computedStyle = ( isComputedStyleSupported ? window.getComputedStyle(opts.node, "") : opts.node.currentStyle ).display;
		document.body.removeChild(opts.node);

		var dummy = null;

		if( opts.allowed_elements[opts.node_name] !== true && computedStyle === 'block' )
		{
			dummy = $('<div class="element-replacement block-element" />');

			dummy.text( $( opts.node ).text() );

			return {
				attr_whitelist:['class'],
				node:dummy[0],
				whitelist:true,
				whitelist_nodes:[]
			};
		}
		else if( opts.allowed_elements[opts.node_name] !== true && computedStyle === 'inline' )
		{
			dummy = $('<span class="element-replacement inline-element" />');

			dummy.text( $( opts.node ).text() );

			return {
				attr_whitelist:['class'],
				node:dummy[0],
				whitelist:true,
				whitelist_nodes:[]
			};
		}
		else
		{
			return null;
		}

		return null;
	}

	function transformer_fix_our_classes( options )
	{
		var opts = options;

		if( self.CLASSNAME_WHITELIST.indexOf( $(opts.node).attr('class') ) === -1 ) return null;

		return{
			attr_whitelist:['class'],
			node:opts.node,
			whitelist:true,
			whitelist_nodes:[]
		}
	}

	function transformer_safe_fix_style_attribute( options )
	{
		var opts = options;

		if( $(opts.node).attr('style') !== undefined && $(opts.node).attr('style').indexOf('text-align') !== -1 )
		{
			return{
				attr_whitelist:['style'],
				node:opts.node,
				whitelist:true,
				whitelist_nodes:[]
			}
		}
		else
		{
			return null;
		}

		return null;
	}

	self.init();
};

( function( $ ){
	DDL_Helper.sanitizeHelper = new DDL_Helper.SanitizeHelper( $ );
} ( jQuery ) );
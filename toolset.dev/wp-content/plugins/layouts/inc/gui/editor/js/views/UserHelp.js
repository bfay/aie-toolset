if ( typeof DDLayout === 'undefined' )
{
	var DDLayout = {
		ddl_admin_page : {
			is_colorbox_opened : false
		}
	};
}

DDLayout.UserHelp = function()
{
	var self = this;
	var $ = jQuery.noConflict();
	self.currentVideo = null;
	self.player = null;
	self.videos = {
		// TODO: Localize these strings
		editorBasics : {
			url: 'http://d1ftqtsbckf6jv.cloudfront.net/basic-layout-editing-a.mp4',
			label: 'Editor basics',
			text: 'Learn to use the Layouts editor',
			width: 828,
			height: 576
		}
	};
	
	if(/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())) {
		self.videos.editorBasics.url = 'http://d1ftqtsbckf6jv.cloudfront.net/basic-layout-editing-a.ogv';
	}
	
	self.isColorboxOpened = function() {
		return DDLayout.ddl_admin_page.is_colorbox_opened;
	};
	_.each( self.videos , function( video, keyName ) { // add 'name' property to each object. It will be use by jStorage.
		video.name = keyName;
	});

	self.videoToolbar = {
		$el : $('.js-tutorial-video-bar').clone() // A hidden element, contains HTML structure for the toolbar. No data.
	};
	self.videoToolbar.$collapseButton = self.videoToolbar.$el.find('.js-collapse-tutorial-video-bar');
	self.videoToolbar.$closeButton = self.videoToolbar.$el.find('.js-close-tutorial-video-bar');
	self.videoToolbar.$showButton = self.videoToolbar.$el.find('.js-show-tutorial-video-bar');
	self.videoToolbar.$playButton = self.videoToolbar.$el.find('.js-play-tutorial-video');
	self.videoToolbar.$label = self.videoToolbar.$el.find('.js-tutorial-video-name');
	self.videoToolbar.$text = self.videoToolbar.$el.find('.js-tutorial-video-bar-desc');

	self.init = function() {

		var self = this;

		$(document).on( 'play-video', function( e, videoName ) {

			if ( typeof( videoName ) !== 'undefined' && $.jStorage.get( videoName ) !== 'disabled' ) {

				var startCollapsed = false;
				if ( $.jStorage.get( videoName ) === 'collapsed' ) {
					startCollapsed = true;
				}

				self.currentVideo = self.videos[videoName];

				if ( startCollapsed ) {
					self.showToolbar( false );
					self.collapseToolbar( false );
				}
				else {
					self.showToolbar( true);
				}

				self.bindEvents();

			}

		});

	};

	self.bindEvents = function() {
		self.videoToolbar.$collapseButton.on('click.videoToolbar', function() {
			self.collapseToolbar( true );
		});

		self.videoToolbar.$showButton.on('click.videoToolbar', function() {
			self.expandToolbar();
		});

		self.videoToolbar.$closeButton.on('click.videoToolbar', function() {
			self.removeToolbar();
			self.disableVideo();
			self.unbindEvents();
		});

		self.videoToolbar.$playButton.on('click.videoToolbar', function(){
			self.showVideo( self.currentVideo.url );
		});

		$('.js-remove-video').on('click.videoToolbar', function() {
			self.removeVideo();
			self.unbindEvents();
		});
	};

	self.unbindEvents = function() {
		$(document).off('.videoToolbar');
	};

	self.expandToolbar = function() {

		self.videoToolbar.$el.removeClass('collapsed');
		$.jStorage.set( self.currentVideo.name.toString(), null );
		$('html').addClass('video-toolbar-visible');

	};

	self.showToolbar = function( animation ) { // true | false

		self.videoToolbar.$el.removeClass('collapsed');
		self.videoToolbar.$label.empty().append( self.currentVideo.label );
		self.videoToolbar.$text.text( self.currentVideo.text );

		if ( self.isColorboxOpened() ) {
			self.videoToolbar.$el.appendTo('#colorbox .js-video-toolbar-container');
		}
		else {
			self.videoToolbar.$el.appendTo('body');
			$('html').addClass('video-toolbar-visible');
		}

		if ( animation ) {
			self.videoToolbar.$el
				.hide()
				.slideDown('fast');
		}
		else {
			self.videoToolbar.$el.show();
		}

	};

	self.collapseToolbar = function( animation ) { // true | false

		if ( self.isColorboxOpened() ) {

			if ( animation ) {

				self.videoToolbar.$el
					.slideUp('fast', function(){
						self.videoToolbar.$el
							.addClass('collapsed')
							.slideDown('fast');
					});

			}
			else {
				self.videoToolbar.$el
					.show()
					.addClass('collapsed');
			}

		}
		else {

			if ( animation ) {

				self.videoToolbar.$el.css('width', self.videoToolbar.$el.innerWidth());
				self.videoToolbar.$el.animate({
					'margin-left': self.videoToolbar.$el.width() - 55
				},
				function() {
					self.videoToolbar.$el
						.animate({
							'height': '46px'
						},
						100,
						function(){
							self.videoToolbar.$el
								.addClass('collapsed')
								.css({
									'margin-left': 0,
									'width': 'auto',
									'height': 'auto'
								});
						});
				});

			}
			else {
				self.videoToolbar.$el.addClass('collapsed');
			}

			$('html').removeClass('video-toolbar-visible');

		}

		$.jStorage.set( self.currentVideo.name.toString(), 'collapsed' );

	};

	self.removeToolbar = function() {

		self.videoToolbar.$el.fadeOut('fast', function(){
			self.videoToolbar.$el.removeClass('collapsed');
			self.videoToolbar.$el.remove();
		});

	};

	self.showVideo = function( fileName ) {

		var $videoEl = $('<video class="js-video-player"/>');
		$videoEl
			.prop('src', fileName)
			.prop('width', self.currentVideo.width)
			.prop('height', self.currentVideo.height);

		var createVideoPlayer = function() {

			if (self.player === null ) {

				$videoEl.appendTo( '#colorbox .js-video-container' );

				self.player = new MediaElementPlayer( $videoEl );
				self.player.pause();
				self.player.setSrc( fileName );
				self.player.play();

			}

		};

		if ( self.isColorboxOpened() ) {

			$videoEl.attr( 'width', $('#colorbox .js-ddl-dialog-content').width() );
			createVideoPlayer();

		}

		else {

			jQuery.colorbox({
				href: '#js-video-player-dialog',
				closeButton: false,
				width: self.currentVideo.width + 20,
				onComplete: function() {
					createVideoPlayer();
					jQuery.colorbox.resize();
				}
			});

		}

	};

	self.removeVideo = function() {

		if ( self.player !== null ) {

			self.player.pause();
			self.player = null;
			$('.js-video-container').empty(); // TODO: Find a better solution to remove all the instances of MediaElementPlayer

		}

	};

	self.disableVideo = function() {

		if ( self.currentVideo !== null ) {

			$.jStorage.set( self.currentVideo.name.toString(), 'disabled' );
			self.currentVideo = null;

		}

	};

	self.init();
};
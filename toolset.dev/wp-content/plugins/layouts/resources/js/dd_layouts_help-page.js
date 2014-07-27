(function($){

	$(function(){

		var videoHelp = new DDLayout.UserHelp();

		var $videoList = $('.js-videos-list');
		$.each( videoHelp.videos, function() {
			var $li = $('<li class="js-play-video"><a href="' + this.url + '"><i class="icon-play-circle"></i> ' + this.label + '</a></li>');
			$li.data( 'video', this );
			$videoList.append( $li );

		});
		$('.js-play-video').on('click', function(e) {
			e.preventDefault();

			videoHelp.currentVideo = $(this).data('video');
			videoHelp.showVideo( videoHelp.currentVideo.url );
			videoHelp.bindEvents();

			return false;
		});

	});

}(jQuery));
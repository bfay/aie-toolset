// Media uploader

jQuery(document).ready(function($) {

	var file_frame;

	jQuery(document).on('click', '.js-ddl-add-media', function(e) {
		e.preventDefault();

		var $uploadBtn = $(this);

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({

			title: $(this).data('uploader-title'),			// Title of WP Media Uploader frame
			button: { text: $(this).data('uploader-button-text') },	// Button text
			library: { type: 'image' },
//			frame: 'post',
//			displaySettings: true,
//	        displayUserSettings: true,
			multiple: false  // True allows multiple files to be selected

		});

		// Callback for selected image
		file_frame.on('select', function() {

			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			console.log(attachment);

//			console.log(attachment.sizes);

			// Set value for the URL field
			$uploadBtn
					.closest('.js-ddl-media-field')
					.find('.js-ddl-media-url')
					.val(attachment.url);
		});

		// Open the modal
		file_frame.open();
	  });

});
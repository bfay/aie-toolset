// dialog-repeating-fields.js

DDLayout.DialogRepeatingFields = function($)
{
	var self = this;

	self.init = function() {
	};

	self.initilize_from_content = function(content, dialog) {

		// get a list of the repeating fields.
		var repeating_fields = new Array();

		jQuery('#ddl-default-edit [name^="ddl-layout-"]').each( function () {
			var data = jQuery(this).attr('name');
			data = data.substr(11);

			if (!self.not_repeating(data)) {
				if (jQuery.inArray(data, repeating_fields) == -1) {
					repeating_fields.push(data);
				}
			}
		});

		// add or remove the input elements to match the content
		for (var i = 0; i < repeating_fields.length; i++) {
			var data = repeating_fields[i];
			var data_key = data.substr(0, data.length - 2);

			var group_name_match = /\[(.*?)\]/.exec(data_key);
			var array_data_name = group_name_match[1];
			data_key = data_key.substr(array_data_name.length + 2);

			var element_count = jQuery('#ddl-default-edit [name="ddl-layout-' + data + '"]').length;
			var content_count = 0;
			if (typeof content[array_data_name] != 'undefined') {
				content_count = content[array_data_name].length;
			}

			if (content_count) {
				if (content_count > element_count) {
					self._add_elements(content_count - element_count, data);
				}
				if (content_count < element_count) {
					self._remove_elements(element_count - content_count, data);
				}
			}

			// set the element values.

			var elements = jQuery('#ddl-default-edit [name="ddl-layout-' + data + '"]');
			for (var j = 0; j < content_count; j++) {
				dialog.set_element_value(elements[j], content[array_data_name][j][data_key]);
			}

			var element = jQuery(elements[0]).closest('.js-repeat-field-container').sortable({
				handle:'.js-ddl-repeat-field-move',
				cursor: 'ns-resize',
				axis: 'y',
				placeholder: 'ddl-repeat-field-placeholder',
				start: function(e, ui) {
					ui.placeholder.height(ui.item.height());
				}
			});

		}

		jQuery('.js-repeat-field-container').each( function () {
			self._check_max_items(jQuery(this));
		});
	};

	self._add_elements = function (count, element_name) {

		for (var i = 0; i < count; i ++) {
			var element = jQuery('#ddl-default-edit [name="ddl-layout-' + element_name + '"]:last');
			var group = element.closest('.js-ddl-repeat-field');

			if (group.length) {
				group.clone().insertAfter(group);
			}
		}

	};

	self._remove_elements = function (count, element_name) {

		for (var i = 0; i < count; i ++) {
			var element = jQuery('#ddl-default-edit [name="ddl-layout-' + element_name + '"]:last');
			var group = element.closest('.js-ddl-repeat-field');
			group.remove();
		}

	};

	self.not_repeating = function(name) {
		return name.substr(name.length - 2, 2) != '[]';
	};

	self.initialize_events = function () {

		// Handle add a new element button click
		jQuery(document).on('click', '#ddl-default-edit .js-ddl-repeat-field-button', {dialog: self}, function(event) {
			var group = jQuery(this).parent().prev('.js-repeat-field-container').find('.js-ddl-repeat-field:last');
			if (group.length) {
				var group_clone = group.clone();
				group_clone.find('[name^="ddl-layout-"]').each( function (){
					jQuery(this).val('');
				});

				group_clone.insertAfter(group);
			}

			self._check_max_items(jQuery(this).parent().prev('.js-repeat-field-container'));
		});

		// Handle remove element click
		jQuery(document).on('click', '#ddl-default-edit .js-ddl-repeat-field-remove', {dialog: self}, function(event) {
			var container = jQuery(this).closest('.js-repeat-field-container');
			var group = jQuery(this).closest('.js-ddl-repeat-field');
			if (jQuery.find('[name="' + group.attr('name') + '"]').length > 1) {
				group.fadeOut('fast', function() {
					jQuery(this).remove();
					self._check_max_items(container);
				});
			}

		});

	};

	self.close_events = function () {
		jQuery(document).off('click', '#ddl-default-edit .js-ddl-repeat-field-button');
		jQuery(document).off('click', '#ddl-default-edit .js-ddl-repeat-field-remove');
	};

	self._check_max_items = function ($field_container) {
		var max_items = $field_container.data('max-items');
		if (max_items != -1) {
			var count = jQuery($field_container).find('.js-ddl-repeat-field').length;
			if (count >= max_items) {
				jQuery($field_container).next().find('.js-ddl-repeat-field-button').prop('disabled', true);
			} else {
				jQuery($field_container).next().find('.js-ddl-repeat-field-button').prop('disabled', false);
			}
		}
	};

};
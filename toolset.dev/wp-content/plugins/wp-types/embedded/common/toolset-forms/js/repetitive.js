/*
 * Repetitive JS.
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/tags/Types1.6b4-CRED1.3b4-Views1.6.2b2/toolset-forms/js/repetitive.js $
 * $LastChangedDate: 2014-07-09 16:17:06 +0000 (Wed, 09 Jul 2014) $
 * $LastChangedRevision: 24810 $
 * $LastChangedBy: juan $
 *
 */
var wptRep = (function($) {
    var count = {};
    function init() {
        // Reorder label and description for repetitive
        $('.js-wpt-repetitive').each(function() {
            var $this = $(this),
			$parent;
			if ($('body').hasClass('wp-admin')) {
				var title = $('label', $this).first().clone();
				var description = $('.description', $this).first().clone();
				$('.js-wpt-field-item', $this).each(function() {
					$('label', $this).remove();
					$('.description', $this).remove();
				});
				$this.prepend(description).prepend(title);
			}
			if ($this.hasClass('js-wpt-field-items')) {// This happens on the frontent
				$parent = $this;
			} else {// This happens on the backend
				$parent = $this.find('.js-wpt-field-items');
			}
            _toggleCtl($parent);
        });
        $('.js-wpt-field-items').each(function(){
            if ($(this).find('.js-wpt-repdelete').length > 1) {
                 $(this).find('.js-wpt-repdelete').show();
            } else if ($(this).find('.js-wpt-repdelete').length == 1) {
                 $(this).find('.js-wpt-repdelete').hide();
            }
        });
        // Add field
        $('.js-wpt-repadd').on('click', function() {
            var $this = $(this),
			parent,
			tpl;
			$parent = $this.closest('.js-wpt-field-items');
			if (1 > $parent.length) {
				return;
			}
            if ($('body').hasClass('wp-admin')) {
				// Get template from the footer templates by wpt-id data attribute
				tpl = $('<div>' + $('#tpl-wpt-field-' + $this.data('wpt-id')).html() + '</div>');
				// Remove label and descriptions from the template
                $('label', tpl).first().remove();
                $('.description', tpl).first().remove();
                // Adjust ids and labels where needed for the template content
				$('[id]', tpl).each(function() {
                    var $this = $(this), uniqueId = _.uniqueId('wpt-form-el');
                    tpl.find('label[for="' + $this.attr('id') + '"]').attr('for', uniqueId);
                    $this.attr('id', uniqueId);
                });
				// Calculate _count to build the name atribute
                var _count = tpl.html().match(/\[%%(\d+)%%\]/);
                if (_count != null) {
                    _count = _countIt(_count[1], $this.data('wpt-id'));
                } else {
                    _count = '';
                }
				// Adjust the _count to avoid duplicates when some intermediary has been deleted
				while ( $('[name*="[' + _count + ']"]', $parent).length > 0 ) {
					_count++;
				}
				// Insert the template before the button
                $this.before(tpl.html().replace(/\[%%(\d+)%%\]/g, '[' + _count + ']'));
            } else {
                /**
                 * template
                 */
				tpl = $('<div>' + $('#tpl-wpt-field-' + $this.data('wpt-id')).html() + '</div>');
				
				$('[id]', tpl).each(function() {
                    var $this = $(this), uniqueId = _.uniqueId('wpt-form-el');
                    $this.attr('id', uniqueId);
                });
				// Calculate _count to build the name atribute
                var _count = tpl.html().match(/\[%%(\d+)%%\]/);
                if (_count != null) {
                    _count = _countIt(_count[1], $this.data('wpt-id'));
                } else {
                    _count = '';
                }
				// Adjust the _count to avoid duplicates when some intermediary has been deleted
				while ( $('[name*="[' + _count + ']"]', $parent).length > 0 ) {
					_count++;
				}
				// Insert the template before the button
                $this.before(tpl.html().replace(/\[%%(\d+)%%\]/g, '[' + _count + ']'));
				
				/*
				* This is the old implementation
				* Lets leave this just in case
				
                template_element = $('.wpt-repctl:first', $parent);
                tpl = $('<div class="wpt-repctl">'+template_element.html()+'</div>');
				
                $('.js-wpt-repdelete', tpl).show().removeAttr('disabled');
                el = $('.js-wpt-repetitive', tpl);
                wpt_name = el.data('wpt-name');
                    index = 0;

                // Not really sure how this works for repetitive fields...
                // ... but lets fix this for date fields on the frontend:
                // 1. If the element has a class .hasDatepicker, then this is a date field
                // 2. In this case, check how many repetitions we have, and store that number in index
                // 3. Find the auxiliar hidden input on the template that we created by cloning the first repetition
                // 4. Replace the numeric part of the name on that auxiliar input (that holds the actual timestamp value); also, empty its value
                if (el.hasClass('hasDatepicker')) {
                    $this.closest('.js-wpt-field-items').find('.js-wpt-date-auxiliar').each(function(){
                        i = $(this).attr('name').match(/\[(\d+)\]/);
                        if (i) {
                            i = parseInt(i[1]);
                            if (i > index) {
                                index = i;
                            }
                        }
                    });
                    index++;
                    var el_aux = $('.js-wpt-date-auxiliar', tpl),
                    el_sel = $('select', tpl);
                    el_aux.attr('name', el_aux.attr('name').replace(/\[\d+\]/, '['+index+']')).val('');
                    if (el_sel.length > 0) {
                        el_sel.each(function(){
                            $(this).attr('name', $(this).attr('name').replace(/\[\d+\]/, '['+index+']')).val(0);
                        });
                    }
                } else {
                    $('.js-wpt-repetitive', $parent).each(function(){
                        i = $(this).attr('name').match(/\[(\d+)\](\[[a-z]+\])?$/);
                        if (i) {
                            i = parseInt(i[1]);
                            if (i > index) {
                                index = i;
                            }
                        }
                    });
                    index++;
                    el.attr('name', el.attr('name').replace(/\[\d+\]/, '['+index+']'));
                    el.attr('id', el.attr('id') + '-' + index);
                }
                el.val('');

                if ('file' == $('.js-wpt-repetitive', $parent).data('wpt-type')) {
                    $('input[type=hidden]', tpl).attr('id', wpt_name+(index)+'_hidden').attr('name', wpt_name+'['+index+']').val('');
                    file = $('[type=file]', tpl).removeAttr('disabled').show().attr('id', wpt_name+index+'_file').attr('alt', '');
                    file.attr('data-wpt-id',file.data('wpt-id').replace(/\d+_file$/, index+'_file'));

                    $('[type=button][name=switch]', tpl).remove();
                    $('img', tpl).remove();
                }

                if (el.hasClass('js-wpt-skypename')) {
                    el = $('input[type=hidden]', tpl);
                    el.attr('name', el.attr('name').replace(/\[\d+\]/, '['+index+']'));
                    el.attr('id', el.attr('id') + '-' + index);
                }

                $(this).before(tpl);

                var has_datepicker = template_element.parents('form').find('input.js-wpt-date, #cred-post-expiration-datepicker');
                if (has_datepicker.length) {
                    has_datepicker.each(function(index){
                        $(this).removeClass('hasDatepicker').parents('.wpt-repctl').find('img').remove();
                        wptDate.add($(this));
                    });
                }
				*/
            }
            wptCallbacks.addRepetitive.fire($parent);
            _toggleCtl($parent);

            return false;
        });
        // Delete field
        $('.js-wpt-field-items').on('click', '.js-wpt-repdelete', function() {
            $parent = $(this).closest('.js-wpt-field-items');
            if ($('body').hasClass('wp-admin')) {
                var $this = $(this),
				value;
                // Allow deleting if more than one field item
                if ($('.js-wpt-field-item', $parent).length > 1) {
                    var formID = $this.parents('form').attr('id');
                    $this.parents('.js-wpt-field-item').remove();
                    wptCallbacks.removeRepetitive.fire(formID);
                }
                /**
                 * if image, try delete images
				 * TODO check this, I do not like using parent() for this kind of things
                 */
                if ('image' == $this.data('wpt-type')) {
					value = $this.parent().parent().find('input').val();
                    $parent.parent().append(
                        '<input type="hidden" name="wpcf[delete-image][]" value="'
                        + value
                        + '"/>'
                       );
                }
            } else {
                if ($('.wpt-repctl', $parent).length > 1) {
                    $(this).closest('.wpt-repctl').remove();
                    wptCallbacks.removeRepetitive.fire(formID);
                }
            }
            _toggleCtl($parent);
            return false;
        });
    }
    function _toggleCtl($sortable) {
		var sorting_count;
        if ($('body').hasClass('wp-admin')) {
            sorting_count = $('.js-wpt-field-item', $sortable).length;
        } else {
			sorting_count = $('.wpt-repctl', $sortable).length;
		}
        if (sorting_count > 1) {
            $('.js-wpt-repdelete', $sortable).removeAttr('disabled').show();
            $('.js-wpt-repdrag', $sortable).css({opacity: 1, cursor: 'move'});
            if (!$sortable.hasClass('ui-sortable')) {
                $sortable.sortable({
					handle: '.js-wpt-repdrag',
                    axis: 'y',
                    cursor: 'move',
					stop: function( event, ui ) {
						$sortable.find('.js-wpt-repadd').detach().appendTo($sortable);
					}
                });
            }
        } else {
            $('.js-wpt-repdelete', $sortable).attr('disabled', 'disabled').hide();
            $('.js-wpt-repdrag', $sortable).css({opacity: 0.5, cursor: 'default'});
            if ($sortable.hasClass('ui-sortable')) {
                $sortable.sortable('destroy');
            }
        }
    }
    function _countIt(_count, id) {
        if (typeof count[id] == 'undefined') {
            count[id] = _count;
            return _count;
        }
        return ++count[id];
    }
    return {
        init: init
    };
})(jQuery);

jQuery(document).ready(wptRep.init);

var toolsetForms = toolsetForms || {};


var wptCallbacks = {};
wptCallbacks.validationInit = jQuery.Callbacks('unique');
wptCallbacks.addRepetitive = jQuery.Callbacks('unique');
wptCallbacks.removeRepetitive = jQuery.Callbacks('unique');
wptCallbacks.conditionalCheck = jQuery.Callbacks('unique');
wptCallbacks.reset = jQuery.Callbacks('unique');

jQuery(document).ready(function() {
    if (typeof wptValidation !== 'undefined') {
        wptCallbacks.validationInit.add(function() {
            wptValidation.init();
        });
    }
    if (typeof wptCond !== 'undefined') {
        wptCond.init();
    } else {
        wptCallbacks.validationInit.fire();
    }
    
});


var wptFilters = {};
function add_filter(name, callback, priority, args_num) {
    var args = _.defaults(arguments, ['', '', 10, 2]);
    if (typeof wptFilters[name] === 'undefined')
        wptFilters[name] = {};
    if (typeof wptFilters[name][args[2]] === 'undefined')
        wptFilters[name][args[2]] = [];
    wptFilters[name][args[2]].push([callback, args[3]]);
}
function apply_filters(name, val) {
    if (typeof wptFilters[name] === 'undefined')
        return val;
    var args = _.rest(_.toArray(arguments));
    _.each(wptFilters[name], function(funcs, priority) {
        _.each(funcs, function($callback) {
            var _args = args.slice(0, $callback[1]);
            args[0] = $callback[0].apply(null, _args);
        });
    });
    return args[0];
}
function add_action(name, callback, priority, args_num) {
    add_filter.apply(null, arguments);
}
function do_action(name) {
    if (typeof wptFilters[name] === 'undefined')
        return false;
    var args = _.rest(_.toArray(arguments));
    _.each(wptFilters[name], function(funcs, priority) {
        _.each(funcs, function($callback) {
            var _args = args.slice(0, $callback[1]);
            $callback[0].apply(null, _args);
        });
    });
    return true;
}

/**
 * flat taxonomies functions
 */

function showHideMostPopularTaxonomy(el)
{
    taxonomy = jQuery(el).data('taxonomy');
    jQuery('.shmpt-'+taxonomy, jQuery(el).closest('form')).toggle();
    var curr = jQuery(el).val();
    if (curr==jQuery(el).data('show-popular-text')) {
        jQuery(el).val(jQuery(el).data('hide-popular-text'));
    } else {
        jQuery(el).val(jQuery(el).data('show-popular-text'));
    }
}

function addTaxonomy(slug, taxonomy, el)
{
    var form = jQuery(el).closest('form');
    var curr = jQuery('input[name=tmp_'+taxonomy+']', form).val().trim();
    if (''==curr) {
        jQuery('input[name=tmp_'+taxonomy+']', form).val(slug);
        setTaxonomy(taxonomy, el);
    } else {
        if (curr.indexOf( slug )==-1) {
            jQuery('input[name=tmp_'+taxonomy+']', form).val(curr+','+slug);
            setTaxonomy(taxonomy, el);
        }
    }
    jQuery('input[name=tmp_'+taxonomy+']', form).val('');
}

function setTaxonomy(taxonomy, el)
{
    var form = jQuery(el).closest('form');
    var tmp_tax = jQuery('input[name=tmp_'+taxonomy+']', form).val();
    if (tmp_tax.trim()=='') return;
    var tax = jQuery('input[name='+taxonomy+']', form).val();
    var arr = tax.split(',');
    if (jQuery.inArray(tmp_tax, arr)!==-1) return;
    var toadd = (tax=='') ? tmp_tax : tax+','+tmp_tax;
    jQuery('input[name='+taxonomy+']', form).val(toadd);
    jQuery('input[name=tmp_'+taxonomy+']', form).val('');
    updateTaxonomies(taxonomy, form);
}

function updateTaxonomies(taxonomy, form)
{
    var taxonomies = jQuery('input[name='+taxonomy+']', form).val();
    jQuery('div.tagchecklist-'+taxonomy, form).html('');
    if (!taxonomies||(taxonomies&&taxonomies.trim()=='')) return;
    var toshow = taxonomies.split(',');
    var str = '';
    for (var i=0;i<toshow.length;i++) {
        var sh = toshow[i].trim();
        str += '<span><a href="#" class=\'ntdelbutton\' data-wpcf-i=\''+i+'\' id=\'post_tag-check-num-'+i+'\'>X</a>&nbsp;'+sh+'</span>';
    }
    jQuery('div.tagchecklist-'+taxonomy, form).html(str);
    jQuery('div.tagchecklist-'+taxonomy+' a', form).bind('click', function() {
        jQuery('input[name='+taxonomy+']', form).val('');
        del = jQuery(this).data('wpcf-i');
        var values = '';
        for(i=0;i<toshow.length;i++ ) {
            if ( del == i ) {
                continue;
            }
            if ( values ) {
                values += ',';
            }
            values += toshow[i];
        }
        jQuery('input[name='+taxonomy+']', form).val(values) ;
        updateTaxonomies(taxonomy, form);
        return false;
    });
}

function initTaxonomies(values, taxonomy, url, fieldId)
{
    form = jQuery('#'+fieldId.replace(/_field_\d+$/, '' ) ).closest('form');
    jQuery('div.tagchecklist-'+taxonomy).html(values);
    jQuery('input[name='+taxonomy+']').val(values);
    updateTaxonomies(taxonomy, form);
    jQuery('input[name=tmp_'+taxonomy+']').autocomplete (
        url+'/external/autocompleter.php',
        {
            delay:10,
            minChars:2,
            matchSubset:1,
            matchContains:1,
            cacheLength:10,
            formatItem:formatItem,
            onItemSelect:onSelectItem,
            autoFill:true
        }
    );
}

toolsetForms.CRED_taxonomy = function () {
    
    var self = this;
    
    self.init = function () {
        self._new_taxonomy = new Array();
        jQuery(document).ready(self._document_ready);
    }
    
    self._document_ready = function () {
        self._initialize_taxonomy_buttons();
        self._initialize_hierachical();
    }
    
    self._initialize_hierachical = function () {
        self._fill_parent_drop_down()
    }
    
    self._fill_parent_drop_down = function () {
        jQuery('select.js-taxonomy-parent').each ( function () {
            var select = jQuery(this);
            
            // remove all the options
            jQuery(this).find('option').each (function () {
                if (jQuery(this).val() != '-1') {
                    jQuery(this).remove();
                }
            })

            var taxonomy = jQuery(this).data('taxonomy');
            
            // Copy all the checkbox values if it's checkbox mode
            jQuery('input[name="' + taxonomy + '\[\]"]').each (function () {
                var id = jQuery(this).attr('id');
                var label = jQuery(this).next('label');
                select.append('<option value="' + jQuery(this).val() + '">' + label.text() + '</option>');
            })
            
            // Copy all the select option values if it's select mode
            jQuery('select[name="' + taxonomy + '\[\]"]').find('option').each (function () {
                var id = jQuery(this).val();
                var text = jQuery(this).text();
                select.append('<option value="' + id + '">' + text + '</option>');
            })
            
            
        });
        
    }
    
    self._initialize_taxonomy_buttons = function () {
        // replace the taxonomy button placeholders with the actual buttons.
        jQuery('.js-taxonomy-button-placeholder').each(function () {
            var taxonomy = jQuery(this).data('taxonomy');
            var button = jQuery('[name="sh_' + taxonomy + '"]');
            if (button.length) {
                button.detach();
                jQuery(this).replaceWith(button);
                button.show();
                
                // move anything else that should be moved with the button
                var selector = button.data('after-selector');
                if (selector.length) {
                    var position = button;
                    jQuery(selector).each( function () {
                        jQuery(this).detach();
                        jQuery(this).insertAfter(button);
                        position = jQuery(this);
                    });
                }
            }
        });
    }    
    
    self.add_new_show_hide = function ( taxonomy, button) {
        jQuery('[name="new_tax_text_' + taxonomy + '"]').toggle();
        jQuery('[name="new_tax_button_' + taxonomy + '"]').toggle();
        jQuery('[name="new_tax_select_' + taxonomy + '"]').toggle();
    }
    
    self.add_taxonomy = function ( taxonomy, button) {
        var new_taxonomy = jQuery('[name="new_tax_text_' + taxonomy + '"]').val();
        new_taxonomy = new_taxonomy.trim()
        if (new_taxonomy == '') {
            return;
        }

        // make sure we don't already have a taxonomy with the same name.
        var exists = false;
        jQuery('input[name="' + taxonomy + '\[\]"]').each (function () {
            var id = jQuery(this).attr('id');
            var label = jQuery('label[for="' + id + '"]');
            
            if (new_taxonomy == label.text()) {
                exists = true
                self._flash_it(label);
            }
        });

        jQuery('select[name="' + taxonomy + '\[\]"]').find('option').each (function () {
            if (new_taxonomy == jQuery(this).text()) {
                exists = true;
                self._flash_it(jQuery(this));
            }
        });
        
        if (exists) {
            jQuery('[name="new_tax_text_' + taxonomy + '"]').val('');
            return;
        }

        var parent = jQuery('[name="new_tax_select_' + taxonomy + '"]').val();
        var add_position = null;
        var margin_left = '';
        var add_before = true;
            
        if (jQuery('input[name="' + taxonomy + '\[\]"]').length){
            // find the last checkbox
            
            var first_checkbox = jQuery('input[name="' + taxonomy + '\[\]"][data-parent="' + parent + '"]:first');
            if (first_checkbox.length == 0) {
                // there are no existing children
                first_checkbox = jQuery('input[name="' + taxonomy + '\[\]"][value="' + parent + '"]');
                margin_left = parseInt(first_checkbox.css('marginLeft'));
                margin_left += 15;
                margin_left = margin_left.toString() + 'px';
                add_before = false;
                add_position = first_checkbox.next();
            } else {
                margin_left = first_checkbox.css('marginLeft');
                add_position = first_checkbox;
            }
            
            
            if (add_position) {
                // add the checkbox
                var style = '';
                if (margin_left) {
                    style = ' style="margin-left:' + margin_left + '"'
                }
                var new_checkbox = '<input type="checkbox" name="' + taxonomy + '[]" checked="checked" value="' + new_taxonomy + '"' + style + '></input>&nbsp;<label>' + new_taxonomy + '</label>';
                if (add_before) {
                    jQuery(new_checkbox + '<br />').insertBefore(add_position);
                } else {
                    jQuery('<br />' + new_checkbox).insertAfter(add_position);
                }
                
            }
        } else {
            // Select control
            var indent = '';
            var first_option = jQuery('select[name="' + taxonomy + '\[\]"]').find('option[data-parent="' + parent + '"]:first');
            if (first_option.length == 0) {
                // there a no children of this parent
                first_option = jQuery('select[name="' + taxonomy + '\[\]"]').find('option[value="' + parent + '"]:first');
                add_before = false;
                var label = first_option.text();
                for (var i = 0; i < label.length; i++) {
                    if (label[i] == '\xA0') {
                        indent += '\xA0';
                    } else {
                        break;
                    }
                }
                indent += '\xA0';
                indent += '\xA0';
                add_position = first_option;

            } else {
                add_position = first_option;
                var label = first_option.text();
                for (var i = 0; i < label.length; i++) {
                    if (label[i] == '\xA0') {
                        indent += '\xA0';
                    } else {
                        break;
                    }
                }
            }
            
            if (add_position) {
                var new_option = '<option value="' + new_taxonomy + '" selected>' + indent + new_taxonomy + '</option>';
                if (add_before) {
                    jQuery(new_option).insertBefore(add_position);
                } else {
                    jQuery(new_option).insertAfter(add_position);
                }
            }
        }
        
        
        
        
        self._update_hierachy(taxonomy, new_taxonomy);
        
        jQuery('[name="new_tax_text_' + taxonomy + '"]').val('');
        
        self._fill_parent_drop_down();
        
        
    }
    
    self._update_hierachy = function (taxonomy, new_taxonomy) {
        var new_taxonomy_input = jQuery('input[name="' + taxonomy + '_hierarchy"]');
        if (!new_taxonomy_input.length) {
            // add a hidden field for the hierarchy
            jQuery('<input name="' + taxonomy + '_hierarchy" style="display:none" type="hidden">').insertAfter(jQuery('[name="new_tax_text_' + taxonomy + '"]'));
            new_taxonomy_input = jQuery('input[name="' + taxonomy + '_hierarchy"]');
        }
        
        var parent = jQuery('[name="new_tax_select_' + taxonomy + '"]').val();
        self._new_taxonomy.push(parent + ',' + new_taxonomy);
        
        var value = '';
        for (var i = 0; i < self._new_taxonomy.length; i++) {
            value += '{' + self._new_taxonomy[i] + '}';
        }
        new_taxonomy_input.val(value);
        
    }
    
    self._flash_it = function (element) {
        element.fadeOut(300).fadeIn(300).fadeOut(300).fadeIn(300);
    }
    
    self.init();
    
}

toolsetForms.cred_tax = new toolsetForms.CRED_taxonomy();


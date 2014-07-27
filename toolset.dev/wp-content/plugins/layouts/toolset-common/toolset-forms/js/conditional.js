/**
 * @see WPToolset_Forms_Conditional (classes/conditional.php)
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/trunk/toolset-forms/js/conditional.js $
 * $LastChangedDate: 2014-07-24 04:32:47 +0000 (Thu, 24 Jul 2014) $
 * $LastChangedRevision: 25256 $
 * $LastChangedBy: bruce $
 *
 */
var wptCondTriggers = {};
var wptCondFields = {};
var wptCondCustomTriggers = {};
var wptCondCustomFields = {};
var wptCondDebug = false;

var wptCond = (function($) {

    function init()
    {
        _.each(wptCondTriggers, function(triggers, formID) {
            _.each(triggers, function(fields, trigger) {
                var $trigger = _getTrigger(trigger, formID);
                _bindChange(formID, $trigger, function(e) {
                    _check(formID, fields);
                });
                _check(formID, fields); // Check conditional on init
            });
        });
        _.each(wptCondCustomTriggers, function(triggers, formID) {
            _.each(triggers, function(fields, trigger) {
                var $trigger = _getTrigger(trigger, formID);
                _bindChange(formID, $trigger, function(e) {
                    _custom(formID, fields);
                });
            });
        });
        // Fire validation after init conditional
        wptCallbacks.validationInit.fire();
		// check initial custom
		_init_custom();
    }

    function _getTrigger(trigger, formID)
    {
        var $trigger = $('[data-wpt-name="'+ trigger + '"]', formID);
        /**
         * wp-admin
         */
        if ( $('body').hasClass('wp-admin') ) {
            trigger = trigger.replace( /wpcf\-/, 'wpcf[' ) + ']';
            $trigger = $('[data-wpt-name="'+ trigger + '"]', formID);

        }
        /**
         * handle skype field
         */
        if ( $trigger.length < 1 ) {
            $trigger = $('[data-wpt-name="'+ trigger + '[skypename]"]', formID);
        }
        /**
         * handle date field
         */
        if ( $trigger.length < 1 ) {
            $trigger = $('[data-wpt-name="'+ trigger + '[datepicker]"]', formID);
        }
		/**
         * handle checkboxes and multiselect
         */
        if ( $trigger.length < 1 ) {
            $trigger = $('[data-wpt-name="'+ trigger + '[]"]', formID);
        }
        /**
         * handle select
         */
        if ( $trigger.length > 0 && 'option' == $trigger.data('wpt-type') ) {
            $trigger = $trigger.parent();
        }
        /**
         * debug
         */
        if ( wptCondDebug ) {
            console.info('_getTrigger');
            console.log( 'trigger', trigger );
            console.log( '$trigger', $trigger );
            console.log( 'formID', formID );
        }
        return $trigger;
    }

    function _getTriggerValue($trigger, formID)
    {
        if ( wptCondDebug ) {
            console.info('_getTriggerValue');
            console.log( '$trigger', $trigger );
            console.log( '$trigger.type', $trigger.data('wpt-type') );
        }
        // Do not add specific filtering for fields here
        // Use add_filter() to apply filters from /js/$type.js
        var val = null;
		// NOTE we might want to set val = ''; by default?
        switch( $trigger.data('wpt-type') ) {
            case 'radio':
            case 'radios':
                radio = $('[name="' + $trigger.attr('name') + '"]:checked', formID);
				// If no option was selected, the value should be empty
				val = '';
                if ( 'undefined' == typeof( radio.data('types-value' ) ) ) {
                    val = radio.val();
                } else {
                    val = radio.data('types-value');
                }
                break;
            case 'select':
				option = $('[name="' + $trigger.attr('name') + '"] option:selected', formID);
				// If no option was selected, the value should be empty
				val = '';
                if ( wptCondDebug ) {
                    console.log( 'option', option );
                }
				if ( option.length == 1 ) {
					if ( 'undefined' == typeof( option.data('types-value' ) ) ) {
						val = option.val();
					} else {
						val = option.data('types-value');
					}
				} else if ( option.length > 1 ) {
					val = [];
					option.each(function() {
						if ( 'undefined' == typeof( $(this).data('types-value' ) ) ) {
							val.push($(this).val());
						} else {
							val.push($(this).data('types-value'));
						}
					});
				}
                break;
            case 'checkbox':
                var $trigger_checked = $trigger.filter(':checked');
				// If no checkbox was checked, the value should be empty
				val = '';
				if ( $trigger_checked.length == 1 ) {
                    val = $trigger_checked.val();
                } else if ( $trigger_checked.length > 1 ) {
					val = [];
					$trigger_checked.each(function() {
						val.push($(this).val());
					});
				}
                break;
            default:
                val = $trigger.val();
        }
        if ( wptCondDebug ) {
            console.log( 'val', val );
        }
        return val;
    }

    function _getAffected(affected, formID)
    {
        if ( wptCondDebug ) {
            console.info('_getAffected');
        }
        var $el = $('[data-wpt-id="' + affected + '"]', formID);
        if ( $('body').hasClass('wp-admin') ) {
            $el = $el.closest('.wpt-field');
            if ($el.length < 1) {
                $el = $('#' + affected, formID).closest('.form-item');
            }
        } else {
			if ( $el.length < 1 ) {
				/**
				 * get pure field name, without form prefix
				 */
				re = new RegExp(formID+'_');
				name = '#'+affected;
				name = name.replace( re, '' );
				/**
				 * try get element
				 */
				$obj = $('[data-wpt-id="' + affected + '_file"]', formID);
				/**
				 * handle by wpt field name
				 */
				if ( $obj.length < 1 ) {
					$obj = $('[data-wpt-name="'+ name + '"]', formID);
				}
				/**
				 * handle date field
				 */
				if ( $obj.length < 1 ) {
					$obj = $('[data-wpt-name="'+ name + '[datepicker]"]', formID);
				}
				/**
				 * handle skype field
				 */
				if ( $obj.length < 1 ) {
					$obj = $('[data-wpt-name="'+ name + '[skypename]"]', formID);
				}
				/**
				 * handle checkboxes field
				 */
				if ( $obj.length < 1 ) {
					$obj = $('[data-wpt-name="'+ name + '[]"]', formID);
				}
				/**
				 * catch by id
				 */
				if ($obj.length < 1) {
					$obj = $('#' + affected, formID);
				}
			
			} else {
				$obj = $el;
			}
            /**
             * finally catch parent: we should have catched the $obj
             */
            if ($obj.length > 0) {
                $el = $obj.closest('.js-wpt-conditional-field');
				if ( $el.length < 1 ) {
					$el = $obj.closest('.cred-field');// This for backwards compatibility
					if ( $el.length < 1 ) {
						$el = $obj.closest('.js-wpt-field-items');
					}
				}
            }
            /**
             * debug
             */
            if ( wptCondDebug ) {
                console.log('$obj', $obj);
            }
        }
        if ($el.length < 1) {
            $el = $('#' + affected, formID);
        }
        /**
         * generic conditional field
         */
        if ($el.length < 1) {
            $el = $('.cred-group.' + affected, formID);
        }
        /**
         * debug
         */
        if ( wptCondDebug ) {
            console.log('affected', affected);
            console.log('$el', $el);
        }
        return $el;
    }

    function _checkOneField(formID, field, next)
    {
        var __ignore = false;
        var c = wptCondFields[formID][field];
        var passedOne = false, passedAll = true, passed = false;
        var $trigger;
        _.each(c.conditions, function(data) {
            if (__ignore) {
                return;
            }
            $trigger = _getTrigger(data.id, formID);
            var val = _getTriggerValue($trigger, formID);
            if ( wptCondDebug ) {
                console.log( 'formID', formID );
                console.log( '$trigger', $trigger );
                console.log('val', 1, val);
            }

			var field_type = $trigger.data('wpt-type');
			if ( data.type == 'date' ) {
				field_type = 'date';
			}
            val = apply_filters('conditional_value_' + field_type, val, $trigger);
            if ( wptCondDebug ) {
                console.log('val', 2, val);
            }
            do_action('conditional_check_' + data.type, formID, c, field);
            var operator = data.operator, _val = data.args[0];
            /**
             * handle types
             */
			 // Not needed anymore
			 // NEVER Date.parse timestamps coming from adodb_xxx functions
			 /*
            switch(data.type) {
                case 'date'://alert(_val);alert(val);
                    if ( _val ) {//alert('this is _val ' + _val);
                    //    _val = Date.parse(_val);//alert('this is _val after parse ' + _val);
                    }//alert('val is ' + val);
                    //val = Date.parse(val);//alert('parsed val is ' + val);
                    break;
            }
			*/
            if ('__ignore' == val ) {
                __ignore = true;
                return;
            }
            /**
             * debug
             */
            if ( wptCondDebug ) {
                console.log('val', 3, val);
                console.log('_val', _val);
            }
            /**
             * for __ignore_negative set some dummy operator
             */
            if ( 0 && '__ignore_negative' == val ) {
                operator = '__ignore';
            }
			
			if ( $.isArray( val ) ) {
				// If the selected value is an array, we just can check == and != operators, which means in_array and not_in_array
				// We return false in any other scenario
				switch (operator) {
					case '===':
					case '==':
					case '=':
						passed_single = jQuery.inArray( _val, val ) !== -1;
						break;
					case '!==':
					case '!=':
					case '<>':
						passed_single = jQuery.inArray( _val, val ) == -1;
						break;
					default:
						passed_single = false;
						break;
				}
			} else {
				// Note: we can use parseInt here although we are dealing with extended timestamps coming from adodb_xxx functions
				// Because javascript parseInt can deal with integers up to ±1e+21
				switch (operator) {
					case '===':
					case '==':
					case '=':
						if ( $.isArray( val ) ) {
							
						} else {
							passed_single = val == _val;
						}
						break;
					case '!==':
					case '!=':
					case '<>':
						passed_single = val != _val;
						break;
					case '>':
						passed_single = parseInt(val) > parseInt(_val);
						break;
					case '<':
						passed_single = parseInt(val) < parseInt(_val);
						break;
					case '>=':
						passed_single = parseInt(val) >= parseInt(_val);
						break;
					case '<=':
						passed_single = parseInt(val) <= parseInt(_val);
						break;
					case 'between':
						passed_single = parseInt(val) > parseInt(_val) && parseInt(val) < parseInt(data.args[1]);
						break;
					default:
						passed_single = false;
						break;
				}
			}
            if (!passed_single) {
                passedAll = false;
            } else {
                passedOne = true;
            }
        });

        if (c.relation === 'AND' && passedAll) {
            passed = true;
        }
        if (c.relation === 'OR' && passedOne) {
            passed = true;
        }
        /**
         * debug
         */
        if ( wptCondDebug ) {
            console.log('passedAll', passedAll, 'passedOne', passedOne, 'passed', passed, '__ignore', __ignore);
            console.log('field', field);
        }
        if (!__ignore) {
            _showHide(passed, _getAffected(field, formID));
        }
		// No need to set a timeout anymore
        //if ( $trigger.length && next && $trigger.hasClass('js-wpt-date' ) ) {
        //    setTimeout(function() {
        //        _checkOneField( formID, field, false );
        //    }, 200);
        //}
    }

    function _check(formID, fields)
    {
        if ( wptCondDebug ) {
            console.info('_check');
        }
        _.each(fields, function(field) {
            _checkOneField(formID, field, true);
        });
        wptCallbacks.conditionalCheck.fire(formID);
    }

    function _bindChange(formID, $trigger, func)
    {
        // Do not add specific binding for fields here
        // Use add_action() to bind change trigger from /js/$type.js
        // if not provided - default binding will be performed
        var binded = do_action('conditional_trigger_bind_' + $trigger.data('wpt-type'), $trigger, func, formID);
        if (binded) {
            return;
        }
        /**
         * debug
         */
        if ( wptCondDebug ) {
            console.info('_bindChange');
            console.log('$trigger', $trigger);
            console.log('wpt-type', $trigger.data('wpt-type'));
        }
        switch( $trigger.data('wpt-type') ) {
            case 'checkbox':
                $trigger.on('click', func);
                break;
            case 'radio':
            case 'radios':
                $('[name="' + $trigger.attr('name') + '"]').on('click', func);
                break;
            case 'select':
                $trigger.on('change', func);
                break;
			case 'date':
                $trigger.on('change', func);
                break;
            default:
                if ( $trigger.hasClass('js-wpt-colorpicker') ) {
                    $trigger.data('_bindChange', func)
                }
                $($trigger).on('blur', func);
        }
    }

    function _custom(formID, fields)
    {
        var data = {action: 'wptoolset_custom_conditional', 'conditions': {}, 'values': {}, 'field_types': {}};
        _.each(fields, function(field) {
            var c = wptCondCustomFields[formID][field];
            data.conditions[field] = c.custom;
            _.each(c.triggers, function(t) {
                var $trigger = _getTrigger(t);
                data.values[t] = _getTriggerValue($trigger);
                data.field_types[t] = $trigger.data('wpt-type');
            });
        });
        $.post(wptConditional.ajaxurl, data, function(res) {
            _.each(res.passed, function(affected) {
                _showHide(true, _getAffected(affected, formID));
            });
            _.each(res.failed, function(affected) {
                _showHide(false, _getAffected(affected, formID));
            });
            wptCallbacks.conditionalCheck.fire(formID);
        }, 'json').fail(function(data) {
            //alert(data.responseText);
        });
    }

    function _showHide(show, $el)
    {
        if ( wptCondDebug ) {
            console.info('_showHide');
            console.log(show, $el);
        }
        if (show) {
            $el.slideDown().removeClass('js-wpt-remove-on-submit js-wpt-validation-ignore');
        } else {
            $el.slideUp().addClass('js-wpt-remove-on-submit js-wpt-validation-ignore');
        }
    }

    function ajaxCheck(formID, field, conditions)
    {
        var values = {};
        _.each(conditions.conditions, function(c) {
            var $trigger = _getTrigger(c.id, formID);
            values[c.id] = _getTriggerValue($trigger);
        });
        var data = {
            'action': 'wptoolset_conditional',
            'conditions': conditions,
            'values': values
        };
        $.post(wptConditional.ajaxurl, data, function(passed) {
            _showHide(passed, _getAffected(field, formID));
            wptCallbacks.conditionalCheck.fire(formID);
        }).fail(function(data) {
            //alert(data);
        });
    }

    function addConditionals(data)
    {
        _.each(data, function(c, formID) {
            if (typeof c.triggers != 'undefined'
                    && typeof wptCondTriggers[formID] != 'undefined') {
                _.each(c.triggers, function(fields, trigger) {
                    wptCondTriggers[formID][trigger] = fields;
                    var $trigger = _getTrigger(trigger, formID);
                    _bindChange(formID, $trigger, function() {
                        _check(formID, fields);
                    });
                });
            }
            if (typeof c.fields != 'undefined'
                    && typeof wptCondFields[formID] != 'undefined') {
                _.each(c.fields, function(conditionals, field) {
                    wptCondFields[formID][field] = conditionals;
                });
            }
            if (typeof c.custom_triggers != 'undefined'
                    && typeof wptCondCustomTriggers[formID] != 'undefined') {
                _.each(c.custom_triggers, function(fields, trigger) {
                    wptCondCustomTriggers[formID][trigger] = fields;
                    var $trigger = _getTrigger(trigger, formID);
                    _bindChange(formID, $trigger, function() {
                        _custom(formID, fields);
                    });
                });
            }
            if (typeof c.custom_fields != 'undefined'
                    && typeof wptCondCustomFields[formID] != 'undefined') {
                _.each(c.custom_fields, function(conditionals, field) {
                    wptCondCustomFields[formID][field] = conditionals;
                });
            }
        });
    }
	
	function _init_custom() {
		$('.js-wpt-field-items').each( function () {
			var init_custom = $(this).data('initial-conditional');
			if (init_custom) {
				var field = $(this).closest('.cred-field');
				if (field.length) {
					_showHide(false, field);
				}
			}
		})
	}

    return {
        init: init,
        ajaxCheck: ajaxCheck,
        addConditionals: addConditionals
    };

})(jQuery);


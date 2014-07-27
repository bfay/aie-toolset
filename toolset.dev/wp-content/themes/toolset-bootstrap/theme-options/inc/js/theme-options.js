jQuery(function($){

	/*
	 * jQuery tipTip tooltips for theme options table
	 */

	var $formTable = $('#wp-bootstrap-theme-options .form-table');
	var $el;
	var tableTdWidth =  $formTable.find('td').outerWidth();

	$formTable.find('tbody th').mouseenter(function(){
		$el = $(this).next().find('.info').text();
	});
	$formTable.find('tbody td').mouseenter(function(){
		$el = $(this).find('.info').text();
	})

	$formTable.find('th').tipTip({
		maxWidth: 'auto',
		edgeOffset: 30,
		dalay: 200,
		defaultPosition: 'right',
		content: function() {
			return $el;
		}
	});

	$formTable.find('td').tipTip({
		maxWidth: 'auto',
		edgeOffset: -tableTdWidth+30,
		dalay: 200,
		defaultPosition: 'right',
		content: function() {
			return $el;
		}
	});

	/*
	 * Enable/disable all checboxes
	 */

	var $generalOptionsHeader = $('#general-options-header thead').clone();
	$formTable.append($generalOptionsHeader);
	$('#general-options-header').remove();

	$('.button.toggle-all').click(function(){
		var $select = $(this).prev('select');
		var $checkboxes = $(this).closest('table').find('input[type=checkbox]');
		if ($select.val() === 'Enable all') {			
			$checkboxes.prop('checked', true);
		}
		if ($select.val() === 'Disable all') {
			$checkboxes.prop('checked', false);
		}

	});


});
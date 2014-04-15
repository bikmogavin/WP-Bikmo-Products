
jQuery.noConflict();

jQuery(function() {

	var timeout = false;

	var form = jQuery('#product-search-form');

	var search = jQuery('#product-search');

	var limit = jQuery('#limit');

	var ajaxLoader = jQuery('#ajax-loader');

	var submitData = function() {
		jQuery.ajax({
			url: form.attr('action'),
			type: 'POST',
			data: form.serialize(),
			beforeSend: function() {
				ajaxLoader.addClass('active');
			},
			success: function(response) {
				jQuery('#search-results').html(response);
			},
			complete: function() {
				ajaxLoader.removeClass('active');
			}
		});
	};

	form.submit(function(e) {
		e.preventDefault();
	});

	limit.change(submitData);

	search.keyup(function() {
		clearTimeout(timeout);
		timeout = setTimeout(submitData, 500);

	});
});
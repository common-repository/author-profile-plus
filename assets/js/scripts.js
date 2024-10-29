/*!
 *  Author Profile Plus scripts
 */
 
jQuery(function($) {

	// Move Author Profile Plus fields to the top of the profile page
	$('#author-profile-plus-wrapper').remove().prependTo('form#your-profile');

	// Ensure that the profile form can handle file uploads
	$('form#your-profile').attr( 'enctype', 'multipart/form-data' );
	
	/*
	// Save author fields via AJAX
	$('#save-author-fields').bind('click', function(e) {
	
		var button = $(this);
		
		// Halt
		e.preventDefault();
				
		// Do AJAX request
		$.ajax({
		
			type: 'POST',
			dataType: 'json',
			url: AJAX.url,
			data: $('#author-profile-plus').find('input,textarea').serialize(),
			beforeSend: function() {
				button.attr('disabled','disabled').toggleClass('disabled');
				$('.author-message').empty().append('Loading...');
			},
			success: function(result) {
				$('.author-message').empty().append('Successfully saved author fields!');
			},
			error: function() {
				$('.author-message').empty().append('Could not save author fields. Please try again.');
			},
			complete: function() {
				button.removeAttr('disabled').toggleClass('disabled');
			}

		});
	
	});
	*/
	
});
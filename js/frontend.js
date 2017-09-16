jQuery(document).ready(function($) {
	$('.ajax-form').on('submit', function(e) {
		e.preventDefault();
		$.ajax({
			type: 'post',
			url: ajax.url,
			//dataType: 'json',
			data: { 
				action:      'ae_action', 
				name:        $( '#name' ).val(), 
				message:     $( '#message' ).val(),
				captcha:     $( '#captcha' ).val(),
				from:        $( '#from' ).val(),
				subject:     $( '#subject' ).val(),
				_ajax_nonce: ajax.nonce
			},
			beforeSend: function() {
				$('#load').appendTo('#load').fadeIn('fast');
			},
			success: function(html){ 
				$('#load').appendTo('#load').fadeOut('slow'); 
				$('#response').html(html).show();
				$('.alert-close').click(function(e){
					e.preventDefault();
					$('#response').hide(); 
				}); 
			},
			error: function(xhr){
				console.log('Error: ' + xhr.responseCode);
			}	     
		}); //close jQuery.ajax
	});
});
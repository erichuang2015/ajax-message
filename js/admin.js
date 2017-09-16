jQuery(document).ready(function($) {

	$(".tab-links a").click(function(e){
		e.preventDefault();
		var tabval = $(this).attr('href');
		$(".tab-content " + tabval).show().siblings().hide();
		$(this).parent().addClass("active").siblings().removeClass("active");
	});

	$('.alert-close').click(function(e){
		e.preventDefault();
		$(this).parent().addClass("closed"); 
	}); 

	// validate email
	function isEmail(email) {
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test(email);
	}

	$('.email').blur(function(){
	    (!$(this).val()) ? $(this).addClass('invalid') : $(this).removeClass('invalid');
	    if(!isEmail( $('.email').val())) { 
	    	$(this).addClass('invalid');
	    } 
	});

	$('.submit').blur(function(){
	    (!$(this).val()) ? $(this).addClass('invalid') : $(this).removeClass('invalid');
	});
	$('.success').blur(function(){
	    (!$(this).val()) ? $(this).addClass('invalid') : $(this).removeClass('invalid');
	});
	$('.error').blur(function(){
	    (!$(this).val()) ? $(this).addClass('invalid') : $(this).removeClass('invalid');
	});
	$('.from').blur(function(){
	    (!$(this).val()) ? $(this).addClass('invalid') : $(this).removeClass('invalid');
	});
	$('.subj').blur(function(){
	    (!$(this).val()) ? $(this).addClass('invalid') : $(this).removeClass('invalid');
	});
	
	//replace <> to < in <pre>
	$('.post pre').each(function() {
   		var text = $(this).text();
    	$(this).text(text.replace('<>', '<')); 
	});
});
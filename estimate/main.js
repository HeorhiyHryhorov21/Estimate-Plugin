jQuery(document).ready(function() {
	jQuery('[data-toggle="tooltip"]').mouseenter(function(){
    var that = jQuery(this)
    that.tooltip('show');
    setTimeout(function(){
        that.tooltip('hide');
    }, 3000);
});

	jQuery('[data-toggle="tooltip"]').mouseleave(function(){
	    jQuery(this).tooltip('hide');
	});

	jQuery('#prst_select').change(function() {
		if(jQuery('#prst_select').attr("value") != "0"){
			jQuery('.add_new_preset').slideUp('slow');
			jQuery('[name="add_save"]').hide();
			jQuery('[name="add"]').css('display', 'block');
		} else 

		if (jQuery('#prst_select').attr("value") == "0") {
			jQuery('.add_new_preset').slideDown('slow');
			jQuery('[name="add_save"]').show();
			jQuery('[name="add"]').css('display', 'none');
		}
	});

	 jQuery('#timepicker1').timepicker({
                template: false,
                showMeridian: false,
                maxHours: 72
	 });

	
});
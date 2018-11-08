jQuery(document).ready(function() {
	jQuery('[data-toggle="tooltip"]').mouseenter(function(){
    var that = jQuery(this)
    that.tooltip('show');
    setTimeout(function(){
        that.tooltip('hide');
    }, 2000);
});

	jQuery('[data-toggle="tooltip"]').mouseleave(function(){
	    jQuery(this).tooltip('hide');
	});

	

});
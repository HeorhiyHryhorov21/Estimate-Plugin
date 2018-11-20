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
        maxHours: 72,
        defaultTime: '00:00',
	});

	var est_count = 1;
	jQuery('#add').click(function(e){
		e.preventDefault();
		var prst_select = jQuery('#prst_select').val();
		var prst_time = jQuery('#prst_select > option:selected').next().val();
		jQuery('.table > tbody').append('<tr><td id="number-col">'+est_count+'</td><td><p>'+prst_select+'</p></td><td>'+prst_time+'</td><td><p hidden>'+prst_time+'</p><span class="dashicons dashicons-trash"></span></td></tr>');
		++est_count;
		setTimeout(function(){
        	jQuery(".modal").modal('hide');
    	}, 200);
  	});

  	jQuery('#add_save').click(function(e){
		e.preventDefault();
		var prst_title = jQuery('#prst_title').val();
		var prst_time = jQuery('#timepicker1').val();
		if (jQuery('#prst_title').val() != '' && jQuery('#timepicker1').val() != '') {
		jQuery('.table > tbody').append('<tr><td id="number-col">'+est_count+'</td><td><p>'+prst_title+'</p></td><td>'+prst_time+'</td><td><p hidden>'+prst_time+'</p><span class="dashicons dashicons-trash"></span></td></tr>');
		++est_count;

		ajax_data = {
			action: 'add_save_preset',
			prst_title: prst_title,
			prst_time: prst_time
		}
     	jQuery.post(js_object.ajax_url, ajax_data, function(data) {
     		console.log(data);
     	});
     	jQuery('#prst_title').val('');
     	jQuery('#timepicker1').val('0:00');
     	setTimeout(function(){
        	jQuery(".modal").modal('hide');
    	}, 300);
     }
  	});

	jQuery(document).on('click', 'span.dashicons-trash', function(){
		est_name = jQuery(this).closest('tr').find('p').html().replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '');		
		jQuery(this).closest('tr').hide('slow', function(){jQuery(this).closest('tr').remove(); });
		if(est_count > 0) {
			--est_count;
		} else {++est_count}
  	});

  	jQuery('#crt_est').click(function(e){
		e.preventDefault();
		var est_title = jQuery('#est_title').val();
		var est_rate = jQuery('#rate').val();
		var est_items = jQuery('.table.table-striped > tbody').html().trim();
		est_items = est_items.replace(/<tr>[\s\S]*?<\/tr>/, '');
		est_items = est_items.replace(/<span class="dashicons dashicons-trash">[\s\S]*?<\/span>/g, '');
		estimate = {
			action: 'add_estimate',
			est_title: est_title,
			est_rate: est_rate,
			est_items: est_items
		}
     	jQuery.post(js_object.ajax_url, estimate, function(data) {
     		console.log(data);
     	});

     	jQuery('<div class="est_success">Estimate '+est_title+' created</div>').insertBefore('.table').delay(3000).fadeOut();
  	});


	jQuery('a.print').click(function(e){
		e.preventDefault();
		jQuery(".form-group").print();
	});
	/*jQuery('span.print').click(function(e){
		e.preventDefault();
		jQuery(".form-group").print();
	});*/
});

/*var ajax_data = {
			action: 'delete_table_item',
			name: est_name
		}
     	jQuery.post(js_object.ajax_url, ajax_data, function(data) {
     		console.log(data);
     	});*/
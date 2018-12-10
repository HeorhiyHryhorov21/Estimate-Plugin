jQuery(document).ready(function() {
	jQuery('[data-toggle="tooltip"]').mouseenter(function(){
    var that = jQuery(this)
    that.tooltip('show');
    setTimeout(function(){
        that.tooltip('hide');
    }, 3000);
});

    jQuery('input#timepicker1').keypress(function(e) {
        e.preventDefault();
    });

    jQuery('input[type=number]').focus(function (e) {
        jQuery(this).on('mousewheel.disableScroll', function (e) {
            e.preventDefault();
        });
    });
    jQuery('input[type=number]').blur(function (e) {
        jQuery(this).off('mousewheel.disableScroll')
    });

	jQuery('[data-toggle="tooltip"]').mouseleave(function(){
	    jQuery(this).tooltip('hide');
	});

	jQuery('#prst_select').change(function() {
		if(jQuery('#prst_select').attr("value") != "0"){
			jQuery('.add_new_preset').slideUp('slow');
            jQuery('[name="add_save"]').hide();
            jQuery('[name="add_custom"]').hide();
			jQuery('[name="add"]').css('display', 'block');
		} else 

		if (jQuery('#prst_select').attr("value") == "0") {
			jQuery('.add_new_preset').slideDown('slow');
            jQuery('[name="add_custom"]').show();
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

	function timeToFloat(time) {
	  var hoursMinutes = time.split(/[.:]/);
	  var hours = parseInt(hoursMinutes[0], 10);
	  var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
	  return hours + minutes / 60;
	}

	var est_count = 1;
	var est_time_sum = 0;
	var time_id = 1;
	var cost_id = 1;
	var title_id = 1;
	var row_id = 1;
	jQuery('#add').click(function(e){
		e.preventDefault();
		var prst_select = jQuery('#prst_select').val();
		var prst_time = jQuery('#prst_select > option:selected').next().val();
		var prst_id = jQuery('#prst_select > option:selected').next().attr('data-id')
		var est_rate = jQuery('input#rate').val();

		if (jQuery('.table.table-striped > tbody > tr').length >= 1) {
			est_count = 1 + +(jQuery('.table.table-striped > tbody > tr:last').find('td#number-col').text());
		} else {
			est_count = 1;
		}

		jQuery('.table > tbody').append('<tr><td id="number-col">'+est_count+'</td><td id="title'+title_id+'"><p>'+prst_select+'</p></td><td id="time'+time_id+'">'+prst_time+'</td><td id="cost'+cost_id+'">'+timeToFloat(prst_time)*est_rate+' $</td><td><p hidden id="row_id'+row_id+'">'+prst_id+'</p><span class="dashicons dashicons-trash"></span></td></tr>');
		++time_id;
		++cost_id;
		++title_id;
		++row_id;
		est_time_sum += timeToFloat(prst_time);
		if(jQuery('.table.table-striped > tbody > tr').length >= 1) {
			jQuery('h3#estimate_time_sum').text(est_time_sum + ' hours');
			jQuery('h3#estimate_money_sum').text(est_time_sum*est_rate + ' $');
		}

		setTimeout(function(){
        	jQuery(".modal").modal('hide');
    	}, 200);
  	});

    function calcRate(est_rate) {
        var count = jQuery('tbody > tr').length;
        var est_rate = jQuery('input#rate').val();
        var i = 1;
        for (i; i < count; ++i) {
            var time = jQuery('tr').find('#time'+i).text();
            jQuery('tr').find('#cost'+i).html(timeToFloat(time)*est_rate+' $');
        }
    }

	jQuery('input#rate').change(function(e) {
		e.preventDefault();
        var est_rate = jQuery('input#rate').val();
		calcRate();
		jQuery('h3#estimate_time_sum').text(est_time_sum + ' hours');
		jQuery('h3#estimate_money_sum').text(est_time_sum*est_rate + ' $');

	});


    jQuery('#add_custom').click(function(e){
        e.preventDefault();
        var prst_title = jQuery('#prst_title').val();
        var prst_time = jQuery('#timepicker1').val();
        var est_rate = jQuery('input#rate').val();

        if (jQuery('.table.table-striped > tbody > tr').length >= 1) {
            est_count = 1 + +(jQuery('.table.table-striped > tbody > tr:last').find('td#number-col').text());
        } else {
            est_count = 1;
        }

        if (jQuery('#prst_title').val() != '' && jQuery('#timepicker1').val() != '') {
            jQuery('.table > tbody').append('<tr><td id="number-col">'+est_count+'</td><td id="title'+title_id+'"><p>'+prst_title+'</p></td><td id="time'+time_id+'">'+prst_time+'</td><td id="cost'+cost_id+'">'+timeToFloat(prst_time)*est_rate+' $</td><td><p hidden id="row_id'+row_id+'"></p><span class="dashicons dashicons-trash"></span></td></tr>');
            ++time_id;
            ++cost_id;
            ++title_id;
            ++row_id;
            est_time_sum += timeToFloat(prst_time);
            if (jQuery('.table.table-striped > tbody > tr').length >= 1) {
                jQuery('h3#estimate_time_sum').text(est_time_sum + ' hours');
                jQuery('h3#estimate_money_sum').text(est_time_sum*est_rate + ' $');
            }

            jQuery('#prst_title').val('');
            jQuery('#timepicker1').val('0:00');
            setTimeout(function(){
                jQuery(".modal").modal('hide');
            }, 300);
        }
    });

  	jQuery('#add_save').click(function(e){
		e.preventDefault();
		var prst_title = jQuery('#prst_title').val();
		var prst_time = jQuery('#timepicker1').val();
		var est_rate = jQuery('input#rate').val();

		if (jQuery('.table.table-striped > tbody > tr').length >= 1) {
			est_count = 1 + +(jQuery('.table.table-striped > tbody > tr:last').find('td#number-col').text());
		} else {
			est_count = 1;
		}

		if (jQuery('#prst_title').val() != '' && jQuery('#timepicker1').val() != '') {
		jQuery('.table > tbody').append('<tr><td id="number-col">'+est_count+'</td><td id="title'+title_id+'"><p>'+prst_title+'</p></td><td id="time'+time_id+'">'+prst_time+'</td><td id="cost'+cost_id+'">'+timeToFloat(prst_time)*est_rate+' $</td><td><p hidden id="row_id'+row_id+'"></p><span class="dashicons dashicons-trash"></span></td></tr>');
		++time_id;
		++cost_id;
		++title_id;
		++row_id;
		est_time_sum += timeToFloat(prst_time);
		if (jQuery('.table.table-striped > tbody > tr').length >= 1) {
			jQuery('h3#estimate_time_sum').text(est_time_sum + ' hours');
			jQuery('h3#estimate_money_sum').text(est_time_sum*est_rate + ' $');
		}	

		ajax_data = {
			action: 'add_save_preset',
			prst_title: prst_title,
			prst_time: prst_time
		}
     	jQuery.post(js_object.ajax_url, ajax_data, function(output) {
     		var result = jQuery.parseJSON(output);
     		jQuery('#prst_select').append('<option>'+result[0]+'</option><option hidden name="prst_select_time" data-id="'+result[2]+'">'+result[1]+'</option>');
     		jQuery('.table > tbody > tr').last().find('p[hidden]').text(result[2]);
     	});
     	jQuery('#prst_title').val('');
     	jQuery('#timepicker1').val('0:00');
     	setTimeout(function(){
        	jQuery(".modal").modal('hide');
    	}, 300);
     }
  	});

    jQuery(document).on('click', 'span.dashicons-trash', function(){
		prst_name = jQuery(this).closest('tr').find('p').html().replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '');
		prst_time = jQuery(this).closest('tr').find('p[hidden]').html();
		prst_count = jQuery(this).closest('tr').find('td#number-col').html();
		est_rate = jQuery('input#rate').val();
		jQuery(this).closest('tr').hide('slow', function(){jQuery(this).closest('tr').remove(); });
		est_time_sum -= timeToFloat(prst_time);
		jQuery('h3#estimate_time_sum').text(est_time_sum + ' hours');
		jQuery('h3#estimate_money_sum').text(est_time_sum*est_rate + ' $');
		if(est_count > 0) {
			--est_count;
		} else {++est_count}
  	});


	function get_table() {
		var count = jQuery('.table.table-striped > tbody > tr').length;
		var table_array = [];
		var i = 1;
		for (i; i < count; ++i) {
			var prst_id = jQuery('tbody > tr').find('p#row_id'+i).text();
			if(prst_id == '') {
				var custom_row = [];
				var custom_title = jQuery('tbody > tr').find('#title'+i).text();
				var custom_time = jQuery('tbody > tr').find('#time'+i).text();
				custom_row.push(custom_title, custom_time);
				table_array.push(custom_row);
			} else {
                table_array.push(prst_id);
            }
		}
		console.log(table_array);
		return table_array;
	}

  	jQuery('#crt_est').click(function(e){
		e.preventDefault();
		var est_title = jQuery('#est_title').val();
		var est_items = jQuery('.table.table-striped > tbody').html().trim();
		var est_summary = jQuery('.summary.card > .card-body > ul').html().trim();
		var time_sum = jQuery('.summary.card > .card-body > ul > li > h3#estimate_time_sum').html().replace(' hours', '');
		var est_rate = jQuery('input#rate').val();
		est_items = est_items.replace(/<tr>[\s\S]*?<\/tr>/, '');
		est_items = est_items.replace(/<span class="dashicons dashicons-trash">[\s\S]*?<\/span>/g, '');
		est_items = get_table();
		estimate = {
			action: 'add_estimate',
			est_title: est_title,
			est_rate: est_rate,
			est_items: est_items,
			est_summary: est_summary,
			time_sum: time_sum
		}
     	jQuery.post(js_object.ajax_url, estimate, function(data) {
     		
     	});

     	jQuery('<div class="est_success">Estimate '+est_title+' was created</div>').insertBefore('.table').delay(3000).fadeOut();

     	setTimeout(function(){
        	location.reload();
    	}, 3000);
  	});

	
});

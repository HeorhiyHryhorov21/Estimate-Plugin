jQuery(document).ready(function() {

	function timeToFloat(time) {
	  var hoursMinutes = time.split(/[.:]/);
	  var hours = parseInt(hoursMinutes[0], 10);
	  var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
	  return hours + minutes / 60;
	}
	
	function calcRate(est_rate) {
		var count = jQuery('.table.table-striped > tbody > tr').length;
		var i = 1;
		for (i; i <= count; ++i) {
			var time = jQuery('.table.table-striped > tbody > tr').find('td#time'+i).text();
			jQuery('.table.table-striped > tbody > tr').find('td#cost'+i).html(timeToFloat(time)*est_rate+' $');
		}
	}

	jQuery('input#est_rate').change(function() {
		var est_rate = jQuery('input#est_rate').val();
		calcRate(est_rate);
		var est_change_table = jQuery('.table.table-striped').html().trim().replace(/\s+/g, " ").replace('<tbody><tr> <td>№</td> <td>Title</td> <td>Time</td> <td>Cost</td> <td></td> </tr>', '').replace('</tbody>', '');
		jQuery('p#est_change_table').val(est_change_table);
		
	});
	
	if(jQuery('body').hasClass('post-type-estimates')) {
		jQuery('input#publish').click(function() {		
			var est_post_id = jQuery('input#post_ID').val();
			var est_rate = jQuery('input#est_rate').val();
			var est_change_table = jQuery('.table.table-striped').html().trim().replace(/\s+/g, " ").replace('<tbody><tr> <td>№</td> <td>Title</td> <td>Time</td> <td>Cost</td> <td></td> </tr>', '').replace('</tbody>', '');
			estimate = {
				action: 'est_ajax_fields',
				id: est_post_id,
				est_change_table: est_change_table
			}
	     	jQuery.post(post_js_object.ajax_url, estimate, function(data) {
	     		
	     	});
     });
	}
});

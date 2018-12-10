jQuery(document).ready(function() {

    jQuery('input[type=number]').focus(function (e) {
        jQuery(this).on('mousewheel.disableScroll', function (e) {
            e.preventDefault();
        });
    });
    jQuery('input[type=number]').blur(function (e) {
        jQuery(this).off('mousewheel.disableScroll')
    });


    function timeToFloat(time) {
        var hoursMinutes = time.split(/[.:]/);
        var hours = parseInt(hoursMinutes[0], 10);
        var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
        return hours + minutes / 60;
    }

    function calcRate(est_rate) {
        var count = jQuery('tr').length;
        var i = 1;
        for (i; i < count; ++i) {
            var time = jQuery('.table.table-striped > tbody > tr').find('td#time'+i).text();
            jQuery('.table.table-striped > tbody > tr').find('td#cost'+i).html(timeToFloat(time)*est_rate+' $');
        }
    }

    jQuery('input#est_rate').change(function() {
        var est_rate = jQuery('input#est_rate').val();
        calcRate(est_rate);
        var sum_time = jQuery('h3#estimate_time_sum').html().replace(' hours', '');
        jQuery('h3#estimate_money_sum').text(sum_time*est_rate + ' $');
    });
});


$(function(){

	// Datetimepicker
    if (jQuery().datetimepicker) {
        $('[data-plugin="datetimepicker"]').datetimepicker({
            defaultDate: new Date(),
			format: 'DD-MM-YYYY hh:mm:ss',
        });
    }

	// Chosen
    if (jQuery().chosen) {
        $('[data-plugin="chosen"]').chosen();
    }
	
});
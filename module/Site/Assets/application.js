
$(function(){

	// Datetimepicker
    if (jQuery().datetimepicker) {
        $('[data-plugin="datetimepicker"]').datetimepicker({
            defaultDate: new Date(),
			format: 'DD-MM-YYYY hh:mm:ss',
        });
    }
	
});
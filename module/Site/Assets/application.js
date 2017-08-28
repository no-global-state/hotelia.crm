
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
	
    var $form = $("form");

    $(".form-action-group .dropdown-menu > li > a").click(function(event){
        event.preventDefault();

        var href = $(this).attr('href');

        $form.attr('action', href);
        $form.submit();
    });
    
});
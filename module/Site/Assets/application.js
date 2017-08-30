
$(function(){
    
	// Datetimepicker
    if (jQuery().datetimepicker) {
        var locale = 'en';

        moment.locale(locale, {
            week: { dow: 1 } // Monday is the first day of the week
        });

        $('[data-plugin="datetimepicker"]').datetimepicker({
            defaultDate: new Date(),
			format: 'YYYY-MM-DD',
            showTodayButton: true,
            locale: locale
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
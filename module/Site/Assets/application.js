
$(function(){
    // Dynamic tooltip
    $('body').tooltip({
        selector: "[data-tooltip=tooltip]",
        container: "body"
    });

    // Datetimepicker
    if (jQuery().datetimepicker) {
        var locale = $('html').attr('lang');

        moment.locale(locale, {
            week: { dow: 1 } // Monday is the first day of the week
        });

        var $datetimepicker = $('[data-plugin="datetimepicker"]');

        // Initialize default date
        if ($datetimepicker.attr('data-initial-date')){
            var defaultDate = $datetimepicker.attr('data-initial-date');
        } else {
            var defaultDate = new Date();
        }

        $('[data-plugin="datetimepicker"]').datetimepicker({
            defaultDate: defaultDate,
            format: 'YYYY-MM-DD',
            showTodayButton: true,
            locale: locale
        });

        // Shared counter updater function
        var countUpdater = function(){
            var $arrival = $("[name='arrival']");
            var $departure = $("[name='departure']");

            // Count days difference
            var a = moment($arrival.val());
            var b = moment($departure.val());
            var diff = Math.abs(a.diff(b, 'days'));
            var price = $("[name='room_id']").find(':selected').attr('data-unit-price');

            $("[data-count='days']").text(diff);
            $("[data-count='price']").text((diff * price).toLocaleString());
        };

        $("[name='room_id']").change(countUpdater);
        $datetimepicker.on('dp.hide', countUpdater);
    }

	// Chosen
    if (jQuery().chosen) {
        $('[data-plugin="chosen"]').chosen();
    }

    // Init tooltip
    $('[data-toggle="tooltip"]').tooltip({
        placement: "bottom",
        trigger: 'hover'
    });

    var $form = $("form");

    $(".form-action-group .dropdown-menu > li > a").click(function(event){
        event.preventDefault();

        var href = $(this).attr('href');

        $form.attr('action', href);
        $form.submit();
    });
    
    
    $('[data-button="view"]').click(function(event){
        event.preventDefault();

        var href = $(this).attr('href');
        var $body = $("#infoModal .modal-body");

        $.ajax({
            cache: true,
            url: href,
            success: function(response){
                $body.html(response);
                
                $("#infoModal").modal('show');
            }
        });
    });
    
});
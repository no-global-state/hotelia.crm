
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
            //defaultDate: defaultDate,
            format: 'YYYY-MM-DD',
            showTodayButton: true,
            locale: locale
        });

        // Shared counter updater function
        var countUpdater = function(){
            var $arrival = $("[name='arrival']");
            var $departure = $("[name='departure']");
            var $discount = $("[name='discount']");
            var $dailyTax = $("#daily-tax");

            // Count days difference
            var a = moment($arrival.val());
            var b = moment($departure.val());
            var days = Math.abs(a.diff(b, 'days')); // Days difference
            var price = $("[name='room_id']").find(':selected').attr('data-unit-price');
            var discount = $discount.val();
            var totalPrice = (days * price); // Number

            // If provided and positive
            if (discount) {
                // To subtract from total price
                var subtract = (totalPrice * parseFloat(discount) / 100);
                totalPrice -= subtract;
            }

            // Daily tax
            var dailyTax = parseFloat(days * $dailyTax.val());
            var discount = discount ? discount : 0;

            // Captions
            $("[data-count='daily-tax']").text(dailyTax);
            $("[data-count='days']").text(days);
            $("[data-count='discount']").text(discount);
            $("[data-count='price']").text(totalPrice.toLocaleString());

            // Inputs
            $("[name='tax']").val(dailyTax);
            $("[name='discount']").val(discount);
            $("[name='price']").val(totalPrice.toLocaleString());
        };

        // Watchers
        $("[name='discount']").bind('keyup change', countUpdater);
        $("[name='room_id']").change(countUpdater);
        $datetimepicker.on('dp.hide', countUpdater);

        countUpdater();
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
    
    
    $("[data-button='back']").click(function(event){
        event.preventDefault();
        history.go(-1);
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

    // Time display
    setInterval(function() {
        var time = (new Date()).toLocaleTimeString();
        $(".date").text(time);
    }, 500);

    // Filter
    $("a[data-input]").click(function(event){
        event.preventDefault();

        var inputSelector = $(this).data('input');
        var value = $(this).data('value');

        $(inputSelector).val(value);

        $("form").submit();
    });
    
    $(".room-taken").each(function(){
        $(this).css('background', $(this).data('background-color'));
    }).hover(function(){
        $(this).css('background', $(this).data('hover-color'));
    });
});


// Keep tabs state on refresh, taken from here: https://stackoverflow.com/a/10524697
$(function(){
    // for bootstrap 3 use 'shown.bs.tab', for bootstrap 2 use 'shown' in the next line
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // save the latest tab; use cookies if you like 'em better:
        localStorage.setItem('lastTab', $(this).attr('href'));
    });

    // go to the latest tab, if it exists:
    var lastTab = localStorage.getItem('lastTab');
    if (lastTab) {
        $('[href="' + lastTab + '"]').tab('show');
    }
});

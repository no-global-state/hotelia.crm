
$(function(){

    // Price group stat
    if (window.priceGroupStat) {
        var config = {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: window.priceGroupStat.reservations,
                    backgroundColor: [
                        window.chartColors.orange,
                        window.chartColors.yellow
                    ]
                }],
                labels: window.priceGroupStat.groups
            },
            options: {
                responsive: true,
				legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: window.priceGroupStat.title
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };

        window.onload = function() {
            var ctx = document.getElementById('chart-area').getContext('2d');
            window.myDoughnut = new Chart(ctx, config);
        };
    }

    // Stat
    if (window.stat) {
        var config = {
            type: 'line',
            data: {
                labels: window.stat.months,
                datasets: [{
                    label: window.stat.label.totalReservations,
                    borderColor: window.chartColors.red,
                    backgroundColor: window.chartColors.red,
                    data: window.stat.data.reservations,
                }, {
                    label: window.stat.label.totalIncoming + ' - ' + window.stat.currency,
                    borderColor: window.chartColors.blue,
                    backgroundColor: window.chartColors.blue,
                    data: window.stat.data.sum,
                },  {
                    label: window.stat.label.totalTax  + ' - ' + window.stat.currency,
                    borderColor: window.chartColors.yellow,
                    backgroundColor: window.chartColors.yellow,
                    data: window.stat.data.tax,
                }]
            },
            options: {
                responsive: true,
                title: {
                    display:true,
                    text: window.stat.label.summary
                },
                tooltips: {
                    mode: 'index',
                },
                hover: {
                    mode: 'index'
                }
            }
        };

        window.onload = function() {
            var ctx = document.getElementById("canvas").getContext("2d");
            window.myLine = new Chart(ctx, config);
        };
    }

    // Show/hide loading modal if available
    $.ajaxSetup({
        beforeSend: function(){
            $("#loadingModal").modal('show');
        },
        complete: function(){
            $("#loadingModal").modal('hide');
        }
    });

    // Reservation tabs (toggle filter tab button)
    $('.nav-reservation > .nav-tabs a').on('shown.bs.tab', function(event){
        // Grab links
        var $link = $(event.target);
        var $li = $link.parent();
        var $wrapper = $li.parent();
        var $dropdown = $wrapper.find('.dropdown');
        var $toggler = $dropdown.find('a');
        var disabledClass = 'disabled';

        if ($link.attr('href') === '#rooms') {
            // Disable filter
            $dropdown.addClass(disabledClass);
            $toggler.attr('data-toggle', '');

        } else {
            // Enable filter
            $dropdown.removeClass(disabledClass);
            $toggler.attr('data-toggle', 'dropdown');
        }
    });

    $(".wizard .next-step").click(function(event){
        $("html, body").animate({ scrollTop: 200 }, "slow");
        return false;
    });

    // Unit caption handler in reservation services
    $("[name='slave_id']").change(function(){
        var $selected = $(this).find(':selected');

        $("[data-unit-caption]").text($selected.data('unit'));
        $("[name='rate']").val($selected.data('rate'));

    }).trigger('change');

    // Removal buttons
    $('[data-button="delete"]').click(function(event){
        event.preventDefault();

        var url = $(this).attr('href');
        var $self = $(this);
        var $modal = $('#removeConfirmationModal');

        // Then show the modal box
        $modal.modal();

        // Then every time attach the click listener
        $("[data-button='confirm-removal']").off('click').click(function(event){
            $(this).attr('href', url);
        });
    });

    // Legal status on reservation form
    $("[data-legal-status]").bind('change', function(){
        var type = $(this).val();
        var checked = $(this).is(':checked');
        var hiddenClass = 'hidden';
        var $group = $("[data-group='legal']");

        if (checked && type == 2) {
            // Show
            $group.removeClass(hiddenClass);
        } else {
            // Hide
            $group.addClass(hiddenClass);
        }

    }).trigger('change');

    // Discount handler in reservation form
    $("[data-input='discount']").change(function(){
        var value = $(this).val();
        var $discount = $("[name='discount']");
        var $group = $("[data-group='discount']"); // Discount div

        // No discount
        if (value == 0){
            $discount.val(value);
            $group.hide();
        }

        // Custom discount
        if (value == ""){
            $group.show();
        }

        if (value != "" && value != "0"){
            $group.hide();
            $discount.val(value);
        }

        // Trigget change to reflect updates
        $discount.trigger('change');
        
    }).trigger('change');
    
    // Buttons that alter form action
    $("[data-form-action]").click(function(event){
        event.preventDefault();

        var $self = $(this);
        var $form = $self.closest('form');
        
        var action = $self.data('form-action');
        var method = $self.data('form-method');

        $form.attr('action', action);
        $form.attr('method', method);

        $form.submit();
    });

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

        $('[data-plugin="timepicker"]').datetimepicker({
            format: 'HH:mm',
            locale: locale
        });

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
            var $room = $("[name='room_id']");

            // Stop if no room selection available
            if ($room.length == 0){
                return false;
            }

            // Currently selected
            var $group = $("[name='price_group_id']").find(':selected');

            // Count days difference
            var a = moment($arrival.val());
            var b = moment($departure.val());
            var days = Math.abs(a.diff(b, 'days')); // Days difference
            var price = $room.find(':selected').data('price-group')[$("[name='price_group_id']").val()];
            var discount = $discount.val();
            var totalPrice = (days * price); // Number

            // If provided and positive
            if (discount) {
                // To subtract from total price
                var subtract = (totalPrice * parseFloat(discount) / 100);
                totalPrice -= subtract;
            }

            // Daily tax
            var dailyTax = parseFloat(days * $group.data('price-group-tax'));
            var discount = discount ? discount : 0;

            // Captions
            $("[data-count='daily-tax']").text(dailyTax);
            $("[data-count='days']").text(days);
            $("[data-count='currency']").text($group.data('price-group-currency'));
            $("[data-count='price']").text(totalPrice.toLocaleString());
            $("[data-count='discount']").text(discount);

            // Inputs
            $("[name='tax']").val(dailyTax);
            $("[name='discount']").val(discount);
            $("[name='price']").val(totalPrice);
        };

        // Watchers
        $("[name='discount']").bind('keyup change', countUpdater);
        $("[name='room_id']").change(countUpdater);
        $("[name='price_group_id']").change(countUpdater);
        $datetimepicker.on('dp.hide', countUpdater);

        countUpdater();
    }

    $("[data-button='print']").click(function(event){
        event.preventDefault();
        window.print();
    });

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

    // Extra privacy handler
    (function(){
        var wrapSelector = '.price-policy-wrapper';

        // Append a box
        $("[data-button='price-policy-add']").click(function(event){
            event.preventDefault();

            // Make a clone
            var $clone = $(wrapSelector).first().clone();

            // Clear all values from clone
            $clone.find('input').val('');
            $(wrapSelector).last().after($clone);
        });

        // Removal handler
        $(document).on('click', "[data-button='price-policy-delete']", function(event){
            event.preventDefault();

            var $parent = $(this).parents(wrapSelector);
            $parent.remove();
        });

    })($);
    
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

$(function(){

    // Room appender
    (function(){
        $(".room-append a").click(function(event){
            event.preventDefault();

            // Hide a in clone
            var $clone = $(".room-append").first().clone();

            $clone.find('a').hide();
            $clone.find('input').val('');
            $clone.find('.btn-danger').show().removeClass('hidden').click(function(event){
                event.preventDefault();
                $clone.remove();
            });

            $(".room-append").last().after($clone);
        });
    })();
    
    // Keep tabs state on refresh, taken from here: https://stackoverflow.com/a/10524697
    (function(){
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
    })();
    
    // Facility selector
    (function(){
        $(".facility-radio > input[type='checkbox']").change(function(){
            var state = $(this).prop('checked');
            var hiddenClass = 'hidden';
            var $wrapper = $(this).parent().parent().find('.facility-container');

            if (state == true) {
                $wrapper.removeClass(hiddenClass);
            } else {
                $wrapper.addClass(hiddenClass);
            }
        });
    })();
    
    // Option + Section group processor
    (function(){
        var hiddenClass = 'hidden';

        // Attach and trigger change event
        $("option[data-group]").parent().change(function(){
            // Hide all sections
            $("section[data-group]").addClass(hiddenClass);

            // Iterate over available options
            $("option[data-group]:selected").each(function(){
                var entity = $(this).data('group');

                $("section[data-group]").each(function(){
                    // Find attached groups
                    var group = $(this).attr('data-group');
                    var groups = group.split(', ');

                    for (var key in groups) {
                        // A single group without spaces
                        var singleGroup = groups[key].trim();

                        // Show
                        if (entity == singleGroup) {
                            $(this).removeClass(hiddenClass);
                        }
                    }
                });
            });
        }).change();
    })();

    // Region switcher
    (function(){
        // Initial value
        var attacher = 'region-id';
        var childSelector = "[data-select='district']";
        var parentSelector = "[data-select='region']";
        var hiddenClass = 'hidden';

        // Find active option
        var findActiveOption = function(selector){
            return $(parentSelector).find('option').filter(':selected');
        };

        var initialValue = findActiveOption().data(attacher);

        // Initial state of child element
        var $child = $(childSelector);
        var childInitialValue = $child.find('[selected]').val();

        $(parentSelector).change(function(){
            var currentValue = $(this).find('option')
                                      .filter(':selected')
                                      .data(attacher);

            // Shared chain provider
            var chainProvider = function(){
                return $child.find('option')
                           .addClass(hiddenClass)
                           .filter("[data-" + attacher + "='" + currentValue + "']")
                           .removeClass(hiddenClass);
            };

            if (currentValue == initialValue) {
                // Now handle region
                if (currentValue) {
                    // If value is undefined
                    if (!childInitialValue) {
                        chainProvider().eq(0)
                                      .prop('selected', true);
                    } else {
                        chainProvider().filter('[value=' + childInitialValue + ']')
                                      .prop('selected', true);
                    }

                } else {
                    chainProvider()
                    .first()
                    .prop('selected', true);
                }

            } else {
                // Now handle region
                chainProvider()
                   .first()
                   .prop('selected', true);
            }

        }).trigger('change');
    })();
    
});


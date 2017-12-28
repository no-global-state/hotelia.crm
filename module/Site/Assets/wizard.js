$(function() {
    function nextTab(elem) {
        $(elem).next().find('a[data-toggle="tab"]').click();
    }

    function prevTab(elem) {
        $(elem).prev().find('a[data-toggle="tab"]').click();
    }
    
    //Initialize tooltips
    $('.nav-tabs > li a[title]').tooltip();

    //Wizard
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        var $target = $(e.target);
    
        if ($target.parent().hasClass('disabled')) {
            return false;
        }
    });

    $(".next-step").click(function (e) {
        var $active = $('.wizard .nav-tabs li.active');
        $active.next().removeClass('disabled');
        nextTab($active);
    });

    $(".prev-step").click(function (e) {
        var $active = $('.wizard .nav-tabs li.active');
        prevTab($active);
    });

    // Button click
	$("[data-button='file-add']").click(function(event){
        event.preventDefault();

		var $wrapper = $(".image-wrapper");
		var $thumb = $wrapper.find('.fileinput');
		var $clone = $thumb.first().clone().removeClass('hidden');

		$wrapper.append($clone);
    });

    $(document).on('click', "[data-button='fileinput-remove']", function(){
        $(this).parent().parent().remove();
    });
});

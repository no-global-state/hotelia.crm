google.maps.event.addDomListener(window, 'load', function(){
    var mapOptions = {
        zoom: config.zoom,
        center: {
            lat: config.lat, 
            lng: config.lng
        }
    };

    var map = new google.maps.Map(document.getElementById('map'), mapOptions);
    var marker = new google.maps.Marker({
        position: {
            lat: config.lat, 
            lng: config.lng
        },
        map: map,
        draggable: true
    });

    var infowindow = new google.maps.InfoWindow({
        content: '<p>Marker Location:' + marker.getPosition() + '</p>'
    });

    var updater = function(market){
        $("[name='hotel[lat]']").val(marker.position.lat());
        $("[name='hotel[lng]']").val(marker.position.lng());
    }

    google.maps.event.addListener(marker, 'click', function() {
        updater(marker);
    });

    google.maps.event.addListener(marker, 'dragend', function() {
        updater(marker);
    });
});

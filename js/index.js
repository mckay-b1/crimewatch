function generateMap(data) {
    $('#map').show();
    $('#map').gmap({'zoom': 2, 'disableDefaultUI': false}).bind('init', function(e, map) {
        for (var i = 0; i < data.length; i++) {
            $('#map').gmap('addMarker', {'position': new google.maps.LatLng(data[i].location.latitude,data[i].location.longitude)}).click(function() {
			$('#map_canvas').gmap('openInfoWindow', { content : 'Hello world!' }, this);
            });
        }
        $('#map').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $(this).gmap('get', 'markers')));
        
    });
}

function getCrimes(data) {
    var url = $('#search form').attr('action');

    $.ajax({
        url : url,
        type: 'POST',
        data: data,
        success: function() {
            var crimes = $.parseJSON(arguments[0]);
            generateMap(crimes);
        },
        error: function() { 
        }
    });
}

$(document).ready(function() {
    
    //Event handler for search button click
    $('#do_search').click(function() {
        //Validate against empty input
        if ($('#search input').val().length === 0 ) {
            alert('Please enter a value before attempting to search!');
        } else {
            $('#search form').submit();
        }
    });

    //Callback handler for form submit
    $('#search form').submit(function(e) {
        e.preventDefault();
        
        var data = $(this).serializeArray();
        
        //First, convert input into lat/lng data
        var address = encodeURIComponent(data[0].value);
        var geometry;
        
        $.ajax({
            url : 'http://maps.googleapis.com/maps/api/geocode/json?address='+address+'&sensor=false',
            type: 'GET',
            success: function() {
                var responseJSON = arguments[2].responseJSON;
                geometry = responseJSON.results[0].geometry.location;
                getCrimes(geometry);
            },
            error: function() {
        
            }
        });
    });

});
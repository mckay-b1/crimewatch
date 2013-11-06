function generateMap(data, geometry) {
    $('#map').slideDown('400', 'linear', function() {
        var center = new google.maps.LatLng(geometry.lat, geometry.lng);    
        
        $('#map').gmap({'zoom': 13, 'center': center, 'disableDefaultUI': false}).bind('init', function(e, map) {
            for (var i = 0; i < data.length; i++) {
                var position = new google.maps.LatLng(data[i].location.latitude,data[i].location.longitude);
                $('#map').gmap('addMarker', {'position': position})
            }
            $('#map').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $(this).gmap('get', 'markers')));
        });
        $('#ajax-loader').addClass('hidden'); 
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
            generateMap(crimes, data);
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
            $('#ajax-loader').removeClass('hidden');
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
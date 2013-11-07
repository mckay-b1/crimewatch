function makeInfoWindowEvent(content) {  
    return function() {
        $('#map-canvas').gmap('openInfoWindow', {'content': content}, this)
    };  
 } 


function populateCategories(data) {
    var categories = new Array();
    
    //Grab categories from results
    for (var i = 0; i < data.length; i++) {
        if ($.inArray(data[i].category, categories) === -1) {
            categories.push(data[i].category);
        }
    }
    
    //Now append categoies to dropdown list
    $('#crime_type').append($("<option />").val('').text('---'));
    for (var i = 0; i < categories.length; i++) {
        $('#crime_type').append($("<option />").val(categories[i].replace(/\s+/g, '-').toLowerCase()).text(categories[i]));
    }
}


function populateDates(dates) {
    for (var i = 0; i < dates.length; i++) {
        $('#crime_date').append($("<option />").val(dates[i][0]).text(dates[i][1]));
    }
}


function updateMap() {
    
}


function generateMap(data, geometry) {
    $('#map-canvas').gmap('destroy');
    var center = new google.maps.LatLng(geometry.lat, geometry.lng);    
        
    $('#map-canvas').gmap({'zoom': 13, 'center': center, 'disableDefaultUI': false}).bind('init', function(e, map) {
        
        var position;
        var content;
        
        for (var i = 0; i < data.length; i++) {
            position = new google.maps.LatLng(data[i].latitude, data[i].longitude);
            content = new Array();
            
            content.push('<div class="inf-wdw" id="inf-'+data[i].crime_id+'">');
            content.push('<b>'+data[i].category+'</b><br>');
            content.push('Crime '+data[i].street+'<br>');
            content.push('Occured '+data[i].month+'<br>');
            content.push(data[i].outcome_status);
            content.push('</div>');
            
            content = content.join('');
            $('#map-canvas').gmap('addMarker', {'position': position}).click(makeInfoWindowEvent(content));
        }
        
        $('#map-canvas').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $(this).gmap('get', 'markers'), {maxZoom: 15}));
    });

    $('#map-container').animate({height: '450px'}, 1000, function() {
        $('#ajax-loader').addClass('hidden'); 
    });
    
}


function getCrimes(data) {
    var url = $('#search form').attr('action');

    $.ajax({
        url : url,
        type: 'POST',
        data: data,
        success: function(response) {
            response = $.parseJSON(response);
            generateMap(response.data, response.geometry);
            populateCategories(response.data);
            populateDates(response.dates);
        },
        error: function() { 
        }
    });
}


$(document).ready(function() {
    
    //Event handler for search button click
    $('#do-search').click(function() {
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
        
        $('#map-container').animate({height: '0px'}, 1000);
        $('#ajax-loader').removeClass('hidden');
        
        var data = $(this).serializeArray();
        
        //First, convert input into lat/lng data
        var address = encodeURIComponent(data[0].value);
        var geometry;
        
        //Use Google's geo-coding service to get lat/lng data for input
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
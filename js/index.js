function makeInfoWindowEvent(content) {  
    return function() {
        $('#map-canvas').gmap('openInfoWindow', {content: content}, this);
    };  
 } 


function addMarkers(data, category) {
    var position;
    var content;
    var markerOptions = new Object();
    
    if (category === undefined) {
        var category = "";
    }
    
    for (var i = 0; i < data.length; i++) {
        if ((category !== "" && data[i].category.url === category) || category === "") {
            position = new google.maps.LatLng(data[i].location.latitude, data[i].location.longitude);
            content = new Array();

            content.push('<div class="inf-wdw" id="inf-'+data[i].crime_id+'">');
            content.push('<b>'+data[i].category.name+'</b><br>');
            
            //Sometimes the API just returns 'on or near' without a street name
            if (data[i].street !=="" && data[i].street !== "on or near ") {
                content.push('Crime '+data[i].street+'<br>');
            }
            
            content.push('Occured '+data[i].month+'<br>');
            content.push(data[i].outcome_status);
            content.push('</div>');

            content = content.join('');

            markerOptions.position = position;

            $('#map-canvas').gmap('addMarker', markerOptions).click(makeInfoWindowEvent(content));
        }
    }
    
    $('#map-canvas').gmap('set', 'MarkerClusterer', new MarkerClusterer($('#map-canvas').gmap('get', 'map'), $('#map-canvas').gmap('get', 'markers'), {
        'maxZoom': 15
    })); 
}


function generateMap(data, geometry) {
    $('#map-canvas').gmap('destroy');

    var center = new google.maps.LatLng(geometry.lat, geometry.lng);
      
    var mapOptions = {
        zoom: 13,
        center: center,
        disableDefaultUI: false
    };
    
    $('#map-canvas').gmap(mapOptions);

    var circleOptions = {
        strokeColor: '#0033FF',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#0066FF',
        fillOpacity: 0.35,
        center: center,
        radius: 1610 
    };
    
    $('#map-canvas').gmap('addShape', 'Circle', circleOptions);
}


function getCrimes(params) {
    var url = "ajax/getCrimes.php";
    var returnData = {};
    returnData.crimeData = {};
    returnData.forceData = {};
    returnData.message = "";
    returnData.success = "";
            
    $.ajax({
        url : url,
        type: 'POST',
        data: params,
        success: function(response) {
            response = $.parseJSON(response);
            
            if (response.success) {
                returnData.crimeData = response.crimeData;
                returnData.forceData = response.forceData;
            }
            
            returnData.message = response.message;
            returnData.success = response.success;
        },
        error: function() {
    
        },
        async: false
    });
    
    return returnData;
}


function populateCategories(data) {
    
    //Grab and tally categories from results data
    var categories = new Object();
    var category_obj;
    
    $.each(data, function() {

        if (categories[this.category.url] === undefined) {
            category_obj = new Object();
            category_obj.url = this.category.url;
            category_obj.name = this.category.name;
            category_obj.count = 1;
            
            categories[this.category.url] = category_obj;
        } else {
            categories[this.category.url].count += 1;
        }
    });
   
    
    //Now append categoies to dropdown list
    $('#crimeTypesSelect').html('');
    $('#crimeTypesSelect').append($("<option />").val('').text('All'));
    
    $.each(categories, function() {
        $('#crimeTypesSelect').append($("<option />").val(this.url).text(this.name+" ("+this.count+")"));
    });

    $('#crimeTypesSelect').find('option').each(function(i, e) {
        if($(e).val() == $('#crimeType').val()){
            $('#crimeTypesSelect').prop('selectedIndex', i);
        }
    });
}


function populateDates() {
    var url = "ajax/getCrimeDates.php";
    var crimeDates;
    
    $.ajax({
        url : url,
        type: 'POST',
        success: function(response) {
            response = $.parseJSON(response);
            crimeDates = response.crimeDates;
            
            $('#crimeDatesSelect').html('');
            for (var i = 0; i < crimeDates.length; i++) {
                $('#crimeDatesSelect').append($("<option />").val(crimeDates[i][0]).text(crimeDates[i][1]));
            }
        },
        error: function() {
    
        },
        async: false
    });
}


function populateForceInformation(data) {
    var html = '';
    
    html += '<h2>Force Information</h2>';
    html += data.name;
    
    if (data.description !== null) {
        html += data.description;
    }
    
    for (var i = 0; i < data.engagement_methods.length; i++) {
        html += '<a href="'+data.engagement_methods[i].url+'" title="'+data.engagement_methods[i].description.replace(/(<([^>]+)>)/ig, '')+'" target="_blank"><h3>'+data.engagement_methods[i].title.charAt(0).toUpperCase() + data.engagement_methods[i].title.slice(1)+'</h3></a>';
    }

    $('#mapPanel div#forceInformation').html(html);
    
} 


function geocode(address) {
    var data = {};
    
    //Use Google's geo-coding service to get lat/lng data for input
    $.ajax({
        url : 'http://maps.googleapis.com/maps/api/geocode/json?address='+address+'&sensor=false',
        type: 'GET',
        success: function() {
            var responseJSON = arguments[2].responseJSON;
            
            if (responseJSON.status==="ZERO_RESULTS") {
                data = false;
            } else {
                data.geometry = responseJSON.results[0].geometry.location;
                data.address = responseJSON.results[0].formatted_address;
            }
        },
        error: function() {
            //TODO - ADD ERROR HANDLING LOGIC
        },
        async: false
    });
    
    return data;
}


$(document).ready(function() {
    var crimeData;
    
    //Event handler for search button click
    $('#do-search').click(function() {
        //Validate against empty input
        if ($('#search input').val().length === 0 ) {
            alert('Please enter a value before attempting to search!');
        } else {
            $('#map-container').animate({height: '0px'}, 1000);
            $('#search .ajaxLoader').toggleClass('hidden');
            $('#search form').submit();
        }
    });

    //Callback handler for form submit
    $('#search form').submit(function(e) {
        e.preventDefault();
        
        $('#map-container').animate({height: '0px'}, 600);
        
        $('#mapOverlay').addClass('hidden');
        
        $('#search .ajaxLoader').removeClass('hidden');
        
        $('#feedback').hide();
        
        //First, convert input into lat/lng data
        var address = encodeURIComponent($(this).serializeArray()[0].value);
        
        var data = geocode(address);
        var geometry = data.geometry;
        address = data.address;
        
        if (!data) {
            $('#feedback').html('No data was found for the specified address! Please check that the address you entered is valid.');
            $('#feedback').show();
        } else {
            //Store the geometry data in the hidden fields
            $('#addressLat').val(geometry.lat);
            $('#addressLng').val(geometry.lng);

            var params = new Object();
            params.geometry = new Object();
            params.geometry.lat = geometry.lat;
            params.geometry.lng = geometry.lng;

            var data = getCrimes(params);
            
            if (data.success) {
                crimeData = data.crimeData;
                var forceData = data.forceData;
                
                generateMap(crimeData, params.geometry);
                addMarkers(crimeData);

                populateCategories(crimeData);
                populateDates();
                populateForceInformation(forceData);
                
                $('#map-container').animate({height: '450px'}, 600);
                
                $('#map-container #resultsInfo').html('Showing '+crimeData.length+' '+(crimeData.length == 1 ? 'crime' : 'crimes')+' for '+address);
            } else {
                $('#mapOverlay').toggleClass('hidden');
                $('#feedback').html('Unfortunately there was no data found in our system for this area.');
                $('#feedback').show();
            }
        }
        $('#search .ajaxLoader').toggleClass('hidden');
    });
    
    $('#crimeTypesSelect, #crimeDatesSelect').change(function(e) {
        $('#crimeType').val($('#crimeTypesSelect').val());
        $('#crimeDate').val($('#crimeDatesSelect').val());
        
        $('#mapOverlay').html('<img src="pix/ajax-loader.png" class="ajaxLoader">');
 
        $('#mapOverlay').removeClass('hidden');
            
        var params = new Object();
        params.geometry = new Object();
        params.geometry.lat = $('#addressLat').val();
        params.geometry.lng = $('#addressLng').val();
        params.crimeType = $('#crimeType').val();
        params.crimeDate = $('#crimeDate').val();
 
        $('#map-canvas').gmap('clear', 'markers');
        
        if (e.target.id === "crimeDatesSelect") {
            var data = getCrimes(params);
            crimeData = data.crimeData;
            populateCategories(crimeData);
        }
        
        if ($.isEmptyObject(crimeData)) {
            $('#mapOverlay').html('<h1>No data to display!</h1>');
        } else {
            $('#mapOverlay').addClass('hidden');
        }
        
        generateMap(crimeData, params.geometry);
        addMarkers(crimeData, params.crimeType);

    });

});
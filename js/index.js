function makeInfoWindowEvent(content) {  
    return function() {
        $('#map-canvas').gmap('openInfoWindow', {content: content}, this);
    };  
 } 


function addMarkers(data, category) {
    var markerOptions = new Object();
    
    //Handle potential lack of 'category' parameter
    if (category === undefined) {
        var category = "";
    }
    
    //Clear any existing markers from the map
    $('#map-canvas').gmap('clear', 'markers');
    
    //For each crime, add a marker to the map
    for (var i = 0; i < data.length; i++) {
        if ((category !== "" && data[i].category.url === category) || category === "") {
            var position = new google.maps.LatLng(data[i].location.latitude, data[i].location.longitude);

            //Dynamically generate icon based on category colour
            var icon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|'+data[i].category.color;

            //Populate the infowindow content for each marker
            var content = new Array();

            content.push('<div class="inf-wdw" id="inf-'+data[i].crime_id+'">');
            content.push('<img src="'+icon+'"/>');
            content.push('<b>'+data[i].category.name+'</b><br>');

            //Sometimes the API just returns 'on or near ' without a street name. Handle this
            if (data[i].street !=="" && data[i].street !== "on or near ") {
                content.push('Crime '+data[i].street+'<br>');
            }

            content.push('Occured '+data[i].month+'<br>');
            content.push(data[i].outcome_status);
            content.push('</div>');

            content = content.join('');

            markerOptions = {
                position: position,
                icon: icon
            }

            $('#map-canvas').gmap('addMarker', markerOptions).click(makeInfoWindowEvent(content));
        }
    }
    //Apply the Markerclusterer plugin to the new set of markers
    $('#map-canvas').gmap('set', 'MarkerClusterer', new MarkerClusterer($('#map-canvas').gmap('get', 'map'), $('#map-canvas').gmap('get', 'markers'), {
        maxZoom: 15
    })); 
}


function generateMap(data, geometry) {
    $('#map-canvas').gmap('destroy');
    
    //Center the map on the co-ordinates generated from the user's address input
    var center = new google.maps.LatLng(geometry.lat, geometry.lng);
      
    var mapOptions = {
        zoom: 13,
        center: center,
        disableDefaultUI: false
    };
    
    $('#map-canvas').gmap(mapOptions);
    
    //Create a circle to show area covered by search (1 mile/1609.344m radius)
    var circleOptions = {
        strokeColor: '#0033FF',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#0066FF',
        fillOpacity: 0.35,
        center: center,
        radius: 1609.344
    };
    
    $('#map-canvas').gmap('addShape', 'Circle', circleOptions);
}


function getCrimes(params) {
    var url = "ajax/getCrimes.php";
    
    //Declare the object to store the data to be returned from this function
    var returnData = {};
    returnData.crimeData = {};
    returnData.forceData = {};
    returnData.categoryData = {};
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
                returnData.categoryData = response.categoryData;
            }
            
            returnData.message = response.message;
            returnData.success = response.success;
        },
        error: function() {
            //TODO - ADD ERROR HANDLING LOGIC
        },
        async: false
    });
    
    return returnData;
}


function populateCategories(categoryData) { 
    //Append categories to 'Crime Type' dropdown list
    $('#crimeTypesSelect').html('');
    $('#crimeTypesSelect').append($("<option />").val('').text('All'));
    
    $.each(categoryData, function() {
        $('#crimeTypesSelect').append($("<option />").val(this.url).text(this.name+" ("+this.count+")"));
    });
    
}


function populateDates() {
    //Makes a call to the API to get all possible crime data dates for an area
    var url = "ajax/getCrimeDates.php";
    var crimeDates;
    
    $.ajax({
        url : url,
        type: 'POST',
        success: function(response) {
            response = $.parseJSON(response);
            crimeDates = response.crimeDates;
            
            //Append dates to 'Date' dropdown list
            $('#crimeDatesSelect').html('');
            for (var i = 0; i < crimeDates.length; i++) {
                $('#crimeDatesSelect').append($("<option />").val(crimeDates[i][0]).text(crimeDates[i][1]));
            }
        },
        error: function() {
            //TODO - ADD ERROR HANDLING LOGIC
        },
        async: false
    });
}


function populateForceInformation(forceData) {
    var html = '';
    
    html += '<h2>Force Information</h2>';
    html += forceData.name;
    
    if (forceData.description !== null) {
        html += forceData.description;
    }
    
    //Dynamically add each avaiable engagement method - The availability and format of these is very inconsistent across the board
    for (var i = 0; i < forceData.engagement_methods.length; i++) {
        html += '<a href="'+forceData.engagement_methods[i].url+'" title="'+forceData.engagement_methods[i].description.replace(/(<([^>]+)>)/ig, '')+'" target="_blank"><h3>'+forceData.engagement_methods[i].title.charAt(0).toUpperCase() + forceData.engagement_methods[i].title.slice(1)+'</h3></a>';
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
        
        $('#map-container').animate({height: '0px'}, 600, function() {
            //Code is embedded into animation callback to ensure animation works as previously it didn't
            $('#mapOverlay').addClass('hidden');

            $('#search .ajaxLoader').removeClass('hidden');

            $('#feedback').hide();

            //URL encode address
            var address = encodeURIComponent($('#search form').serializeArray()[0].value);
            
            //Geocode address into into lat/lng data
            var data = geocode(address);
            var geometry = data.geometry;
            address = data.address;
            
            if (!data) {
                //If no data was returned from attempted geocoding
                $('#feedback').html('No data was found for the specified address! Please check that the address you entered is valid.');
                $('#feedback').show();
            } else {
                $('#address').val(address);
                
                //Store the geometry data in the hidden fields
                $('#addressLat').val(geometry.lat);
                $('#addressLng').val(geometry.lng);

                var params = new Object();
                params.geometry = new Object();
                params.geometry.lat = geometry.lat;
                params.geometry.lng = geometry.lng;
                
                //Fetch the crimes for this lat/lng location
                var data = getCrimes(params);

                if (data.success) {
                    crimeData = data.crimeData;
                    var forceData = data.forceData;
                    var categoryData = data.categoryData;

                    generateMap(crimeData, params.geometry);
                    addMarkers(crimeData);

                    populateCategories(categoryData);
                    populateDates();
                    populateForceInformation(forceData);
                    
                    setTimeout(function () {
                        $('#map-container').animate({height: '450px'}, 600);
                    }, 1000);
                    
                    var date = $('#crimeDatesSelect option:selected').text();
                    var count = crimeData.length !== undefined ? crimeData.length : 0;
                    
                    $('#map-container #resultsInfo').html('Showing '+count+' '+(count == 1 ? 'crime' : 'crimes')+' for '+address+' ('+date+')');
                } else {
                    $('#mapOverlay').toggleClass('hidden');
                    $('#feedback').html('Unfortunately there was no data found in our system for this area.');
                    $('#feedback').show();
                }
            }
            $('#search .ajaxLoader').toggleClass('hidden');
        });
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
        
        if (e.target.id === "crimeDatesSelect") {
            //When changing Date select, reset category to 'All'
            //This is because category types are inconsistent across dates
            $('#crimeType').val('');
            params.crimeType = $('#crimeType').val();
            
            var data = getCrimes(params);
            crimeData = data.crimeData;
            populateCategories(data.categoryData);
        }
        
        if ($.isEmptyObject(crimeData)) {
            $('#mapOverlay').html('<h1>No data to display!</h1>');
        } else {
            $('#mapOverlay').addClass('hidden');
        }
        
        generateMap(crimeData, params.geometry);
        addMarkers(crimeData, params.crimeType);
        
        var address = $('#address').val();
        var date = $('#crimeDatesSelect option:selected').text();
        var count = crimeData.length !== undefined ? crimeData.length : 0;
  
        $('#map-container #resultsInfo').html('Showing '+count+' '+(count == 1 ? 'crime' : 'crimes')+' for '+address+' ('+date+')');
        
    });

});
var crimeData;
var forceData;
var categoryData;
var localTeamData;


function ucfirst(str) {
    str += '';
    var f = str.charAt(0).toUpperCase();
    return f + str.substr(1);
}


function createInfoWindowEvent(content) {  
    return function() {
        $('#mapCanvas').gmap('openInfoWindow', {content: content}, this);
    };  
 } 


function addMarkers(data, category) {
    var markerOptions = new Object();
    
    //Handle potential lack of 'category' parameter
    if (category === undefined) {
        var category = "";
    }
    
    //Clear any existing markers from the map
    $('#mapCanvas').gmap('clear', 'markers');
    
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
            content.push('<b>'+data[i].category.nicename+'</b><br>');

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

            $('#mapCanvas').gmap('addMarker', markerOptions).click(createInfoWindowEvent(content));
        }
    }
    
    //Apply the Markerclusterer plugin to the new set of markers
    $('#mapCanvas').gmap('set', 'MarkerClusterer', new MarkerClusterer($('#mapCanvas').gmap('get', 'map'), $('#mapCanvas').gmap('get', 'markers'), {
        maxZoom: 15
    })); 
}


function generateMap(data, lat, lng) {
    $('#mapCanvas').gmap('destroy');
    
    //Center the map on the co-ordinates generated from the user's address input
    var center = new google.maps.LatLng(lat, lng);
      
    var mapOptions = {
        center: center,
        disableDefaultUI: false,
        scrollwheel: true,
        zoom: 14
    };
    
    $('#mapCanvas').gmap(mapOptions);
    
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
    
    $('#mapCanvas').gmap('addShape', 'Circle', circleOptions);
}


function getCrimes(params) {
    //Declare the object to store the data to be returned from this function
    var returnData = {};
    returnData.crimeData = {};
    returnData.forceData = {};
    returnData.categoryData = {};
    returnData.localTeamData = {};
    returnData.message = "";
    returnData.success = "";
            
    $.ajax({
        url : 'ajax/getCrimes.php',
        type: 'POST',
        data: params,
        success: function(response) {
            response = $.parseJSON(response);
            
            if (response.success) {
                returnData.crimeData = response.crimeData;
                returnData.forceData = response.forceData;
                returnData.categoryData = response.categoryData;
                returnData.localTeamData = response.localTeamData;
            }
            
            returnData.message = response.message;
            returnData.success = response.success;
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
        $('#crimeTypesSelect').append($("<option />").val(this.url).text(this.nicename+" ("+this.count+")"));
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
        async: false
    });
}


function populateForceInfo(forceData) {
    var html = '';
    
    html += '<h2>Force Information</h2>';
    html += '<span class="forceName">'+forceData.name+'</span>';
    
    if (forceData.description !== null) {
        html += forceData.description;
    }
    
    //Dynamically add each available engagement method - The availability and format of these is very inconsistent across the board
    var em = forceData.engagement_methods;
    for (var i = 0; i < em.length; i++) {
        html += '<a href="'+em[i].url+'" title="'+em[i].description.replace(/(<([^>]+)>)/ig, '')+'" target="_blank"><h4>'+ucfirst(em[i].title)+'</h4></a>';
    }

    $('#forceInfo').html(html);
    
}

function populateLocalTeamInfo(localTeamData) {
    var html = '';
    
    html += '<h2>Your Local Policing Team</h2>';
    html += '<a href="'+localTeamData.url_force+'" target="_blank"><h3>'+localTeamData.name+'</h3></a>';
    
    if (localTeamData.population !== null) {
        html += '<p>Population: '+Number(localTeamData.population).toLocaleString('en')+'</p>';
    }
    
    if (localTeamData.description !== null) {
        html += localTeamData.description;
    }
    
    //Dynamically add each available engagement method - The availability and format of these is very inconsistent across the board
    if (localTeamData.contact_details !== undefined) {
        $.each(localTeamData.contact_details, function(k,v) {
            if (/^http:\/\//.test(v) || k === 'email') {
                html += '<a href="'+v+'" title="'+ucfirst(k)+'" target="_blank"><h4>'+ucfirst(k)+'</h4></a>';
            }

            if (k === 'telephone') {
                html += '<h4>'+ucfirst(k)+'</h4>';
                html += v;
            }
        });
    }
    
    if (localTeamData.locations !== undefined) {
        $.each(localTeamData.locations, function() {      
            if (this.type === 'station') {
                if (this.description !== undefined) {
                    html += '<h4>Police station</h4>';
                    html += '<p>'+this.address+'</p>';
                }
                
                if (this.description !== undefined) {
                    html += this.description;
                }
            }
        });
    }
    
    $('#localTeamInfo').html(html);
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
                var result = responseJSON.results[0];
                
                data.geometry = result.geometry.location;
                data.address = result.formatted_address;
            }
        },
        async: false
    });
    
    return data;
}


function doBuildMap(data) {
    $('#mapOverlay').addClass('hidden');
    $('#search .errorBox').hide();

    var geometry = data.geometry;
    var address = data.address;

    $('#address').val(address);

    //Store the geometry data in the hidden fields
    $('#addressLat').val(geometry.lat);
    $('#addressLng').val(geometry.lng);
    
    $('#viewStatistics').attr('href', '/statistics?lat='+geometry.lat+'&lng='+geometry.lng);
    
    var params = new Object();
    params.lat = geometry.lat;
    params.lng = geometry.lng;  

    //Fetch the crimes for this lat/lng location
    var data = getCrimes(params);

    if (data.success) {
        crimeData = data.crimeData;
        forceData = data.forceData;
        categoryData = data.categoryData;
        localTeamData = data.localTeamData;

        generateMap(crimeData, params.lat, params.lng);
        addMarkers(crimeData);

        populateCategories(categoryData);
        populateDates();
        populateForceInfo(forceData);
        populateLocalTeamInfo(localTeamData);

        var date = $('#crimeDatesSelect option:selected').text();
        var count = crimeData.length !== undefined ? crimeData.length : 0;

        $('#resultsInfo').html('Showing '+count+' '+(count === 1 ? 'crime' : 'crimes')+' for '+address+' ('+date+')');

        $('#mapContainer').animate({height: '530px'}, 600, function() {
            $('#resultsInfo').show();
            $('#mapContainer').removeClass('hidden');
            $('#mapFilters').show();
            $('#forceInfo').show();
            $('#localTeamInfo').show();
        });
    } else {
        $('#mapOverlay').toggleClass('hidden');
        $('#search .errorBox').html(data.message);
        $('#search .errorBox').show();
    }
    
    $('#search .ajaxLoader').addClass('hidden');
}


function buildMap(data) {
    $('#search .ajaxLoader').removeClass('hidden');
    
    $('#mapContainer').animate({height: '0'}, 600, function() {
        $('#resultsInfo').hide();
        $('#mapContainer').addClass('hidden');
        $('#mapFilters').hide();
        $('#forceInfo').hide();
        $('#localTeamInfo').hide();
        
        doBuildMap(data);
    });
}


function geolocateSearch(position) {
    var data = new Object();

    //Use Google's reverse geocoding service to get address
    $.ajax({
        url : 'http://maps.googleapis.com/maps/api/geocode/json?latlng='+position.coords.latitude+','+position.coords.longitude+'&sensor=false',
        type: 'GET',
        success: function() {
            var responseJSON = arguments[2].responseJSON;

            if (responseJSON.status==="ZERO_RESULTS") {
                data = false;
            } else {
                var result = responseJSON.results[0];

                data.geometry = result.geometry.location;
                data.address = result.formatted_address;
            }
        },
        async: false
    });

    buildMap(data);
}


$(document).ready(function() {
    
    //Event handler for search form submit
    $('#searchButton').click(function(e) {
        $('#search .errorBox').hide();
        
        //Validate against empty input
        if ($('#search input').val().length === 0 ) {
            $('#search .errorBox').html('Please enter a value before attempting to search!');
            $('#search .errorBox').show();
        } else {
            $('#searchForm').submit();
        }
    });
    
    
    //Event handler for location sharing
    $('#geolocateButton').click(function(e) {
        e.preventDefault();
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(geolocateSearch);
        } else {   
            alert('Sorry, your browser doesn\'t support geolocation!');   
        }
    });
    
    
    //Event handler for login form submit
    $('#loginForm').submit(function(e) { 
        e.preventDefault();
        
        var ajaxLoader = $('div#login .ajaxLoader');
        ajaxLoader.removeClass('hidden');       
        
        var errorBox = $('div#login .errorBox');
        errorBox.hide();
        errorBox.html('');
        var errorMessage = "";
        
        var emailField = $('div#login #emailInput');
        var passwordField = $('div#login #passwordInput');
        
        emailField.css('border', '0');
        passwordField.css('border', '0');
        
        if (emailField.val().length === 0 || passwordField.val().length === 0) {
            
            if (emailField.val().length === 0) {
                errorMessage += "Email field is blank!<br>";
                emailField.css('border', 'solid 1px #FF0000');
            }
            
            if (passwordField.val().length === 0) {
                errorMessage += "Password field is blank!";
                passwordField.css('border', 'solid 1px #FF0000');
            }

            errorBox.html(errorMessage);
            errorBox.show();
            ajaxLoader.addClass('hidden');
            return false;
        } else {
            $.ajax({
                url : 'ajax/login.php',
                type: 'POST',
                data: $(this).serializeArray(),
                success: function(response) {
                    response = $.parseJSON(response);

                    if (response.success) {
                        location.reload();
                    } else {
                        //An error occurred
                        errorBox.html(response.message);
                        errorBox.show();
                        passwordField.val('');
                    }
                    
                    ajaxLoader.addClass('hidden');
                },
                async: false
            });
        }
    });
    
    
    //Event handler for logout form submit
    $('#logoutForm').submit(function(e) {
        $.ajax({
            url : 'ajax/login.php',
            type: 'POST',
            data: { logout: true },
            success: function(response) {
                location.reload();
            },
            async: false
        });
    });
    
    
    //Event handler for search form submit
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        
        var address = $('#searchForm').serializeArray()[0].value;
        
        //URL encode address
        address = encodeURIComponent(address);

        //Geocode address into into lat/lng data
        var data = geocode(address);
        
        if (!data) {
            //If no data was returned from attempted geocoding
            $('#search .errorBox').html('No data was found for the specified address! Please check that the address you entered is valid.');
            $('#search .errorBox').show();
            return false;
        }
        
        buildMap(data);
    });
    
    
    //Event handler for custom location form submit
    $('form#addLocationForm').submit(function(e) {
        e.preventDefault();
        
        var ajaxLoader =  $('#savedLocations .ajaxLoader');
        ajaxLoader.show();
        
        var errorMessage = "";
        var errorBox = $('#savedLocations .errorBox');
        errorBox.hide();
        errorBox.html("");
        
        var locName = $('form#addLocationForm #locName');
        var locAddress = $('form#addLocationForm #locAddress');
        
        locName.css('border', 'none');
        locAddress.css('border', 'none');
        
        //Validate against empty inputs
        if (locName.val().length === 0 || locAddress.val().length === 0) {
            if (locName.val().length === 0) {
                errorMessage += "Please enter a name for your location!<br>";
                locName.css('border', 'solid 1px #FF0000');
            }
            
            if (locAddress.val().length === 0) {
                errorMessage += "Please enter an address/postcode for your location!";
                locAddress.css('border', 'solid 1px #FF0000');
            }

            errorBox.html(errorMessage);
            errorBox.show();
            return false;
        } else {
            //Check address is valid
            var geocoded = geocode(encodeURIComponent(locAddress.val()));
            locAddress = geocoded.address;

            if (!geocoded) {
                //If no data was returned from attempted geocoding
                alert('Invalid address/postcode entered! Please try again.');
                return false;
            }

            var data = new Object();
            data.name = locName.val();
            data.address = locAddress;

            $('form#addLocationForm img.ajaxLoader').removeClass('hidden');

            //Add the location to the database
            $.ajax({
                url : 'ajax/addLocation.php',
                type: 'POST',
                data: data,
                success: function(response) {
                    response = $.parseJSON(response);

                    if (response.success) {
                        $('#savedLocations ul').html(response.locationsHTML);
                        $('#savedLocations form')[0].reset();
                    } else {
                        //An error occurred
                        errorBox.show();
                        errorBox.html(response.message);
                    }

                    $('form#addLocationForm img.ajaxLoader').addClass('hidden');

                },
                async: false
            });
        }
    });

    
    $('#savedLocations').delegate('ul li span', 'click', function(e) {
        var address = $(this).attr('title');
        
        //URL encode address
        address = encodeURIComponent(address);

        //Geocode address into into lat/lng data
        var data = geocode(address);
        
        if (!data) {
            //If no data was returned from attempted geocoding
            $('#search .errorBox').html('No data was found for the specified address! Please check that the address you entered is valid.');
            $('#search .errorBox').show();
            return false;
        }
        
        buildMap(data);
    });
    
    
    $('#savedLocations').delegate('img.deleteLocation', 'click', function(e) {
        var name = $(this).siblings('span').html();
        if (confirm('Are you sure you want to delete \''+name+'\' from your locations?')) {
            var errorBox = $('#savedLocations .errorBox');
            errorBox.hide();
            errorBox.html('');

            var locationid = $(this).parent().attr('id').split('-')[1];

            var data = new Object();
            data.locationid = locationid;

            $.ajax({
                url : 'ajax/deleteLocation.php',
                type: 'POST',
                data: data,
                success: function(response) {
                    response = $.parseJSON(response);

                    if (response.success) {
                        $('#savedLocations ul').html(response.locationsHTML);
                    } else {
                        //An error occured
                        errorBox.show();
                        errorBox.html(response.message);
                    }

                },
                async: false
            });
        }
    });
    
    
    $('#crimeTypesSelect, #crimeDatesSelect').change(function(e) {
        $('#crimeType').val($('#crimeTypesSelect').val());
        $('#crimeDate').val($('#crimeDatesSelect').val());
 
        $('#mapOverlay').removeClass('hidden');
            
        var params = new Object();
        params.lat = $('#addressLat').val();
        params.lng = $('#addressLng').val();
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
        
        generateMap(crimeData, params.lat, params.lng);
        addMarkers(crimeData, params.crimeType);
        
        var address = $('#address').val();
        var date = $('#crimeDatesSelect option:selected').text();
        var count = crimeData.length !== undefined ? crimeData.length : 0;
  
        $('#resultsInfo').html('Showing '+count+' '+(count === 1 ? 'crime' : 'crimes')+' for '+address+' ('+date+')');
        
    });
    
    //Event handler for 'Scroll to top' link
    $('a#scrollTop').click(function() {
        $('html').animate({scrollTop: 0}, 'slow');
        return false;
    });
    
});
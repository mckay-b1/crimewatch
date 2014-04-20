var colors = [
    '#FE2E2E',
    '#2FFE2F',
    '#2F2FFE',
    '#FEFE2F',
    '#FF00BF',
    '#FE962F',
    '#03FCFC',
    '#9A2EFE',
    '#886A08',
    '#FFFFFF',
    '#000000',
    '#A4A4A4',
    '#FE9696',
    '#96FE96',
    '#9696FE',
    '#CAFE96'
];

function reverseGeocode(lat, lng) {
    var address;
    
    $.ajax({
        url : 'https://maps.googleapis.com/maps/api/geocode/json?latlng='+lat+','+lng+'&sensor=false',
        type: 'GET',
        success: function() {
            var responseJSON = arguments[2].responseJSON;

            if (responseJSON.status==="ZERO_RESULTS") {
                return false;
            } else {
                address = responseJSON.results[0].formatted_address;
            }
        },
        async: false
    });
    
    return address;
}


function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y + 10,
        left: x + 10,
        border: '1px solid #007DC6',
        padding: '2px',
        'background-color': '#5FC6FF',
        opacity: 0.80
    }).appendTo('body').fadeIn(200);
}


function plotChart(element, data, chartType) {
    if (chartType === "" || chartType === "area") {
        $.plot(element, [data], {
            series: {
                lines: { 
                    show: true,
                    fill: true,
                    fillColor: {
                        colors:['rgba(198,0,26,0.5)', 'rgba(198,0,26,0.5)']
                    },
                    lineWidth: 4
                },
                points:{
                    show: true
                },
                color: '#C6001A',
                shadowSize: 0
            },
            xaxis: {
                mode: 'categories'
            },
            yaxis: {
                axisLabel: 'Number of crimes',
                tickDecimals: 0
            },
            grid: {
                backgroundColor: { colors: [ "#FFFFFF", "#FFFFFF"] },
                borderWidth: {
                    top: 1,
                    right: 1,
                    bottom: 4,
                    left: 4
                },
                hoverable: true
            },
            tooltip: true,
            tooltipOpts: {
                content: "%y crimes for %x",
                defaultTheme: false
            }
        });
    } else if (chartType === "pie") {
        $.plot(element, data, {
            series: {
                pie: { 
                    show: true,
                    highlight: {
                        opacity: 0.2
                    },
                    label: {
                        //threshold: 0.01
                    },
                    combine: {
                        //threshold: 0.01
                    },
                    stroke: {
                        color: '#666666',
                        width: 0
                    },
                    tilt: 1
                }
            },
            grid: {
                hoverable: true,
                clickable: true
            },
            tooltip: true,
            tooltipOpts: {
                content: "%s: %p.1%", // show percentages, rounding to 2 decimal places
                shifts: {
                    x: 20,
                    y: 0
                }
            }
        });
    }
    
}


function objectArrayLookup(arr, name) {
    for (var i = 0; i < arr.length; i++){
        if (arr[i].name === name) {
            return i;
        }
    }
    return false;
}

function generatePieChartData(statisticsData, date) {
    var chartData = [];
    var newData = [];
    
    if (date !== undefined) {
        $.each(statisticsData[date].categories, function(a, b) {
            var o = new Object;
            o.name = a;
            o.count = b.count;
            o.nicename = b.nicename;
            
            if (b.count > 0) {
                newData.push(o);
            }
        });
    } else {
        //Reformulate data in tallied categories
        $.each(statisticsData, function(a, b) {
            $.each(b.categories, function(c, d) {
                var index = objectArrayLookup(newData, c);

                if (index !== false) {
                    newData[index].count += d.count;
                } else {
                    var o = new Object;
                    o.name = c;
                    o.count = d.count;
                    o.nicename = d.nicename;

                    newData.push(o);
                }
            });
        });
    }

    //Sort the data by category tally descending
    newData.sort(function(a, b) {
        return b.count > a.count;
    });

    var n = 0;

    $.each(newData, function(k, v) {
        if (v.count > 0) {
            chartData.push({ label: v.nicename,  data: v.count, color: colors[n] });
            n++;
        }
    });
    
    return chartData;
}


function generateAreaChartData(statisticsData) {
    var chartData = [];
    
    //Formulate chart data in [[month, count], [month, count]] format
    $.each(statisticsData, function(k, v) {
        var date = $.format.date(new Date(k), "MMM yy");
        chartData.push([date, v['count']]);
    });
    
    return chartData;
}


function geocode(address) {
    var data = {};
    
    //Use Google's geo-coding service to get lat/lng data for input
    $.ajax({
        url : 'https://maps.googleapis.com/maps/api/geocode/json?address='+address+'&sensor=false',
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


$(document).ready(function() {
    var lat = $('#addressLat').val();
    var lng = $('#addressLng').val();
    
    if (lat !== "" && lng !== "") {
        //Add the formatted address to the page
        var address = reverseGeocode(lat, lng);
        $('#currentAddress').html(address);
    }

    //Declare/initialise variables and elements
    var errorMessage = "";
    var errorBox = $('.page-statistics .errorBox');
    errorBox.hide();
    errorBox.html("");
    
    var ajaxLoader =  $('#datesForm .ajaxLoader');
    
    var statisticsData;
    
    //Set the 'From date' select to 6 months before the 'To date' on page load
    $("#fromSelect").prop('selectedIndex', 5);
    
    //Event handler for Search form submit button
    $('#searchButton').click(function(e) {
        //Validate against empty input
        if ($('#searchValue').val().length === 0 ) {
            alert('Please enter a value before attempting to search!');
        } else {
            $('#searchForm').submit();
        }
    });
    
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        
        $('#searchForm .ajaxLoader').removeClass('hidden');
        
        var address = $('#searchValue').val();

        address = encodeURIComponent(address);

        //Geocode address into into lat/lng data
        var data = geocode(address).geometry;

        if (!data) {
            //If no data was returned from attempted geocoding
            $('#feedback').html('No data was found for the specified address! Please check that the address you entered is valid.');
            $('#feedback').show();
            return false;
        } else {
            document.location.href='/statistics?lat='+data.lat+'&lng='+data.lng;
        }
    });
    
    //Event handler for Geolocate button
    $('#geolocateButton').click(function(e) {
        e.preventDefault();
        
        $('#searchForm .ajaxLoader').removeClass('hidden');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                document.location.href='/statistics?lat='+lat+'&lng='+lng;
            });
        } else {   
            alert('Sorry, your browser doesn\'t support geolocation!');   
        }
    });

    //Event handler for Crime Levels submit button
    $('#datesButton').submit(function(e) {
        e.preventDefault();

        $('#datesForm').submit();
    });
 
    //Event handler for Crime Levels form submit
    $('#datesForm').submit(function(e) {
        e.preventDefault();
        
        //Fade out the sections for content refresh
        $('#crimeLevels').fadeOut();
        $('#crimeTypes').fadeOut();
        $('#statisticsData').fadeOut();

        ajaxLoader.removeClass('hidden');
        
        var data = $(this).serializeArray();
        
        $.ajax({
            url : 'ajax/getStatistics.php',
            type: 'POST',
            data: data,
            success: function(response) {
                response = $.parseJSON(response);

                if (response.success) {
                    //CRIME LEVELS SECTION
                    $('#crimeLevels').fadeIn();

                    statisticsData = response.data;

                    var chartData = [];
                    
                    chartData = generateAreaChartData(statisticsData);

                    $('#crimeLevelsChart').fadeIn();

                    plotChart($("#crimeLevelsChart"), chartData, "area");
                    
                    //Populate the category filter based on returned categories
                    $('#crimeLevelsFilter').html(response.categoryFilter);

                    $('#crimeLevelsFilter').fadeIn();
                    
                    //CRIME TYPES SECTION
                    $('#crimeTypes').fadeIn();

                    chartData = [];

                    chartData = generatePieChartData(statisticsData);

                    $('#crimeTypesChart').fadeIn();

                    plotChart("#crimeTypesChart", chartData, "pie");

                    $('#crimeTypesFilter').html(response.typeFilter);

                    $('#crimeTypesFilter').fadeIn();
                    
                    //STATISTICS DATA SECTION
                    $('#statisticsTable').html(response.statisticsTable);

                    $('#statisticsData').fadeIn();
                } else {
                    errorBox.show();
                    errorBox.html(response.message);
                }
            },
            async: false
        });
        
        ajaxLoader.addClass('hidden');
    });
    
    
    $('.page-statistics').delegate('#crimeLevelsFilter select', 'change', function(e) {
        ajaxLoader.removeClass('hidden');
        
        var selected = e.target.value;
        
        var chartData = [];
        
        if (selected === "") {
            chartData = generateAreaChartData(statisticsData);
        } else {
            $.each(statisticsData, function(k, v) {
                var date = $.format.date(new Date(k), "MMM yy");
                chartData.push([date, v['categories'][selected].count]);
            });   
        }
        
        plotChart($("#crimeLevelsChart"), chartData, "area");
        
        ajaxLoader.addClass('hidden');
    });
    
        
    $('.page-statistics').delegate('#crimeTypesFilter select', 'change', function(e) {
        ajaxLoader.removeClass('hidden');
        
        var selected = e.target.value;
        
        var chartData = [];
        
        if (selected === "") {
            chartData = generatePieChartData(statisticsData);
        } else {
            chartData = generatePieChartData(statisticsData, selected);
        }
        
        plotChart($("#crimeTypesChart"), chartData, "pie");
        
        ajaxLoader.addClass('hidden');
    });
    
    //Event handler for 'Scroll to top' link
    $('a#scrollTop').click(function() {
        $('html').animate({scrollTop: 0}, 'slow');
        return false;
    });
    
});
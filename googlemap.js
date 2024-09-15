function initMap() {
    // Map options
    var options = {
    zoom: 7,
    center: {lat: 48.730556, lng: 19.457222}
    }
    // New map
    var map = new google.maps.Map(document.getElementById('map'), options);

    google.maps.event.addListener(map, 'click', function (event) {
    // Add marker
    addMarker({coords: event.latLng});
    });
    // Loop through markers
    for (var i = 0; i < passedsirky.length; i++) {
    switch (passedvucky[i]) {
    case "1" :
    color = 'red';
    break;
    case "2" :
    color = 'green';
    break;
    case "3" :
    color = 'green';
    break;
    case "4" :
    color = 'blue';
    break;
    case "5" :
    color = 'pink';
    break;
    case "6" :
    color = 'yellow';
    break;
    case "7" :
    color = 'purple';
    break;
    case "8" :
    color = 'orange';
    break;
    }
    // Add marker
    var markers =
    {
    coords: {lat: Number(passedsirky[i]), lng:
   Number(passeddlzky[i])},
    content: passednazvy[i],
    };
    addMarker(markers, color);
    }
    // Add Marker Function
    function addMarker(props, color) {
    let url = "http://maps.google.com/mapfiles/ms/icons/";
    url += color + "-dot.png";
    var marker = new google.maps.Marker({
    position: props.coords,
    map: map,
    icon: {
    url: url
    }
    });
    // Check content
    if (props.content) {
    var infoWindow = new google.maps.InfoWindow({
    content: props.content
    });
    marker.addListener('click', function () {
    infoWindow.open(map, marker);
    $('#mesto').val(props.content);
    $('#vuc').attr('disabled',true);
    });
    }
    }
    function pinSymbol(color) {
    return {
    path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
    fillColor: color,
    fillOpacity: 1,
    strokeColor: '#000',
    strokeWeight: 2,
    scale: 1
    };
    }
   }
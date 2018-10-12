//Initializing map parameters
function initialize() {
	var hlat = parseFloat(helper.lat)||39.0000;
	var hlng = parseFloat(helper.lng)||22.0000;
	var hzoom = parseInt(helper.zoomlevel)||5;
	
	var hlat=document.getElementById("latitude").value;
	var hlng=document.getElementById("longitude").value;
	
	var myLatLng = new google.maps.LatLng(hlat,hlng);
	var mapOptions = {
	  center: myLatLng,
	  zoom: hzoom
	};
	var map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
	
	var marker = new google.maps.Marker({position: myLatLng, map: map, draggable: true});
	marker.setMap(map);
	
	//event occurs when dragging marker on map
	google.maps.event.addListener(marker,'drag',function(event) {
		placeMarker(event.latLng);									 
	});
	
	//event occurs when changing zoom level of map
	google.maps.event.addListener(map,'zoom_changed',function(event) {	
		var z = map.getZoom();
		document.getElementById("zoom").value = z;
		
	});
	
	//this function calls when marker position changes on map
	function placeMarker(location) {
		if (marker == undefined){
			  marker = new google.maps.Marker({
					position: location,
					map: map,
					animation: google.maps.Animation.DROP
				});
		 }
		 else{
				marker.setPosition(location);
		 }
		 map.setCenter(location);
		 document.getElementById("latitude").value = location.lat();
		 document.getElementById("longitude").value = location.lng();
	}

}
//event occurs when window loads first time and calls initialize function
google.maps.event.addDomListener(window, 'load', initialize);
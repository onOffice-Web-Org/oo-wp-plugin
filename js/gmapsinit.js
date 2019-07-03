function initGoogleMaps(element, properties) {
	var map = new google.maps.Map(element, properties);
	return map;
}


function getMarkerConfig(config, map) {
	var latLng = new google.maps.LatLng(config.lat, config.lng);
	var newConfig = {
		position: latLng,
		icon: config.icon,
		map: map,
		title: config.title
	};
	return newConfig;
}
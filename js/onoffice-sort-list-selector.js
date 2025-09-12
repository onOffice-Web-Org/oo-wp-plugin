jQuery(document).ready(function($) {
	$('.onofficeSortListSelector').change(function() {
		let listviewID = $(this).data('sort-listviewid');
		let selectedValue = $(this).val();
		let sortUrlParameter = {};

		if (selectedValue !== "") {
			const parts = selectedValue.split("#");
			sortUrlParameter[`sortby_id_${listviewID}`] = parts[0];
			sortUrlParameter[`sortorder_id_${listviewID}`] = parts[1];
		}

		const url = new URL(window.location.href);
		const searchParams = url.searchParams;

		for (const key in sortUrlParameter) {
			if (sortUrlParameter.hasOwnProperty(key)) {
				if (sortUrlParameter[key] === "") {
					searchParams.delete(key);
				} else {
					searchParams.set(key, sortUrlParameter[key]);
				}
			}
		}

		let hash = url.hash;

		// Add a slash to the hash if it exists and doesn't already have one
		if (hash && !hash.startsWith('/')) {
			hash = '/' + hash;
		}

		// Construct the new URL by combining the original parts in the correct order
		// This ensures the query string is always before the hash
		const newLocation = `${url.origin}${url.pathname}?${searchParams.toString()}${hash}`;

		// Update the browser's location, which triggers a reload
		window.location.href = newLocation;
	});
});
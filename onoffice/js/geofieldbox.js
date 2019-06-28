var onOffice = onOffice || {};

onOffice.geoSettingsBox = function() {};

onOffice.geoSettingsBox.prototype.addRemoveBoxEvent = function(geoRemoveButton) {
    if (geoRemoveButton !== null) {
        geoRemoveButton.addEventListener("click", function() {
            onOffice.geoSettingsBox.getGeoFieldBox().hidden = true;
        });
    }
};

onOffice.geoSettingsBox.prototype.addCheckBoxExistsEvent = function() {
    var instance = this;
    document.addEventListener("addFieldItem", function(e) {
        if (e.detail.fieldname === "geoPosition") {
            onOffice.geoSettingsBox.getGeoFieldBox().hidden = false;
            var geoRemoveButton = e.detail.item.querySelector("div.submitbox > a.item-delete-link");
            instance.addRemoveBoxEvent(geoRemoveButton);
        }
    });
};

onOffice.geoSettingsBox.getGeoFieldBox = function() {
    return document.querySelector("#geofields.postbox");
};

(function() {
    if (document.querySelector("#menu-item-geoPosition") === null) {
        onOffice.geoSettingsBox.getGeoFieldBox().hidden = true;
    }

	var geosettingsUiHide = new onOffice.geoSettingsBox();
    var geoRemoveButton = document.querySelector("#menu-item-geoPosition > div.submitbox > a.item-delete-link");
	geosettingsUiHide.addRemoveBoxEvent(geoRemoveButton);
	geosettingsUiHide.addCheckBoxExistsEvent();
})();
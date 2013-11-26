/**
 * jQuery Hotkey Functionality
 *
 * @package WordPress Hotkeys
 * @since   1.0.0
 */

// Get admin menu items and admin URL from PHP
var hotkeys = phpHotkeys;
var adminUrl = phpAdminUrl;

//
jQuery(document).ready(function() {

	jQuery.each(hotkeys, function(key, value) {
	    jQuery.hotkeys.add(key, {type:'keypress', propagate: true}, function() {
	    	window.location = adminUrl + hotkeys[key];
	    });
	});


});
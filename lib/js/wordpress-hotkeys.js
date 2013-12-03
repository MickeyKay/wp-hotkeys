/**
 * jQuery Hotkey Functionality
 *
 * @package WordPress Hotkeys
 * @since   1.0.0
 */

// Get PHP variables
var menuItems = phpVars['menuItems'];
var adminUrl = phpVars['adminUrl'];
var showHints = phpVars['showHints'];
var closeHoverHotkey = phpVars['closeHoverHotkey'];

jQuery(document).ready(function() {

	// Setup top-level menu hotkeys
	whHotkeys();

});

// Setup top level hotkeys
function whHotkeys() {

	jQuery.each(menuItems, function() {

		var item = jQuery(this);
		var url = item[0]['url'];
		var hotkey = item[0]['hotkey'];
		var subItems = item[0]['sub_items'];

		if (url && hotkey) {

			// Check top-level item
	    	var topLevelLinks = jQuery('#adminmenu > li > a');
	    	var menuItem;
	    	var menuLink;

	    	jQuery.each(topLevelLinks, function() {
	    		var href = jQuery(this).attr('href');
	    		var hrefLength = href.length;
	    		var urlLength = url.length;

	    		// Test menu links for exact URL match (since some URLs are absolute and others are relative)
	    		if (href.indexOf(url) >= 0 && hrefLength == href.indexOf(url) + urlLength)
	    			menuLink = jQuery(this);

	    	});

	    	// Add top-level hotkey hints
	    	if ( 1 == showHints && 1 > menuLink.find('.hotkey-hint').length )
	    		menuLink.find('.wp-menu-name').prepend('<span class="hotkey-hint">[' + hotkey + '] </span>' );

	    	// Check sub-level items
	    	if (subItems) {

		    	jQuery.each(subItems, function() {

					var subItem = jQuery(this);
					var subUrl = subItem[0]['url'];
					var subHotkey = subItem[0]['hotkey'];

					if (subUrl && subHotkey) {

						var subLevelLinks = jQuery('#adminmenu > li > ul > li > a');
				    	var subMenuItem;
				    	var subMenuLink;

				    	jQuery.each(subLevelLinks, function() {
				    		var subHref = jQuery(this).attr('href');
				    		var subHrefLength = subHref.length;
				    		var subUrlLength = subUrl.length;

				    		// Test menu links for exact URL match (since some URLs are absolute and others are relative)
				    		if (subHref.indexOf(subUrl) >= 0 && subHrefLength == subHref.indexOf(subUrl) + subUrlLength)
				    			subMenuLink = jQuery(this);
				    	});

				    	// Add sub-level hotkey hints
				    	if ( 1 == showHints && 1 > subMenuLink.find('.hotkey-hint').length )
				    		subMenuLink.prepend('<span class="hotkey-hint">[' + subHotkey + '] </span>' );

					}

				});
		    }

			// Add hotkey functionality
		    jQuery.hotkeys.add(hotkey, {type:'keypress', propagate: true}, function() {
		    	
		    	// Only execute if we're not typing
	    		if ( ! jQuery('*:focus').is('textarea, input') ) {

			    	// Close any open sub-menus
			    	jQuery('#adminmenu li').removeClass('opensub');

			    	// Reset in case another top-level item is open
			    	whReset();

			    	// Open this sub-menu
			    	menuLink.parents('li').addClass('opensub');

			    	// Setup sub-menu hotkeys if they exist, otherwise setup top level item only
			    	if (subItems) {
			    		whSubHotkeys(item);
			    	}
			    	else
			    		window.location = adminUrl + url;

			    }

		    });

		}
		
	});

	// Space - closes sub menu and resets inital hotkeys
	if (closeHoverHotkey) {
		jQuery.hotkeys.add(closeHoverHotkey, {type:'keydown', propagate: false}, function() {
	    	whReset();
	    });
	}

}

// Setup top level hotkeys
function whSubHotkeys(topItem) {

	var subItems = topItem[0]['sub_items'];

	jQuery.each(subItems, function() {

		var item = jQuery(this);
		var url = item[0]['url'];
		var hotkey = item[0]['hotkey']

		whSetupHotkey(hotkey, url);
		
	});

}

// Setup hotkey
function whSetupHotkey(hotkey, url) {

	if (hotkey) {

	    jQuery.hotkeys.add(hotkey, {type:'keypress', propagate: true}, function() {
	    	
	    	// Only execute if we're not typing
	    	if ( ! jQuery('*:focus').is('textarea, input') )
	    		window.location = adminUrl + url;

	    });

	}
}

// Reset all hotkeys to their initial state
function whReset() {

	// Close all open sub-menus
	jQuery('#adminmenu li').removeClass('opensub');

	// Reset to initial (top-level) hotkeys
	whHotkeys();
}

// Prompt user to confirm reset
function whConfirmReset() {
	return confirm('Are you sure you want to reset plugin defaults?');
}
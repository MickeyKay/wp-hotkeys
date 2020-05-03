/**
 * WP Hotkeys jQuery functionality
 *
 * @package WP Hotkeys
 * @since   0.9.0
 */

topLevelLinks = '';
wphActiveHotkeys = new Array();

// Get PHP variables
var menuItems = phpVars['menuItems'];
var adminUrl = phpVars['adminUrl'];
var showHints = phpVars['showHints'];
var closeHoverHotkey = phpVars['closeHoverHotkey'];
var closeHoverHotkeyModifier = phpVars['closeHoverHotkeyModifier'];
if ( closeHoverHotkeyModifier && 0 != closeHoverHotkeyModifier )
	closeHoverHotkey = closeHoverHotkeyModifier + '+' + closeHoverHotkey;

// Setup top-level hotkeys
jQuery(document).ready(function() {

	// Setup link objects
	topLevelLinks = jQuery('#adminmenu > li > a');

	// Initialize hotkeys
	wphReset();
	
	// Reset hotkeys when exiting input or textarea
	jQuery('input, textarea, select, button').blur(function() {
		wphReset();
	});

	// Remove all hotkeys when in input or textarea
	jQuery('input, textarea, select, button').focus(function() {
		wphRemoveAllHotkeys();
	});

	// Reset button confirmation
	jQuery('.wh-reset').click(function() {
		return wphConfirmReset();
	});

});

// Reset all hotkeys to their initial state
function wphReset() {

	// Close all open sub-menus
	jQuery('#adminmenu li').removeClass('opensub wph-active');

	// Remove arrow hotkey functionality
	wphRemoveAllHotkeys();

	// Setup functionality to close sub-menus with 1. escape key or 2. click
	wphManualResets();
	
	// Reset to initial (top-level) hotkeys
	wphTopHotkeys();
}

// Check to make sure user is not trying to type
function not_typing() {
	if (! jQuery('textarea, input, select, button').is(':focus') )
		return true;
	else
		return false;
}

// Setup top level hotkeys
function wphTopHotkeys() {

	jQuery.each(menuItems, function() {

		var item = jQuery(this);
		var url = item[0]['url'];
		var hotkey = item[0]['hotkey'];
		var modifier = item[0]['modifier'];
		var subItems = item[0]['sub_items'];

		if ( modifier && 0 != modifier )
			hotkey = modifier + '+' + hotkey;

		if ( url && hotkey ) {

			// Locate top level menu item
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
	    	if ( menuLink && 1 == showHints && 1 > menuLink.find('.hotkey-hint').length )
	    		menuLink.find('.wp-menu-name').prepend('<span class="hotkey-hint">[' + hotkey + '] </span>' );

	    	// Check sub-level items
	    	if (subItems) {

	    		jQuery.each(subItems, function() {

	    			var subItem = jQuery(this);
	    			var subUrl = subItem[0]['url'];
	    			var subHotkey = subItem[0]['hotkey'];
					var subModifier = subItem[0]['modifier'];

					if ( subModifier && 0 != subModifier )
						subHotkey = subModifier + '+' + subHotkey;

	    			if ( subUrl && subHotkey ) {

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
				    	if ( subMenuLink && 1 == showHints && 1 > subMenuLink.find('.hotkey-hint').length )
				    		subMenuLink.prepend('<span class="hotkey-hint">[' + subHotkey + '] </span>' );

				    }

				});
	    	}

			// Add hotkey functionality
			if ( not_typing() )
				wphSetupHotkey(hotkey, url, item, menuLink, subItems);

		}
		
	});

}

// Setup top level hotkeys
function wphSubHotkeys(topItem) {

	var subItems = topItem[0]['sub_items'];

	jQuery.each(subItems, function() {

		var item = jQuery(this);
		var url = item[0]['url'];
		var hotkey = item[0]['hotkey']
		var modifier = item[0]['modifier'];

		if ( modifier && 0 != modifier )
			hotkey = modifier + '+' + hotkey;

		wphSetupHotkey(hotkey, url);
		
	});

}

// Setup arrow functionality
function wphArrowHotkeys() {
	wphSetupHotkey('up');
	wphSetupHotkey('down');
	wphSetupHotkey('right');
	wphSetupHotkey('left');
	wphSetupHotkey('return');
}

// Setup individual hotkey
function wphSetupHotkey(hotkey, url, item, menuLink, subItems) {

	if ( hotkey ) {

		var type = 'keydown';
		var propagate = false;

		jQuery.hotkeys.add(hotkey, {type:type, propagate:propagate}, function() {

	    	// Only execute the hotkey if we're not typing
	    	if ( not_typing() ) {
	    		
	    		// Individual top-level hotkeys assigned to url
	    		if ( hotkey && url && menuLink ) {
	    			// Close any open sub-menus
			    	jQuery('#adminmenu li').removeClass('opensub wph-active');

			    	// Reset in case another top-level item is open
			    	wphReset();

			    	// Open this sub-menu and indicate active link
			    	menuLink.parents('li').addClass('opensub wph-active');

			    	// Setup sub-menu hotkeys if they exist, otherwise setup top level item only
			    	if ( subItems ) {
			    		// Sub hotkeys
			    		wphSubHotkeys(item);
			    		
			    		// Arrow hotkey functionality
			    		wphArrowHotkeys();
			    	}
			    	else {
			    		window.location = adminUrl + url;
			    	}
	    		}

	    		// Individual sub-menu hotkeys assigned to url
	    		if ( hotkey && url && ! menuLink ) {
	    			window.location = adminUrl + url;
	    		}

	    		// Directional hotkeys
	    		if ( 'up' == hotkey && jQuery('.wph-active').prevAll('li').has('a').length > 0 ) {
	    			jQuery('.wph-active').removeClass('opensub wph-active').prevAll('li').has('a').first().addClass('opensub wph-active');
	    		}

	    		if ( 'down' == hotkey && jQuery('.wph-active').nextAll('li').has('a').length > 0 ) {
	    			jQuery('.wph-active').removeClass('opensub wph-active').nextAll('li').has('a').first().addClass('opensub wph-active');
	    		}

	    		if ( 'right' == hotkey && jQuery('.wph-active').children('ul').length > 0 ) {
	    			jQuery('.wph-active').removeClass('wph-active').find('ul a').first().parent('li').addClass('wph-active');
	    		}

	    		if ( 'left' == hotkey && jQuery('.wph-active').parents('ul').parent('li').length > 0 ) {
	    			jQuery('.wph-active').removeClass('wph-active').parents('ul').parent('li').addClass('opensub wph-active');
	    		}

	    		if ( 'return' == hotkey ) {
	    			window.location = jQuery('.wph-active > a').attr('href');
	    		}

	    		// Close hover hotekey
	    		if ( closeHoverHotkey == hotkey) {
	    			wphReset();
	    		}
	    	}

	    });

		// Add to active hotkeys array for reference when removing our hotkeys
		wphActiveHotkeys.push(hotkey);
	}

}

// Unset all hotkey functionality
function wphRemoveAllHotkeys() {
	jQuery.each(wphActiveHotkeys, function(i,v) {
		jQuery.hotkeys.remove(v);
	});
}

// Manual methods of escaping/leaving hotkey functionality
function wphManualResets() {

	// 1. Escape key
	wphSetupCloseHoverHotkey();

	// 2. Click anywhere off the menu
	jQuery('html').unbind('click').click(function( event ) {
		wphReset();
	});
}

// Close hover hotkey functionality
function wphSetupCloseHoverHotkey() {
	if (closeHoverHotkey) {
		wphSetupHotkey(closeHoverHotkey);
	}
}

// Prompt user to confirm reset
function wphConfirmReset() {
	return confirm('Are you sure you want to reset plugin defaults?');
}
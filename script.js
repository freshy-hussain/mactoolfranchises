jQuery( document ).ready( function( $ ) {
	// Insert a single .sub-menu-toggle element before each menu link with children
	$( "#main-header #mobile_menu.et_mobile_menu .menu-item-has-children > a, #et-boc header .et_mobile_menu .menu-item-has-children > a" ).each(function () {
		if ($(this).siblings('.sub-menu-toggle').length === 0) { 
			$("<div class='sub-menu-toggle'></div>").insertBefore($(this));
		}
	});

	// Toggle the 'popped' class when the .sub-menu-toggle is clicked
	$(document).on("click", "#main-header #mobile_menu.et_mobile_menu .sub-menu-toggle, #et-boc header .et_mobile_menu .sub-menu-toggle", function() {
		$(this).toggleClass("popped");
	});
});

// save UTM parameters to local storage for users
(function() {
  const params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
  const url = new URLSearchParams(window.location.search);

  params.forEach(p => {
    if (url.get(p)) {
      localStorage.setItem(p, url.get(p));
    } else if (!url.get(p) && localStorage.getItem(p)) {
      // Add UTM params back into URL for Gravity Forms to pick up
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.set(p, localStorage.getItem(p));
      window.history.replaceState({}, '', currentUrl);
    }
  });
})();
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

// If URL parameters are present, save them to local store. Then, insert in Gravity Forms
document.addEventListener('DOMContentLoaded', function() {
  const params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'txtsourcedetails'];
  const url = new URLSearchParams(window.location.search);

  // field parameters from URL and save to local storage
  params.forEach(p => {
    if (url.get(p) && !(p == 'txtsourcedetails')) {
      localStorage.setItem(p, url.get(p));
    } 
  });

  // 
  params.forEach(function(field) {
    let value = localStorage.getItem(field);

    // if (field == 'ddlsource') {
    //   value = localStorage.getItem('utm_campaign');
    // } 
    if (field == 'txtsourcedetails') {
      value = localStorage.getItem('utm_medium');
    }

    if (!value) return; // Skip if no stored value

    const wrapper = document.querySelector('.field-' + field);
    if (!wrapper) return; // Skip if that field doesn't exist on this form

    // Find the input inside the wrapper and insert
    const input = wrapper.querySelector('input');
    if (input) {
      input.value = value;
    }
  });
});
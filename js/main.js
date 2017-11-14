/**
 * @file plugins/themes/default/js/main.js
 *
 *
 * @brief Handle JavaScript functionality unique to this theme.
 */
(function($) {


	/* dainst */

	// spotlights (couldnt find the original implementation so fuck it */

	function changeHighlight(e) {
		var spotlightNumber = jQuery(e.currentTarget).data('spotlight');
		console.log(spotlightNumber);
		jQuery(".cmp_spotlights ul.spotlights li.current").toggleClass("current");
		jQuery(".cmp_spotlights li.spotlight_" + spotlightNumber).toggleClass("current");
		jQuery(".cmp_spotlights ul.list li.current").toggleClass("current");
		jQuery(e.currentTarget).parent("li").toggleClass("current");

	}
	jQuery("a[data-spotlight]").mouseenter(changeHighlight).click(changeHighlight);



	// (register fuck) da-clickedy-clack
	function showTermsModal(e) {
		e.preventDefault();
		showModal();
	}
	jQuery('#register button[type="submit"]').click(showTermsModal);

	function confirmTermsModal(e) {
		jQuery('#register').submit()
	}
	jQuery('#modal.terms #modal-dialog-ok').click(confirmTermsModal);


	// modal functioniality wich could be reused fpr some otehr modal
	function showModal(e) {
		jQuery('#modal').toggle(true);
	}
	function hideModal(e) {
		jQuery('#modal').toggle(false);
	}
	function escapeModal(e) {
		if (jQuery('#modal').hasClass("escapeable")) {
			hideModal(e);
		}
	}
	jQuery('#modal-dialog-esc').click(hideModal);
	jQuery('body').keypress(function(e) {
		if (e.key == "Escape") {
			escapeModal();
		}
	});
	jQuery('#modal').click(escapeModal);
	jQuery('#modal .dialog').click(function(e) {
		e.stopImmediatePropagation();
		e.stopPropagation();
	});



	/* from the default theme */

	// Modify the Chart.js display options used by UsageStats plugin
	document.addEventListener('usageStatsChartOptions.pkp', function(e) {
		e.chartOptions.elements.line.backgroundColor = 'rgba(0, 122, 178, 0.6)';
		e.chartOptions.elements.rectangle.backgroundColor = 'rgba(0, 122, 178, 0.6)';
	});

	// Initialize tag-it components
	//
	// The tag-it component is used during registration for the user to enter
	// their review interests. See: /templates/frontend/pages/userRegister.tpl
	if (typeof $.fn.tagit !== 'undefined') {
		$('.tag-it').each(function() {
			var autocomplete_url = $(this).data('autocomplete-url');
			$(this).tagit({
				fieldName: $(this).data('field-name'),
				allowSpaces: true,
				autocomplete: {
					source: function(request, response) {
						$.ajax({
							url: autocomplete_url,
							data: {term: request.term},
							dataType: 'json',
							success: function(jsonData) {
								if (jsonData.status == true) {
									response(jsonData.content);
								}
							}
						});
					},
				},
			});
		});

		/**
		 * Determine if the user has opted to register as a reviewer
		 *
		 * @see: /templates/frontend/pages/userRegister.tpl
		 */
		function isReviewerSelected() {
			var group = $('#reviewerOptinGroup').find('input');
			var is_checked = false;
			group.each(function() {
				if ($(this).is(':checked')) {
					is_checked = true;
					return false;
				}
			});

			return is_checked;
		}

		/**
		 * Reveal the reviewer interests field on the registration form when a
		 * user has opted to register as a reviewer
		 *
		 * @see: /templates/frontend/pages/userRegister.tpl
		 */
		function reviewerInterestsToggle() {
			var is_checked = isReviewerSelected();
			if (is_checked) {
				$('#reviewerInterests').addClass('is_visible');
			} else {
				$('#reviewerInterests').removeClass('is_visible');
			}
		}

		// Update interests on page load and when the toggled is toggled
		reviewerInterestsToggle();
		$('#reviewerOptinGroup input').click(reviewerInterestsToggle);
	}

})(jQuery);

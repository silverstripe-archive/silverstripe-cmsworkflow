(function($) {
	$('#FutureStateLink').livequery('click',
		function() {
			$('#FutureStateDatePopup').toggle();
			return false;
		}
	);

})(jQuery);

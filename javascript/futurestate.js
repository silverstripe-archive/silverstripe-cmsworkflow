(function($) {
	$('#FutureStateLink').live('click',
		function() {
			$('#FutureStateDatePopup').toggle();
			return false;
		}
	);
	
	$('#FutureStateDatePopup a.close').live('click',
		function() {
			$('#FutureStateDatePopup').hide();
			return false;
		}
	);
	
	$('#FutureStateGoLink').live('click',
		function() {
			var date = $('#FutureStateDate-date').val();
			var time = $('#FutureStateDate-time').val();
			var dateObj = new Date(date + ' ' + time);
			var urlDate = dateObj.getFullYear() + '-' + pad(dateObj.getMonth()+1) + '-' + pad(dateObj.getDate());
			urlDate += ' ' + pad(dateObj.getHours()) + ':' + pad(dateObj.getMinutes()) + ':00';
			
			var w = window.open(this.href + '?futureDate=' + urlDate);
			w.focus();
			
			return false;
		}
	);
	function pad(n){return n<10 ? '0'+n : n;}

})(jQuery);

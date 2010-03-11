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
			var date = $('#FutureStateDate_Date').val();
			// Javascript is silly, so we need to convert to US date format first
			if(date) {
				var dateParts = date.split('/');
				date = dateParts[1] + '/' + dateParts[0] + '/' + dateParts[2];
			} else {
				date = new Date();
				date = date.toDateString();
			}
			var time = $('#FutureStateDate_Time').val();
			var dateObj = new Date(date + ' ' + time);
			
			var urlDate = dateObj.getFullYear() + '-' + (dateObj.getMonth() + 1) + '-' + dateObj.getDate();
			urlDate += '+' + dateObj.getHours() + ':' + dateObj.getMinutes() + ':00';
			
			var w = window.open(this.href + '?futureDate=' + urlDate, windowName(this.target));
			w.focus();
			
			return false;
		}
	);

})(jQuery);

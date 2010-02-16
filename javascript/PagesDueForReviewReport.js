(function($) {

	// Hide all owner dropdowns except the one for the current subsite
	function showCorrectSubsiteIDDropdown(value) {
		var domid = 'OwnerID' + value;
		
		var ownerIDDropdowns = $('div.subsiteSpecificOwnerID');
		for(var i = 0; i < ownerIDDropdowns.length; i++) {
			if(ownerIDDropdowns[i].id == domid)
				$(ownerIDDropdowns[i]).show();
			else
				$(ownerIDDropdowns[i]).hide();
		}
		
	}	
	
	// Call method to show on report load
	$('#Form_EditForm_SubsiteIDWithOwner').livequery(
		function() {
			showCorrectSubsiteIDDropdown(this.value);
		}
	);
	
	// Call method to show on dropdown change
	$('#Form_EditForm_SubsiteIDWithOwner').livequery('change',
		function() {
			showCorrectSubsiteIDDropdown(this.value);
		}
	);

})(jQuery);

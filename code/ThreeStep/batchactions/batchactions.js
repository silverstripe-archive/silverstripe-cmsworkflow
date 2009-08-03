jQuery(document).ready(function() {
	var el = jQuery('.checkboxAboveTree:first').get(0);
	var html = '<div><input id="show_only_pages_awaiting_approval" type="checkbox"/><label for="show_only_pages_awaiting_approval">Show only pages awaiting approval</label></div>';
	el.innerHTML = el.innerHTML+html;
	var html = '<div><input id="show_only_pages_awaiting_publish" type="checkbox"/><label for="show_only_pages_awaiting_publish">Show only pages needing publishing</label></div>';
	el.innerHTML = el.innerHTML+html;

	ShowPagesAwaitingApproval = Class.create();
	ShowPagesAwaitingApproval.applyTo('#show_only_pages_awaiting_approval');
	ShowPagesAwaitingApproval.prototype = {
		initialize: function () {
		},
	
		onclick : function() {
			if(this.checked) { 
				$('sitetree').setCustomURL(SiteTreeHandlers.controller_url+'/getfilteredsubtree_awaiting_approval');
			} else {
				$('sitetree').clearCustomURL();
			}

			// We can't update the tree while it's draggable; it gets b0rked.
			var __makeDraggableAfterUpdate = false;
			if($('sitetree').isDraggable) {
				$('sitetree').stopBeingDraggable();
				__makeDraggableAfterUpdate = true;
			}
		
			var indicator = $('checkboxActionIndicator');
			indicator.style.display = 'block';
		
			$('sitetree').reload({
				onSuccess: function() {
					if(__makeDraggableAfterUpdate) $('sitetree').makeDraggable();
					indicator.style.display = 'none';
				},
				onFailure: function(response) {
					errorMessage('Could not update tree', response);
				}
			});
		}
	}
	
	ShowPagesAwaitingPublish = Class.create();
	ShowPagesAwaitingPublish.applyTo('#show_only_pages_awaiting_publish');
	ShowPagesAwaitingPublish.prototype = {
		initialize: function () {
		},
	
		onclick : function() {
			if(this.checked) { 
				$('sitetree').setCustomURL(SiteTreeHandlers.controller_url+'/getfilteredsubtree_awaiting_publish');
			} else {
				$('sitetree').clearCustomURL();
			}

			// We can't update the tree while it's draggable; it gets b0rked.
			var __makeDraggableAfterUpdate = false;
			if($('sitetree').isDraggable) {
				$('sitetree').stopBeingDraggable();
				__makeDraggableAfterUpdate = true;
			}
		
			var indicator = $('checkboxActionIndicator');
			indicator.style.display = 'block';
		
			$('sitetree').reload({
				onSuccess: function() {
					if(__makeDraggableAfterUpdate) $('sitetree').makeDraggable();
					indicator.style.display = 'none';
				},
				onFailure: function(response) {
					errorMessage('Could not update tree', response);
				}
			});
		}
	}
});
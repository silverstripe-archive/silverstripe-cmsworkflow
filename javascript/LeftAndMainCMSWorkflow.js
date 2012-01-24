Behaviour.register({
	'.TableListField .externallink' : {
		onclick: function(e) {
			window.open(e.target.href);
			Event.stop(e);
			return false;
		}
	}
});


CMSWorkflow = {
	setOption: function(key, value) {
		if (typeof(this.data) == 'undefined') {
			this.data = {};
		}
		this.data[key] = value;
	},
	getOption: function(key) {
		if (typeof(this.data) != 'undefined' && typeof(this.data[key]) != 'undefined') {
			return this.data[key];
		}
		return null;
	},
	/**
	 * Prompt for input from the user and then submit the given form via ajax.
	 */
	submitWithPromptedMessage : function(form, button, msgVar, msgPrompt) {
		var messageEl = CMSWorkflow.createPromptElement(msgVar, msgPrompt);
		if (!messageEl) {
			return;
		}
		form.appendChild(messageEl);

		Ajax.SubmitForm(form, button, {
			onSuccess: Ajax.Evaluator,
			onFailure: ajaxErrorHandler
		});
		
		// Once Ajax.SubmitForm has been calld, this element is no longer necessary		
		form.removeChild(messageEl);
	},
	
	createPromptElement: function(varName, promptText) {
		var message = prompt(promptText, "");
		if (message === null) {
			// User canceled prompt box
			return null;
		}
		var messageEl = document.createElement("input");
		messageEl.type = "hidden";
		messageEl.name = varName;
		messageEl.value = message;
		return messageEl;
	},
	
	/**
	 * Simple behaviour for an ajax button
	 */
	WorkflowButton : {
		onclick: function() {
			$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoExpiryTZConverter_TZ'] = true;
			$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoExpiryTZConverter_From_Date'] = true;
			$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoExpiryTZConverter_From_Time'] = true;
			$('Form_EditForm').changeDetection_fieldsToIgnore['DeletionScheduling'] = true;
			$('Form_EditForm').changeDetection_fieldsToIgnore['WorkflowComment'] = true;
			
			if (EmbargoExpiry.embargoUnsaved || EmbargoExpiry.expiryUnsaved) {
				if (!confirm('Your embargo/expiry date changes have not been saved. Do you wish to continue?')) {
					return false;
				}
			}
			
			if ($('Form_EditForm').isChanged()) {
				if(!confirm('You have unsaved changes. You will lose them if you click OK.')) return false;
			}
			
			Ajax.SubmitForm($('Form_EditForm'), this.name, {
				onSuccess: Ajax.Evaluator,
				onFailure: ajaxErrorHandler
			});
			return false;
		}
	},
	
	showHideExpiry: {
		onclick: function() {
			if ($('deleteImmediate').checked) {
				$('expiryField').style.display = 'none';
			} else {
				$('expiryField').style.display = 'block';
			}
		}
	}
};

Behaviour.register({
	'#deleteImmediate' : CMSWorkflow.showHideExpiry,
	'#deleteLater' : CMSWorkflow.showHideExpiry,
	'#Form_EditForm_action_cms_requestedit' : CMSWorkflow.WorkflowButton,
	'#Form_EditForm_action_cms_approve' : CMSWorkflow.WorkflowButton,
	'#Form_EditForm_action_cms_deny' : CMSWorkflow.WorkflowButton,
	'#Form_EditForm_action_cms_cancel' : CMSWorkflow.WorkflowButton,
	'#Form_EditForm_action_cms_comment' : CMSWorkflow.WorkflowButton,
	'#Form_EditForm_action_cms_publish' : CMSWorkflow.WorkflowButton,
	'#WorkflowActions #Form_EditForm_action_cms_requestpublication' : CMSWorkflow.WorkflowButton,
	'#WorkflowActions #Form_EditForm_action_cms_requestdeletefromlive' : CMSWorkflow.WorkflowButton
});

// Create these actions
function action_cms_requestpublication_right(e) {
	if ($('Form_EditForm').isChanged()) {
		if(!confirm('You have unsaved changes. You will lose them if you click to continue requesting publication.'))
			return false;
	}
	
	return CMSWorkflow.submitWithPromptedMessage(
			$('Form_EditForm'), 'action_cms_requestpublication',
			'WorkflowComment',
			'Please comment on the change you are asking to have published.'
	);
}

function action_cms_requestdeletefromlive_right(e) {
	CMSWorkflow.submitWithPromptedMessage(
			$('Form_EditForm'), 'action_cms_requestdeletefromlive',
			'WorkflowComment',
			'Please comment on why you are asking to have this page deleted.'
	);
}

var EmbargoExpiry = {
	embargoUnsaved: false,
	expiryUnsaved: false,
	init: function() {
		jQuery('#EmbargoDate-date').change(EmbargoExpiry.embargoChange);
		jQuery('#EmbargoDate-time').change(EmbargoExpiry.embargoChange);
		jQuery('#ExpiryDate-date').change(EmbargoExpiry.expiryChange);
		jQuery('#ExpiryDate-time').change(EmbargoExpiry.expiryChange);

		EmbargoExpiry.fieldCheck();

		var ids = EmbargoExpiry.ids('embargo');
		if($(ids.dateField)) $('Form_EditForm').changeDetection_fieldsToIgnore[$(ids.dateField).name] = true;
		if($(ids.timeField)) $('Form_EditForm').changeDetection_fieldsToIgnore[$(ids.timeField).name] = true;
		ids = EmbargoExpiry.ids('expiry');
		if($(ids.dateField)) $('Form_EditForm').changeDetection_fieldsToIgnore[$(ids.dateField).name] = true;
		if($(ids.timeField)) $('Form_EditForm').changeDetection_fieldsToIgnore[$(ids.timeField).name] = true;

		$('Form_EditForm').changeDetection_fieldsToIgnore['ExpiryDate[TimeZone]'] = true;
		$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoDate[TimeZone]'] = true;
		
		EmbargoExpiry.embargoUnsaved = false;
		EmbargoExpiry.expiryUnsaved = false;
	},
	save: function(what, el) {
		EmbargoExpiry.fieldCheck();
		
		var url = 'admin/cms_setembargoexpiry?wfRequest='+$('WorkflowRequest_ID').value;
		var ids = EmbargoExpiry.ids(what);

		url += '&' + escape($(ids.dateField).name)+'='+escape($(ids.dateField).value)+'&' + escape($(ids.timeField).name) + '='+escape($(ids.timeField).value);
		if (what == 'embargo') {
			EmbargoExpiry.embargoUnsaved = false;
		} else if (what == 'expiry') {
			EmbargoExpiry.expiryUnsaved = false;
		}
		
		if ($(ids.timezoneField)) {
			var timezone = $(ids.timezoneField).options[$(ids.timezoneField).selectedIndex].value;
			url += '&' + escape($(ids.timezoneField).name) + '='+escape(timezone);
		}

		if ($(ids.dateField).value == '' || $(ids.timeField).value == '') {
			alert('You must fill out the '+what+' date and time fields');
			return;
		}
		
		$(el.id).className = 'action loading';
		new Ajax.Request(url, {
			method: 'get',
			onSuccess: function(t) {
				data = eval('('+t.responseText+')');
				if (data.status == 'success') {
					$(ids.wholeMessage).style.display = 'block';
					$(ids.dateTime).innerHTML = eval('data.message.'+ids.what);
				} else { EmbargoExpiry.errorAlert(data); }
			},
			onFailure: function(t) { EmbargoExpiry.errorAlert(data); },
			onComplete: function(t) { $(el.id).className = 'action'; }
		});	
	},
	reset: function(what, el) {
		var elIds = EmbargoExpiry.ids(what);
		var url = 'admin/cms_setembargoexpiry?wfRequest='+$('WorkflowRequest_ID').value;
		
		$(elIds.dateField).value = '';
		$(elIds.timeField).value = '';

		EmbargoExpiry.fieldCheck();
		
		if (what == 'embargo') {
			url += '&ResetEmbargo';
			EmbargoExpiry.embargoUnsaved = false;
		} else if (what == 'expiry') {
			url += '&ResetExpiry';
			EmbargoExpiry.expiryUnsaved = false;
		}
		
		new Ajax.Request(url, {
			method: 'get',
			onSuccess: function(t) {
				data = eval('('+t.responseText+')');
				if (data.status == 'success') {
					$(elIds.wholeMessage).style.display = 'none';
				} else {
					EmbargoExpiry.errorAlert(data);
				}
			},
			onFailure: function(t) { EmbargoExpiry.errorAlert(t); },
			onComplete: function(t) { $(el.id).className = 'action'; }
		});
	},
	errorAlert: function(data) {
		EmbargoExpiry.fieldCheck();
		alert("There was an error processing that request:\n\n"+data.message);
	},
	ids: function(forWhat) {
		switch(forWhat) {
			case 'expiry':
				return {
					resetButton: 'resetExpiryButton',
					saveButton: 'saveExpiryButton',
					dateField: 'ExpiryDate-date',
					timeField: 'ExpiryDate-time',
					timezoneField: 'ExpiryDate-timezone',
					wholeMessage: 'embargoExpiry-expiryStatus',
					dateTime: 'expiryDate',
					what: 'expiry'
				};
			case 'embargo':
				return {
					resetButton: 'resetEmbargoButton',
					saveButton: 'saveEmbargoButton',
					dateField: 'EmbargoDate-date',
					timeField: 'EmbargoDate-time',
					timezoneField: 'EmbargoDate-timezone',
					wholeMessage: 'embargoExpiry-embargoStatus',
					dateTime: 'embargoDate',
					what: 'embargo'
				};
		}
	},
	embargoChange: function() {
		EmbargoExpiry.embargoUnsaved = true;
		EmbargoExpiry.fieldCheck();
	},
	expiryChange: function() {
		EmbargoExpiry.expiryUnsaved = true;
		EmbargoExpiry.fieldCheck();
	},
	eButton: function(id) {
		Element.removeClassName(id, 'disabled');
		$(id).disabled = false;
	},
	dButton: function(id) {
		Element.addClassName(id, 'disabled');
		$(id).disabled = true;
	},
	fieldCheck: function() {
		if (EmbargoExpiry.originalValues === null) {
			EmbargoExpiry.originalValues = true;
		}

		ids = EmbargoExpiry.ids('embargo');
		// Only call this logic if the date field & save button exist, otherwise it's unnecessary
		if($(ids.dateField) && $(ids.saveButton)) {
			if ($(ids.dateField).value == '' || $(ids.timeField).value == '') {
				EmbargoExpiry.dButton(ids.saveButton);
				EmbargoExpiry.dButton(ids.resetButton);
			} else {
				EmbargoExpiry.eButton(ids.saveButton);
				EmbargoExpiry.eButton(ids.resetButton);
			}
		}
		
		ids = EmbargoExpiry.ids('expiry');
		// Only call this logic if the date field & save button exist, otherwise it's unnecessary
		if($(ids.dateField) && $(ids.saveButton)) {
			if ($(ids.dateField).value == '' || $(ids.timeField).value == '') {
				EmbargoExpiry.dButton(ids.saveButton);
				EmbargoExpiry.dButton(ids.resetButton);
				EmbargoExpiry.setExpiryWarning(false);
			} else {
				EmbargoExpiry.eButton(ids.saveButton);
				EmbargoExpiry.eButton(ids.resetButton);
				EmbargoExpiry.setExpiryWarning(true);
			}
		}
	},
	setExpiryWarning: function(shouldDisplay) {
		var el = $('ExpiryWorkflowWarning');
		if(el) el.style.display = shouldDisplay ? '' : 'none';
	}
};

Behaviour.register({
	'#embargoExpiry' : {
		initialize: EmbargoExpiry.init
	}
});

var autoSave_original = autoSave;
autoSave = function(confirmation, callAfter) {
	if (EmbargoExpiry.embargoUnsaved || EmbargoExpiry.expiryUnsaved) {
		if (!confirm('Your embargo/expiry date changes have not been saved. Do you wish to continue?')) {
			return false;
		}
	}
	return autoSave_original(confirmation, callAfter);
};

var save_original = CMSForm.prototype.save;
CMSForm.prototype.save = function(ifChanged, callAfter, action, publish) {
	if (EmbargoExpiry.embargoUnsaved || EmbargoExpiry.expiryUnsaved) {
		if (!confirm('Your embargo/expiry date changes have not been saved. Do you wish to continue?')) {
			_AJAX_LOADING = false;
			if($('Form_EditForm_action_save') && $('Form_EditForm_action_save').stopLoading) $('Form_EditForm_action_save').stopLoading();
			if($('Form_EditForm_action_publish') && $('Form_EditForm_action_publish').stopLoading) $('Form_EditForm_action_publish').stopLoading();
			return false;
		}
	}
	return save_original.call(this, ifChanged, callAfter, action, publish);
};

function action_publish_right(e) {
	var messageEl = null;
	if (CMSWorkflow.getOption('noPromptForAdmin')) {
		messageEl = document.createElement("input");
		messageEl.type = "hidden";
		messageEl.name = 'WorkflowComment';
	} else {
		messageEl = CMSWorkflow.createPromptElement('WorkflowComment', 'Please comment on this publication, if applicable.');
	}
	$('Form_EditForm').appendChild(messageEl);
	
	// Don't need to restore the button state after ajax success because the form is replaced completely
	var btn = jQuery('#Form_EditForm_action_publish');
	btn.val(ss.i18n._t('CMSMAIN.PUBLISHING')).addClass('loading').attr('disabled', 'disabled');
	$('Form_EditForm').save(false, null, 'cms_publishwithcomment', true);
	$('Form_EditForm').removeChild(messageEl);
}

/**
 * UI behaviour for the "Access" tab
 */
Behaviour.register({
	'#Form_EditForm_CanApproveType input': {
		initialize: function() {
			if(this.checked) this.click();
		},
		onclick: function() {
			$('ApproverGroups').style.display = (this.value == 'OnlyTheseUsers') ? 'block' : 'none';
		}
	},
	'#Form_EditForm_CanPublishType input': {
		initialize: function() {
			if(this.checked) this.click();
		},
		onclick: function() {
			$('PublisherGroups').style.display = (this.value == 'OnlyTheseUsers') ? 'block' : 'none';
		}
	}
});

(function($) {
	// Limit date range for start/end date on reports
	$('.ReportAdmin input[name="StartDate\[date\]"], .ReportAdmin .field.datetime input[name="EndDate\[date\]"]').live('change', function(e) {
		// Don't apply if ssDatepicker plugin isn't present (e.g. in SilverStripe 2.4),
		// or if no datepicker has been applied to the field (configurable via the "showcalendar" option in DateField.php)
		if(!$.fn.ssDatepicker || !$(this).data('datepicker')) return;
		
		var $thisField = $(this), isStart = $thisField.is('[name="StartDate\[date\]"]'), isEnd = !isStart;
			
		var holder = $thisField.parents('form'), 
			otherFieldName = isStart ? 'EndDate\[date\]' : 'StartDate\[date\]',
			$otherField = holder.find('input[name="' + otherFieldName + '"]');
			
		$otherField.ssDatepicker();
		$otherField.datepicker(
			'option', 
			isStart ? 'minDate' : 'maxDate', 
			$.datepicker.parseDate($otherField.datepicker('option', 'dateFormat'), $thisField.val())
		);
	});
}(jQuery));
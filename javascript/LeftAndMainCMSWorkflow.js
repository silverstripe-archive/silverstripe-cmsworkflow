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
			
			if ($('Form_EditForm').()) {
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
	originalValues: null,
	save: function(what, el) {
		EmbargoExpiry.fieldCheck();
		
		var url = 'admin/cms_setembargoexpiry?wfRequest='+$('WorkflowRequest_ID').value;
		var ids = EmbargoExpiry.ids(what);

		if (what == 'embargo') {
			url += '&EmbargoDate='+escape($(ids.dateField).value)+'&EmbargoTime='+escape($(ids.timeField).value);
			$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoDate[Date]'] = true;
			$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoDate[Time]'] = true;
		} else if (what == 'expiry') {
			url += '&ExpiryDate='+escape($(ids.dateField).value)+'&ExpiryTime='+escape($(ids.timeField).value);
			$('Form_EditForm').changeDetection_fieldsToIgnore['ExpiryDate[Date]'] = true;
			$('Form_EditForm').changeDetection_fieldsToIgnore['ExpiryDate[Time]'] = true;
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
					$(ids.dateTime).innerHTML = eval('data.message.'+what);
				} else { EmbargoExpiry.errorAlert(data); }
			},
			onFailure: function(t) { EmbargoExpiry.errorAlert(data); },
			onComplete: function(t) { $(el.id).className = 'action'; }
		});	
	},
	reset: function(what, el) {
		ids = EmbargoExpiry.ids(what);
		var url = 'admin/cms_setembargoexpiry?wfRequest='+$('WorkflowRequest_ID').value;
		
		$(ids.dateField).value = '';
		$(ids.timeField).value = '';

		EmbargoExpiry.fieldCheck();
		
		if (what == 'embargo') {
			url += '&ResetEmbargo';
			$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoDate[Date]'] = true;
			$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoDate[Time]'] = true;
		} else if (what == 'expiry') {
			url += '&ResetExpiry';
			$('Form_EditForm').changeDetection_fieldsToIgnore['ExpiryDate[Date]'] = true;
			$('Form_EditForm').changeDetection_fieldsToIgnore['ExpiryDate[Time]'] = true;
		}
		
		$(el.id).className = 'action loading';
		new Ajax.Request(url, {
			method: 'get',
			onSuccess: function(t) {
				if (data.status == 'success') {
					$(ids.wholeMessage).style.display = 'none';
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
					dateField: 'ExpiryDate_Date',
					timeField: 'ExpiryDate_Time',
					wholeMessage: 'embargoExpiry-expiryStatus',
					dateTime: 'expiryDate'
				};
			case 'embargo':
				return {
					resetButton: 'resetEmbargoButton',
					saveButton: 'saveEmbargoButton',
					dateField: 'EmbargoDate_Date',
					timeField: 'EmbargoDate_Time',
					wholeMessage: 'embargoExpiry-embargoStatus',
					dateTime: 'embargoDate'
				};
		}
	},
	embargoChange: function() {
		$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoDate[Date]'] = false;
		$('Form_EditForm').changeDetection_fieldsToIgnore['EmbargoDate[Time]'] = false;
		EmbargoExpiry.fieldCheck();
	},
	expiryChange: function() {
		$('Form_EditForm').changeDetection_fieldsToIgnore['ExpiryDate[Date]'] = false;
		$('Form_EditForm').changeDetection_fieldsToIgnore['ExpiryDate[Time]'] = false;
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
			} else {
				EmbargoExpiry.eButton(ids.saveButton);
				EmbargoExpiry.eButton(ids.resetButton);
			}
		}
	}
};

Behaviour.register({
	'#EmbargoDate_Time' : {
		initialize: EmbargoExpiry.fieldCheck,
		onchange: EmbargoExpiry.embargoChange
	},
	'#EmbargoDate_Date' : { onchange: EmbargoExpiry.embargoChange },
	'#ExpiryDate_Date' : { onchange: EmbargoExpiry.expiryChange },
	'#ExpiryDate_Time' : { onchange: EmbargoExpiry.expiryChange }
});


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
	$('Form_EditForm_action_publish').value = ss.i18n._t('CMSMAIN.PUBLISHING');
	$('Form_EditForm_action_publish').className = 'action loading';
	$('Form_EditForm').save(false, null, 'cms_publishwithcomment', true);
	$('Form_EditForm').removeChild(messageEl);
}

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
	/**
	 * Prompt for input from the user and then submit the given form via ajax.
	 */
	submitWithPromptedMessage : function(form, button, msgVar, msgPrompt) {
		var messageEl = CMSWorkflow.createPromptElement(msgVar, msgPrompt);
		form.appendChild(messageEl);

		Ajax.SubmitForm(form, button, {
			onSuccess: Ajax.Evaluator,
			onFailure: ajaxErrorHandler
		});
		
		// Once Ajax.SubmitForm has been calld, this element is no longer necessary		
		form.removeChild(messageEl);
	},
	
	createPromptElement: function(varName, promptText) {
		var message = prompt(promptText);
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
			Ajax.SubmitForm($('Form_EditForm'), this.name, {
				onSuccess: Ajax.Evaluator,
				onFailure: ajaxErrorHandler
			});
			return false;
		}
	}
}

Behaviour.register({
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
	CMSWorkflow.submitWithPromptedMessage(
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

// Replace these two actions with some alternatives

// function action_publish_right(e) {
// 	CMSWorkflow.submitWithPromptedMessage(
// 			$('Form_EditForm'), 'action_cms_publishwithcomment',
// 			'WorkflowComment',
// 			'Please comment on this publication, if applicable.'
// 	);
// }
function action_publish_right(e) {
	var messageEl = CMSWorkflow.createPromptElement('WorkflowComment', 'Please comment on this publication, if applicable.');
	$('Form_EditForm').appendChild(messageEl);
	$('Form_EditForm_action_publish').value = ss.i18n._t('CMSMAIN.PUBLISHING');
	$('Form_EditForm_action_publish').className = 'action loading';
	$('Form_EditForm').save(false, null, 'cms_publishwithcomment', true);
	$('Form_EditForm').removeChild(messageEl);
}
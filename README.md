# CMS Workflow Module

## Maintainer Contact
* Tom Rix (Nickname: trix)
  <tom (at) silverstripe (dot) com>
 * Sam Minnee (Nickname: sminnee)
   <sam (at) silverstripe (dot) com>
 * Ingo Schommer (Nickname: chillu)
   <ingo (at) silverstripe (dot) com>

## Requirements
 * SilverStripe 2.4 or newer


## Installation

You need to choose an 'approval path'. This details the actual process a request goes through before
it gets published to the live site.
 
There are two approval paths supplied: "Two Step" and "Three Step".

### Two Step

Author submits a request. Publisher approves it change is pushed live immediately.

This workflow is automatically set up for you and doesn't need any configuration.

### Three Step

Author submits a request. Approver approves it. Publisher publishes it at a later date.

Attach the following decorators in your `mysite/_config.php`:

	// remove two-step decorators
	Object::remove_extension('WorkflowRequest', 'WorkflowTwoStepRequest');
	Object::remove_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	Object::remove_extension('SiteConfig', 'SiteConfigTwoStepWorkflow');
	i// add three-step decorators
	Object::add_extension('WorkflowRequest', 'WorkflowThreeStepRequest');
	Object::add_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	Object::add_extension('LeftAndMain', 'LeftAndMainCMSThreeStepWorkflow');
	Object::add_extension('SiteConfig', 'SiteConfigThreeStepWorkflow');
	
Refresh your database schema through `http://<your-host>/dev/build`.

## Usage

Based on your permission levels, authors in the CMS will see different actions on a page,
and a new "Workflow" tab listing open requests.

## Popup alerts

You can allow Administrator users to Publish without giving a comment. 
by placing the following in your mysite/_config.php file:
This will disable the popup for this situation.

	LeftAndMainCMSWorkflow::set_prompt_admin_for_comments(false);

## Email alerts

Email alerts are configurable by the developer.

The following line sets a config option

    WorkflowRequest::set_alert(CLASS, EVENT, GROUP, NOTIFY);

CLASS is one of either WorkflowPublicationRequest or WorkflowDeletionRequest

EVENT is one of

 * request
 * publish
 * approve (3 step only)
 * deny
 * cancel
 * comment

GROUP is either author or publisher or approver

NOTIFY is either true or false



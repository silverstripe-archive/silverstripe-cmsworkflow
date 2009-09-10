# CMS Workflow Module

## Maintainer Contact
 * Sam Minnee (Nickname: sminnee)
   <sam (at) silverstripe (dot) com>
 * Tom Rix (Nickname: trix)
   <tom (at) silverstripe (dot) com>
 * Ingo Schommer (Nickname: chillu)
   <ingo (at) silverstripe (dot) com>

## Requirements
 * SilverStripe 2.3 or newer


## Installation

You need to choose an 'approval path'. This details the actual process a request goes through before
it gets published to the live site. You also need to delete the `_mainfest_exclude` file in the 
sidereports and batchactions directory of the path you choose.
Otherwise, you will get an error like 'Unknown class passed as parameter'. 
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
	// add three-step decorators
	Object::add_extension('WorkflowRequest', 'WorkflowThreeStepRequest');
	Object::add_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	Object::add_extension('LeftAndMain', 'LeftAndMainCMSThreeStepWorkflow');
	
Refresh your database schema through `http://<your-host>/dev/build`.

## Usage

Based on your permission levels, authors in the CMS will see different actions on a page,
and a new "Workflow" tab listing open requests.
See [documentation on doc.silverstripe.com][documentation] for screenshots and further usage details.

[documentation]: http://doc.silverstripe.com/doku.php?id=modules:cmsworkflow

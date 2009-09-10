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

## Usage

You need to choose an 'approval path'. This details
the actual process a request goes through before it
gets published to the live site. You also need to
delete the `_mainfest_exclude` file in the sidereports
and batchactions directory of the path you choose.
Otherwise, you will get an error like 'Unknown class
passed as parameter'. There are two approval paths supplied:

 * TwoStep: Author submits a request. Publisher approves it
   change is pushed live immediately.
 * ThreeStep: Author submits a request. Approver approves it.
   Publisher publishes it at a later date.

## Additional Documenation
http://doc.silverstripe.com/doku.php?id=modules:cmsworkflow

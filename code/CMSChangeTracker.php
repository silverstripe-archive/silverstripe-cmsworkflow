<?php

/**
 * This change tracker lets people access RSS feeds of the CMS.
 */
class CMSChangeTracker extends Controller {
	static $allowed_actions = array(
		'index',
	);
	
	function index($request) {
		// For 2.3 and 2.4 compatibility
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		BasicAuth::enable();
		BasicAuth::requireLogin("CMS RSS feed access.  Use your CMS login", "CMS_ACCESS_CMSMain");
		$member = $this->getBasicAuthMember();
		
		// Due to a bug in 2.3.0 we can't get the information that we need from $request
		$params = Director::urlParams();
		// Default value
		if(!isset($params['Data']) || !$params['Data']) $params['Data'] = 'all';
		
		switch($params['Data']) {
			case 'all':
				$changes = $this->changes();
				break;
			
			case 'page':
				if((int)$params['PageID']) {
					$changes = $this->changes("{$bt}SiteTree{$bt}.ID = " . (int)$params['PageID']);
				} else {
					return new HTTPResponse("<h1>Bad Page ID</h1><p>Bad page ID when getting RSS feed of changes to a page.</p>", 400);
				}
				break;

			default:
				user_error("CMSChangeTracker Data param value '$params[Data]' not implemented; this is probably due to a bad URL rule.", E_USER_ERROR);
		}
		
		$processedChanges = new DataObjectSet();
		foreach($changes as $change) {
			if($change->canEdit($member)) {
				$author = DataObject::get_by_id("Member", $change->AuthorID);
				$verbed = $change->Version == 1 ? "created" : "edited";
			
				if($author) {
					$changeTitle = "'$change->Title' $verbed by $author->FirstName $author->Surname";
					$changeAuthor = "$author->FirstName $author->Surname";
					$firstParagraph = "$author->FirstName $author->Surname (<a href=\"mailto:$author->Email\">$author->Email</a>) has $verbed the '$change->Title' page.";
				} else {
					$changeTitle = "'$change->Title' $verbed";
					$changeAuthor = "";
					$firstParagraph = "The '$change->Title' page has been $verbed.";
				}
				
				$actionLinks = "";

				$cmsLink = Director::absoluteURL("admin/show/$change->ID");
				$actionLinks .= "<li><a href=\"$cmsLink\">Edit in CMS</a></li>\n";
				
				$page = DataObject::get_by_id('SiteTree', $change->ID);
				if($page) {
					$link = $page->AbsoluteLink();
					$actionLinks .= "<li><a href=\"$link\">See the page on site</a></li>\n";
				}

				if($change->Version > 1) {
					$prevVersion = $change->Version - 1;
					$diffLink = Director::absoluteURL("admin/compareversions/$change->ID/?From={$prevVersion}&To={$change->Version}");
					$actionLinks .= "<li><a href=\"$diffLink\">See the changes in CMS</a></li>\n";
				}
			
				$changeDescription = <<<HTML
<p>$firstParagraph</p>

<h3>Actions and links</h3>

<ul>
	$actionLinks
</ul>
HTML;

				$processedChange = new CMSChangeTracker_Change(array(
					// We use ChangeTitle instead of Title to side-step a random bug in 2.3.0 that will be fixed in 2.3.1
					"ChangeTitle" => $changeTitle,
					"Author" => $changeAuthor,
					"Content" => $changeDescription,
					"Link" => $change->Link() . "version/$change->Version",
				));
				$processedChanges->push($processedChange);
			}
		}
		
		 $feed = new RSSFeed($processedChanges, Director::absoluteURL("admin/"), "SilverStripe Content Changes","", "ChangeTitle");
			
		return $feed->outputToBrowser();
	}

	/**
	 * Get a DataObjectSet of changes made to pages covered by the given filter
	 * @todo This should be part of the model layer.  Migrate this into the Versioned system in SilverStripe 2.4.
	 */
	function changes($SQL_filter = null, $limit = 10) {
		// For 2.3 and 2.4 compatibility
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		
		// Build the query by  replacing `SiteTree` with `SiteTree_versions` in a regular query.
		// Note that this should *really* be handled by a more full-featured data mapper; as it stands
		// this is a bit of a hack.
		$origStage = Versioned::current_stage();
		Versioned::reading_stage('Stage');
		$versionedQuery = singleton('SiteTree')->extendedSQL('');
		Versioned::reading_stage($origStage);

		if($SQL_filter) $versionedQuery->where[] = $SQL_filter;
		$versionedQuery->select[] = "{$bt}SiteTree{$bt}.RecordID AS ID";

		foreach($versionedQuery->from as $k => $v) {
			$versionedQuery->renameTable($k, $k . '_versions');
		}
		
		$versionedQuery->groupby = null;
		$versionedQuery->orderby = "{$bt}LastEdited{$bt} DESC, {$bt}SiteTree_versions{$bt}.{$bt}WasPublished{$bt}";
		$versionedQuery->limit = $limit;
		
		return singleton('Dataobject')->buildDataObjectSet($versionedQuery->execute());
	}

	/**
	 * Return the member currently logged in using basicuath
	 * @todo Move this into the core BasicAuth class
	 */
	function getBasicAuthMember() {
		if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$member = MemberAuthenticator::authenticate(array(
				'Email' => $_SERVER['PHP_AUTH_USER'], 
				'Password' => $_SERVER['PHP_AUTH_PW'],
			), null);
			return $member;
		}
	}
}

/**
 * Customisation of ArrayData that adds a Link method.  Needed for 2.3.0 support :-(.
 */
class CMSChangeTracker_Change extends ArrayData {
	function Link() {
		return $this->Link;
	}
}


?>
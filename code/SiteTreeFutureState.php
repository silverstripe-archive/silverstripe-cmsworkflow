<?php

/**
 * Extension that modifies SiteTree data requests to return future state contnet.
 */
class SiteTreeFutureState extends DataObjectDecorator {
	/**
	 * Set the future datetime to view
	 */
	static function set_future_datetime($datetime) {
		if($datetime) {
			Versioned::set_reading_mode('FutureState.' . $datetime);
		}
	}
	
	/**
	 * Return the currently viewed future datetime
	 */
	static function get_future_datetime() {
		$parts = explode('.', Versioned::get_reading_mode());
		if($parts[0] == 'FutureState') return $parts[1];
	}

	/**
	 * Choose the stage the site is currently on.
	 * If $_GET['futureDate'] is set, then it will use that future date, and store it in the
	 * session.
	 */
	static function choose_future_datetime() {
		$date = false;
		
		if(empty($_GET['stage']) && empty($_GET['archiveDate']) && isset($_GET['futureDate'])) {
			if($time = strtotime($_GET['futureDate'])) $date = date('Y-m-d H:i:s', $time);
		}
		
		if($date) {
			Session::set('readingMode', 'FutureState.' . $date);
			self::set_future_datetime($date);
			if(!Director::is_cli()) Cookie::set('bypassStaticCache', '1', 0);
		} else {
			if(Versioned::current_stage() == 'Live') {
				if(!Director::is_cli()) Cookie::set('bypassStaticCache', null, 0);
			}
		}
	}
	
	/**
	 * Choose the correct future datetime on model
	 */
	function modelascontrollerInit() {
		self::choose_future_datetime();
		
		if(Controller::curr() instanceof ModelAsController && self::get_future_datetime() && !Permission::check('CMS_ACCESS_CMSMain')) {
			$message = _t("ContentController.FUTURE_SITE_ACCESS_RESTRICTION", 'You must log in with your CMS password in order to view the future state of the site.  <a href="%s">Click here to go back to the published site.</a>');
			Security::permissionFailure(Controller::curr(), sprintf($message, "?stage=Live"));
		}
	}
	
	/**
	 * Amend the query to select from a future date if necessary.
	 */
	function augmentSQL(SQLQuery &$query) {
		if($datetime = self::get_future_datetime()) {
			foreach($query->from as $table => $dummy) {
				if(!isset($baseTable)) {
					$baseTable = $table;
				}
				$query->renameTable($table, $table . '_versions');
				$query->replaceText("\"$table\".\"ID\"", "\"$table\".\"RecordID\"");
				$query->replaceText("\"{$table}_versions\".\"ID\"", "\"{$table}_versions\".\"RecordID\"");
				
				if($table == $baseTable) {
					// Add all <basetable>_versions columns
					foreach(Versioned::$db_for_versions_table as $name => $type) {
						$query->select[] = sprintf('"%s_versions"."%s"', $baseTable, $name);
					}
					$query->select[] = sprintf('"%s_versions"."%s" AS "ID"', $baseTable, 'RecordID');
				}

				if($table != $baseTable) {
					$query->from[$table] .= " AND \"{$table}_versions\".\"Version\" = \"{$baseTable}_versions\".\"Version\"";
				}
			}
			
			// Link to the version archived on that date
			$tempTable = $this->requireFutureStateTempTable($baseTable, $datetime);
			$query->from[$tempTable] = "INNER JOIN \"$tempTable\"
				ON \"$tempTable\".\"ID\" = \"{$baseTable}_versions\".\"RecordID\" 
				AND \"$tempTable\".\"Version\" = \"{$baseTable}_versions\".\"Version\"";
		}
	}

	/**
	 * Keep track of the archive tables that have been created 
	 */
	private static $temp_tables = array();
	
	/**
	 * Called by {@link SapphireTest} when the database is reset.
	 * @todo Reduce the coupling between this and SapphireTest, somehow.
	 */
	public static function on_db_reset() {
		// Drop all temporary tables
		$db = DB::getConn();
		if($db->isActive()) {
			foreach(self::$temp_tables as $tableName) {
				if(method_exists($db, 'dropTable')) $db->dropTable($tableName);
				else $db->query("DROP TABLE \"$tableName\"");
			}
		}

		// Remove references to them
		self::$temp_tables = array();
	}

	/**
	 * Create a temporary table mapping each database record to its version on the given date.
	 * 
	 * This gives us an easy way of querying the future state at the date passed to this function:
	 * we simply join SiteTree_versions to the future table, inner joining on RecordID and Version.
	 * 
	 * @param string $baseTable The base table.
	 * @param string $date The date.
	 */
	protected static function requireFutureStateTempTable($baseTable, $date = null) {
		$tmpID = "_FutureState{$baseTable}_" . str_replace(array(' ','-',':'),'',$date);

		if(!isset(self::$temp_tables[$tmpID])) {
			self::$temp_tables[$tmpID] = DB::createTable($tmpID, array(
				"ID" => "INT NOT NULL",
				"Version" => "INT NOT NULL",
				"ExpiryDate" => DB::getConn()->SS_Datetime(array()),
			), null, array('temporary' => true));
		}
		
		if(!DB::query("SELECT COUNT(*) FROM \"" . self::$temp_tables[$tmpID] . "\"")->value()) {
			$SQL_date = Convert::raw2sql($date);
			$tempTable = self::$temp_tables[$tmpID];
			
			// Insert current live data
			DB::query("INSERT INTO \"$tempTable\"
				SELECT \"ID\", \"Version\", \"ExpiryDate\" FROM \"{$baseTable}_Live\"");

			// Remove pages that will be included by the embargo line below, so that we can update
			// without duplication
			DB::query("DELETE FROM \"$tempTable\" WHERE \"ID\" IN
				(SELECT \"PageID\" FROM \"WorkflowRequest\" 
				WHERE \"Status\" = 'Scheduled' AND \"EmbargoDate\" <= '$SQL_date')");

			// Add/update embargoed pages
			DB::query("INSERT INTO \"$tempTable\"
				SELECT \"WorkflowRequest\".\"PageID\", \"$baseTable\".\"Version\", \"$baseTable\".\"ExpiryDate\"
				FROM \"WorkflowRequest\" 
				INNER JOIN \"$baseTable\" ON \"$baseTable\".\"ID\" = \"WorkflowRequest\".\"PageID\"
				WHERE \"WorkflowRequest\" .\"Status\" = 'Scheduled' AND \"WorkflowRequest\" .\"EmbargoDate\" <= '$SQL_date'");
				
			// Remove expired pages
			DB::query("DELETE FROM \"$tempTable\" WHERE \"ExpiryDate\" IS NOT NULL AND \"ExpiryDate\" <= '$SQL_date'");

			// Remove expired subsite pages
			DB::query("DELETE FROM \"$tempTable\" WHERE \"ExpiryDate\" IS NOT NULL AND \"ExpiryDate\" <= '$SQL_date'");

			// Add/update embargoed Virtual pages - if the VP already exists on the live site
			if($baseTable == 'SiteTree') {
				// Remove existing items to prevent duplication
				DB::query("DELETE FROM \"$tempTable\" WHERE \"ID\" IN (
					SELECT \"VirtualPage\".\"ID\"
					FROM \"WorkflowRequest\" 
					INNER JOIN \"VirtualPage\" ON \"VirtualPage\".\"CopyContentFromID\" = \"WorkflowRequest\".\"PageID\"
					INNER JOIN \"{$baseTable}_Live\" ON \"VirtualPage\".\"ID\" = \"{$baseTable}_Live\".\"ID\"
					WHERE \"WorkflowRequest\" .\"Status\" = 'Scheduled' AND \"WorkflowRequest\" .\"EmbargoDate\" <= '$SQL_date')");
					
				// Then insert new ones
				DB::query("INSERT INTO \"$tempTable\"
					SELECT \"VirtualPage\".\"ID\", \"$baseTable\".\"Version\", \"$baseTable\".\"ExpiryDate\"
					FROM \"WorkflowRequest\" 
					INNER JOIN \"VirtualPage\" ON \"VirtualPage\".\"CopyContentFromID\" = \"WorkflowRequest\".\"PageID\"
					INNER JOIN \"$baseTable\" ON \"$baseTable\".\"ID\" = \"VirtualPage\".\"ID\"
					INNER JOIN \"{$baseTable}_Live\" ON \"$baseTable\".\"ID\" = \"{$baseTable}_Live\".\"ID\"
					WHERE \"WorkflowRequest\" .\"Status\" = 'Scheduled' AND \"WorkflowRequest\" .\"EmbargoDate\" <= '$SQL_date'");
			}
				
		}
		
		return self::$temp_tables[$tmpID];
	}

	/**
	 * Return a piece of text to keep DataObject cache keys appropriately specific
	 */
	function cacheKeyComponent() {
		if(self::get_future_datetime()) {
			return 'future-'.str_replace(array(' ',':','-'),'',self::get_future_datetime());
		}
	}

}

class SiteTreeFutureState_SilverStripeNavigatorItem extends SilverStripeNavigatorItem {
	static $priority = 50;
	
	function getHTML($page) {
		Requirements::css('cmsworkflow/css/FutureStateNavigatorItem.css');
		Requirements::javascript(THIRDPARTY_DIR .'/jquery/jquery-packed.js');
		Requirements::javascript(THIRDPARTY_DIR .'/jquery-livequery/jquery.livequery.js');
		
		Requirements::javascript('cmsworkflow/javascript/futurestate.js');
		
		$datetimeField = new DatetimeField('FutureStateDate', 'Date');
		$datetimeField->getDateField()->setConfig('showcalendar', true);
		$datetimeField->getTimeField()->setConfig('showdropdown', true);
		
		$datetime = SiteTreeFutureState::get_future_datetime();
		if($datetime) {
			$datetimeField->setValue($datetime);
		}
		
		$data = new ArrayData(array(
			'Page' => $page,
			'DateTimeField' => $datetimeField,
			'Current' => (bool)$datetime,
		));
		
		return $data->renderWith(array('FutureStateNavigatorItem'));
	}
	
	function getMessage($page) {
		if($date = SiteTreeFutureState::get_future_datetime()) {
			$dateObj = Object::create('Datetime');
			$dateObj->setValue($date);
			
			return "<div id=\"SilverStripeNavigatorMessage\" title=\"". _t('ContentControl.NOTEWONTBESHOWN', 'Note: this message will not be shown to your visitors') ."\">". "Viewing site in future at <br>" . $dateObj->Nice() . "</div>";
		}
	}
	
	function getLink($page) {
		if($date = SiteTreeFutureState::get_future_datetime()) {
			return $page->AbsoluteLink() . '?futureDate=' . $date;
		}
	}	
}
<?php

/**
 * Extension that modifies SiteTree data requests to return future state contnet.
 */
class SiteTreeFutureState extends DataObjectDecorator {
	protected static $future_datetime = null;
	
	/**
	 * Set the future datetime to view
	 */
	static function set_future_datetime($datetime) {
		if($datetime) {
			Versioned::reset();
			Session::set('currentStage', "Stage");
			Versioned::reading_stage("Stage");
		}
		
		self::$future_datetime = $datetime;
	}
	
	/**
	 * Return the currently viewed future datetime
	 */
	static function get_future_datetime() {
		return self::$future_datetime;
	}

	/**
	 * Choose the stage the site is currently on.
	 * If $_GET['futureDate'] is set, then it will use that future date, and store it in the
	 * session.
	 */
	static function choose_future_datetime() {
		if(!empty($_GET['stage']) || !empty($_GET['archiveDate'])) {
			Session::set('futureDate', null);

		} else if(isset($_GET['futureDate'])) {
			if($time = strtotime($_GET['futureDate'])) $date = date('Y-m-d h:i:s', $time);
			else $date = null;
			
			Session::set('futureDate', $date);
		}
		
		if(Session::get('futureDate')) {
			self::set_future_datetime(Session::get('futureDate'));
			Cookie::set('bypassStaticCache', '1', 0);
		} else {
			self::set_future_datetime(null);
			if(Versioned::current_stage() == 'Live') {
				Cookie::set('bypassStaticCache', null, 0);
			}
		}
	}
	
	/**
	 * Choose the correct future datetime on model
	 */
	function modelascontrollerInit() {
		self::choose_future_datetime();
	}
	
	/**
	 * Amend the query to select from a future date if necessary.
	 */
	function augmentSQL(SQLQuery &$query) {
		if($datetime = self::$future_datetime) {
			foreach($query->from as $table => $dummy) {
				if(!isset($baseTable)) {
					$baseTable = $table;
				}
				$query->renameTable($table, $table . '_versions');
				$query->replaceText("\"$table\".\"ID\"", "\"$table\".\"RecordID\"");
				
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
		foreach(self::$temp_tables as $tableName) {
			if(method_exists($db, 'dropTable')) $db->dropTable($tableName);
			else $db->query("DROP TABLE \"$tableName\"");
		}

		// Remove references to them
		self::$$temp_tables = array();
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
		if(!isset(self::$temp_tables[$baseTable])) {
			self::$temp_tables[$baseTable] = DB::createTable("_FutureState$baseTable", array(
				"ID" => "INT NOT NULL",
				"Version" => "INT NOT NULL",
			), null, array('temporary' => true));
		}
		
		if(!DB::query("SELECT COUNT(*) FROM \"" . self::$temp_tables[$baseTable] . "\"")->value()) {
			$SQL_date = Convert::raw2sql($date);
			$tempTable = self::$temp_tables[$baseTable];
			
			// Insert current live data
			DB::query("INSERT INTO \"$tempTable\"
				SELECT \"ID\", Version FROM \"{$baseTable}_Live\"");

			// Remove expired pages
			DB::query("DELETE FROM \"$tempTable\" WHERE \"ID\" IN
				(SELECT \"ID\" FROM \"{$baseTable}_Live\" 
				WHERE \"ExpiryDate\" IS NOT NULL AND \"ExpiryDate\" <= '$SQL_date')");
				
			// Remove pages that will be included by the embargo line below, so that we can update
			// without duplication
			DB::query("DELETE FROM \"$tempTable\" WHERE \"ID\" IN
				(SELECT \"PageID\" FROM \"WorkflowRequest\" 
				WHERE \"Status\" = 'Scheduled' AND \"EmbargoDate\" <= '$SQL_date')");

			// Add/update embargoed pages
			DB::query("INSERT INTO \"$tempTable\"
				SELECT \"WorkflowRequest\".\"PageID\", \"$baseTable\".\"Version\"
				FROM \"WorkflowRequest\" 
				INNER JOIN \"$baseTable\" ON \"$baseTable\".\"ID\" = \"WorkflowRequest\".\"PageID\"
				WHERE \"WorkflowRequest\" .\"Status\" = 'Scheduled' AND \"WorkflowRequest\" .\"EmbargoDate\" <= '$SQL_date'");
		}
		
		return self::$temp_tables[$baseTable];
	}
}

class SiteTreeFutureState_SilverStripeNavigatorItem extends SilverStripeNavigatorItem {
	static $priority = 50;
	
	function getHTML($page) {
		Requirements::css('cmsworkflow/css/FutureStateNavigatorItem.css');
		Requirements::javascript('jsparty/jquery/jquery.js');
		Requirements::javascript('jsparty/jquery/plugins/livequery/jquery.livequery.js');
		Requirements::javascript('cmsworkflow/javascript/futurestate.js');
		
		$datetimeField = new PopupDateTimeField('FutureStateDate', 'Date');
		$current = (bool) SiteTreeFutureState::get_future_datetime();
		$data = new ArrayData(array(
			'Page' => $page,
			'DateTimeField' => $datetimeField,
			'Current' => $current
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

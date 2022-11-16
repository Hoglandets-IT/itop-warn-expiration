<?php

class TriggerOnDatePass extends TriggerOnObject
{
	/**
	 * @throws \CoreException
	 * @throws \Exception
	 */
	public static function Init()
	{
		$aParams = array
		(
			"category" => "grant_by_profile,core/cmdb",
			"key_type" => "autoincrement",
			"name_attcode" => "description",
			"state_attcode" => "",
			"reconc_keys" => array('description'),
			"db_table" => "priv_trigger_ondatepass",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(
			new AttributeInteger(
				"days_until", 
				array(
					"allowed_values" => null, 
					"sql" => "threshold_index", 
					"default_value" => 1, 
					"is_null_allowed" => false, 
					"depends_on" => array('target_class')
				)
			)
		);

		MetaModel::Init_AddAttribute(
			new AttributeClassAttCodeSet(
				'watch_attribute', 
				array(
					"allowed_values" => null, 
					"class_field" => "target_class", 
					"sql" => "stop_watch_code", 
					"default_value" => null, 
					"is_null_allowed" => false, 
					"max_items" => 1, 
					"min_items" => 1, 
					"attribute_definition_exclusion_list" => null, 
					"attribute_definition_list" => "AttributeDate", 
					"include_child_classes_attributes" => true, 
					"depends_on" => array('target_class')
				)
			)
		);

		MetaModel::Init_SetZListItems('details', array('description', 'context', 'target_class', 'filter', 'watch_attribute', 'days_until', 'action_list')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('finalclass', 'target_class', 'watch_attribute', 'days_until')); // Attributes to be displayed for a list
		MetaModel::Init_SetZListItems('standard_search', array('description', 'target_class', 'watch_attribute', 'days_until')); // Criteria of the std search form
	}

	/**
	 * Check whether the given object is in the scope of this trigger
	 * and can potentially be the subject of notifications
	 *
	 * @param DBObject $oObject The object to check
	 *
	 * @return bool
	 * @throws \CoreException
	 */
	public function IsInScope(DBObject $oObject)
	{
		return parent::IsInScope($oObject);
	}

	/**
	 * Check if the trigger can be used in the current context
	 *
	 * @return bool true if context OK
	 * @throws \ArchivedObjectException
	 * @throws \CoreException
	 */
	public function IsContextValid()
	{
		return true;
	}

	// /**
	//  * @param $aContextArgs
	//  *
	//  * @throws \ArchivedObjectException
	//  * @throws \CoreException
	//  */
	// public function DoActivate($aContextArgs)
	// {
	// 	// $bGo = true;
	// 	// if (isset($aContextArgs['this->object()']))
	// 	// {
	// 	// 	/** @var \DBObject $oObject */
	// 	// 	$oObject = $aContextArgs['this->object()'];
	// 	// 	$bGo = $this->IsTargetObject($oObject->GetKey(), $oObject->ListPreviousValuesForUpdatedAttributes());
	// 	// }
	// 	// if ($bGo)
	// 	// {
	// 	// 	parent::DoActivate($aContextArgs);
	// 	// }
	// 	parent::DoActivate($aContextArgs);
	// }
}

class CheckDatepassThreshold implements iBackgroundProcess
{
	public function GetPeriodicity()
	{	
		return 10; // seconds
	}

	public function GetTrigger(int $id) : Trigger {
		$obj = MetaModel::GetObject('TriggerOnDatePass', $id, false);
		return $obj;
	}

	public function GetCertificate(int $id) : Certificate {
		return MetaModel::GetObject('Certificate', $id, false);
	}

	public function HasBeenNotified(TriggerOnDatePass $oTrigger, Certificate $oObject) : bool {
		$watchAttr = strval($oTrigger->Get('watch_attribute'));
		$oExpDate = $oObject->Get($watchAttr);
		
		$actualNotifyDate = new DateTime($oExpDate . ' 00:00:00');
		echo "Notification date is ";
		print_r($actualNotifyDate);
		$subtractDays = new DateInterval('P'.$oTrigger->Get('days_until').'D');
		$actualNotifyDate->sub($subtractDays);
		echo "Notification date is";
		print_r($actualNotifyDate);

		$actualNotifyDate = $actualNotifyDate->format('Y-m-d H:i:s');
		echo "Actual notification date is $actualNotifyDate";

		// Check if object has been notified after the date		echo <<<EOF
		$cOps = DBObjectSearch::FromOQL(<<<EOF
		SELECT CMDBChangeOpPlugin
		WHERE objclass = '{$oTrigger->Get('target_class')}'
		AND objkey = '{$oObject->GetKey()}'
		AND date >= '{$actualNotifyDate}'
		AND date <= '{$oExpDate} 23:59:59'
		AND description = 'Trigger {$oTrigger->GetKey()} sent notification'
EOF);

		print_r($cOps);
		$cpr = new DBObjectSet($cOps);

		return $cpr->Count() > 0;

		// print_r($cpr->Count());
		// die();
// EOF;
		// print_r($cOps);

		// $oRes = new DBObjectSet($cOps);
		// echo "DBOSet fetched";
		// if ($oRes->Count() > 0)
		// {
		// 	return true;
		// }
		// return false;
		// var_dump($oRes);

		// 
		// $nowDate = new DateTime();
		// $nowDateStr = $nowDate->format('Y-m-d H:i:s');

		// $expDate = DateTime::createFromFormat('Y-m-d 00:00:00', $watchAttr);
		// $expDate->modify('-'.$oTrigger->Get('days_until').' days');
		// $daysuntilBeforeExpdate = $expDate->format('Y-m-d H:i:s');


		// // $expWarningDate = $nowDate->add(new DateInterval('P'.$oTrigger->Get('days_until').'D'));
		

		// // var_dump($oTrigger->Get('target_class'));
		// // var_dump($oObject->Get('friendlyname'));
		// // var_dump($oObject->ListActions());

		// // Search for CMDB Change ops
		// $oSearch = DBObjectSearch::FromOQL("SELECT CMDBChangeOp WHERE objclass = {$oTrigger->Get('target_class')} AND objkey = {$oObject->GetKey()} AND date > {$daysuntilBeforeExpdate} ORDER BY date DESC LIMIT 1");
		// $oSet = new DBObjectSet($oSearch);
		// var_dump($oSet);
		// Find notification objects after objdate and before or equal to now
		// $sOQL = <<<EOF
		// 	SELECT EventNotification
		// 	WHERE object_class = '{$oTrigger->Get('target_class')}'
		// 	AND object_id = '{$oObject->GetKey()}'
		// 	AND date >= '{$oObject->Get($watchAttr)} 00:00:00'
		// 	AND date <= '{$nowDateStr}'
		// 	AND date 
		// EOF;

		// $expiryDateStr = $oObject->Get($watchAttr);
		
		// $expiryDate = DateTime::CreateFromFormat('Y-m-d H:i:s', "$expiryDateStr 00:00:00");

		// var_dump($oObject->Get($watchAttr));
		// print_r($expiryDate);
		// echo PHP_EOL;
		// echo "DONE".PHP_EOL;

		// $watchAttr = $this->Get('watch_attribute')->GetValues()[0];
		// print_r($watchAttr);
		// // $getTimeout = $this->Get('days_until');
		// $expiryDate = $oObject->Get($this->Get('watch_attribute'));
		// print_r($expiryDate);
		// die();
		// $search_time = new DateTime();
		// $search_time->add(new DateInterval('P'.$this->Get('days_until').'D'));
		// $search_time = $search_time->format('Y-m-d H:i:s');

		// Search for all objects matching expiration criteria
		// $oObjectSet = new DBObjectSet(
		// 	DBObjectSearch::FromOQL(
		// 		"SELECT {$oTrigger->Get('target_class')} WHERE {$oTrigger->Get('watch_attribute')} <= '{$search_time}'"
		// 	)
		// );
		// Check if there is another notification for this object between now and the exact expiry datetime
		// Check if last updated is more recent than last notification date

// 		$OQL = <<<EOF
// 			SELECT EventNotification
// 			WHERE object_class = '{$this->Get('target_class')}'
// 			AND object_id = '{$oObject->GetKey()}'
// 			AND date > 
// EOF;


		// $oEventSet = new DBObjectSet(
		// 		DBObjectSearch::FromOQL(
		// 			"SELECT EventNotification WHERE obj_class = '{$oTrigger->Get('target_class')}' AND obj_key = {$oObject->GetKey()} AND message LIKE '%{$oTrigger->Get('days_until')} dagar%'"
		// 		)
		// 	);
		return true;
	}

	function Process($iTimeLimit)
	{
		// Get all TriggerOnDatePass objects
		$oTriggerSet = new DBObjectSet(
			DBObjectSearch::FromOQL('SELECT TriggerOnDatePass')
		);

		// Loop over objects to handle TriggerOnDatePass
		while ($oTrigger = $oTriggerSet->Fetch())
		{
			if (!$oTrigger->IsContextValid())
			{
				echo "Invalid Context";
				continue;
			}

			// Get attribute for object search
			$search_time = new DateTime();
			$now_time = $search_time->format('Y-m-d H:i:s');
			$search_time->add(new DateInterval('P'.$oTrigger->Get('days_until').'D'));
			$search_time = $search_time->format('Y-m-d H:i:s');

			echo "SELECT {$oTrigger->Get('target_class')} WHERE {$oTrigger->Get('watch_attribute')} <= '{$search_time}' AND {$oTrigger->Get('watch_attribute')} > '{$now_time}' AND status = 'active'";

			// Search for all objects matching expiration criteria
			$oObjectSet = new DBObjectSet(
				DBObjectSearch::FromOQL(
					"SELECT {$oTrigger->Get('target_class')} WHERE {$oTrigger->Get('watch_attribute')} <= '{$search_time}' AND {$oTrigger->Get('watch_attribute')} > '{$now_time}' AND status = 'active'"
					// "SELECT {$oTrigger->Get('target_class')} WHERE {$oTrigger->Get('watch_attribute')} <= '{$search_time}' AND status = 'active'"
				)
			);

			echo "X";
			echo $oObjectSet->Count();
			die();


			// For each object
			while ($oObject = $oObjectSet->Fetch())
			{
				if (!$oTrigger->IsInScope($oObject))
				{
					echo "Not in scope";
					continue;
				}
				
				if ($this->HasBeenNotified($oTrigger, $oObject))
				{
					echo "Already notified";
					die();
					continue;
				}

				$oTrigger->DoActivate($oObject->ToArgs('this'));

				$chop = new CMDBChangeOpPlugin();
				$chop->Set('objclass', $oTrigger->Get('target_class'));
				$chop->Set('objkey', $oObject->GetKey());
				$chop->Set('description', "Trigger {$oTrigger->GetKey()} sent notification");
				$chop->DBInsert();	
				

				// // Check if object is in scope
				// if ($oTrigger->IsInScope($oObject))
				// {
				// 	// Check if object is in context
				// 	if ($oTrigger->IsContextValid())
				// 	{
				// 		// Check if object is already in the list of objects to be notified
				// 		$oTrigger->DoActivate($oObject->ToArgsForQuery('this'));
				// 	}
				// }



				// Check if event log contains previous notification event for this period
				// $oEventSet = new DBObjectSet(
				// 	DBObjectSearch::FromOQL(
				// 		"SELECT EventNotification"
				// 		"SELECT EventNotification WHERE obj_class = '{$oTrigger->Get('target_class')}' AND obj_key = {$oObject->GetKey()} AND message LIKE '%{$oTrigger->Get('days_until')} dagar%'"
				// 	)
				// );
				// Get the Event log and check if the object has been notified recently (Last N days where N = threshold) with this trigger ID. If yes:
					// Check if object is modified since last notification. If yes:
						// Notify
					// Else
						// Do nothing
				// Else
						// Get actions connected to TriggerOnDatePass item
						// Call the actions
			}
			
		}

		// $this->sendLog();


		

		// // Get all action triggers of type TriggerOnDatePass
		// $oTriggerSet = new DBObjectSet(DBObjectSearch::FromOQL("SELECT TriggerOnDatePass"));

		// while ($tmp = $oTriggerSet->Fetch())
		// {
		// 	$days = $tmp->Get('days_until');
		// 	$dst_class = $tmp->Get('target_class');

		// 	$now = new DateTime();
		// 	$now->add(new DateInterval('P'.$days.'D'));
						
		// 	$timestr = $now->format('Y-m-d H:i:s');
		// 	$print['Trig'][] = $dst_class . ' ' . $timestr;		
			
		// 	// Get objects with matching expiration date
		// 	$oTargetSearch = DBObjectSearch::FromOQL("SELECT {$tmp->Get('target_class')} WHERE {$tmp->Get('watch_attribute')} <= '$timestr'");
		// 	$oTargets = new DBObjectSet($oTargetSearch);

		// 	while ($oTarget = $oTargets->Fetch())
		// 	{
		// 		// Execute Trigger
		// 		$print['Trig'][] = $oTarget;
		// 	}
		// 	// Get target object
		// 	// $oTargetObj = MetaModel::GetObject($tmp['target_class'], $tmp['object_id'], false, true);

		// }

	

		// $aList = array();
		// foreach (MetaModel::GetClasses() as $sClass)
		// {
		// 	foreach (MetaModel::ListAttributeDefs($sClass) as $sAttCode => $oAttDef)
		// 	{
		// 		if ($oAttDef instanceof AttributeStopWatch)
		// 		{
		// 			foreach ($oAttDef->ListThresholds() as $iThreshold => $aThresholdData)
		// 			{
		// 				$iPercent = $aThresholdData['percent']; // could be different than the index !
		
		// 				$sNow = date(AttributeDateTime::GetSQLFormat());
		// 				$sExpression = "SELECT $sClass WHERE {$sAttCode}_laststart AND {$sAttCode}_{$iThreshold}_triggered = 0 AND {$sAttCode}_{$iThreshold}_deadline < :now";
		// 				$oFilter = DBObjectSearch::FromOQL($sExpression);
		// 				$oSet = new DBObjectSet($oFilter, array(), array('now' => $sNow));
		// 				$oSet->OptimizeColumnLoad(array($sClass => array($sAttCode)));
		// 				while ((time() < $iTimeLimit) && ($oObj = $oSet->Fetch()))
		// 				{
		// 					$sClass = get_class($oObj);

		// 					$aList[] = $sClass.'::'.$oObj->GetKey().' '.$sAttCode.' '.$iThreshold;

		// 					// Execute planned actions
		// 					//
		// 					foreach ($aThresholdData['actions'] as $aActionData)
		// 					{
		// 						$sVerb = $aActionData['verb'];
		// 						$aParams = $aActionData['params'];
		// 						$aValues = array();
		// 						foreach($aParams as $def)
		// 						{
		// 							if (is_string($def))
		// 							{
		// 								// Old method (pre-2.1.0) non typed parameters
		// 								$aValues[] = $def;
		// 							}
		// 							else // if(is_array($def))
		// 							{
		// 								$sParamType = array_key_exists('type', $def) ? $def['type'] : 'string';
		// 								switch($sParamType)
		// 								{
		// 									case 'int':
		// 										$value = (int)$def['value'];
		// 										break;
										
		// 									case 'float':
		// 										$value = (float)$def['value'];
		// 										break;
										
		// 									case 'bool':
		// 										$value = (bool)$def['value'];
		// 										break;
										
		// 									case 'reference':
		// 										$value = ${$def['value']};
		// 										break;
										
		// 									case 'string':
		// 									default:
		// 										$value = (string)$def['value'];
		// 								}
		// 								$aValues[] = $value;
		// 							}
		// 						}
		// 						$aCallSpec = array($oObj, $sVerb);
		// 						call_user_func_array($aCallSpec, $aValues);
		// 					}

		// 					// Mark the threshold as "triggered"
		// 					//
		// 					$oSW = $oObj->Get($sAttCode);
		// 					$oSW->MarkThresholdAsTriggered($iThreshold);
		// 					$oObj->Set($sAttCode, $oSW);
		
		// 					if($oObj->IsModified())
		// 					{
		// 						CMDBObject::SetTrackInfo("Automatic - threshold triggered");

		// 						$oObj->DBUpdate();
		// 					}

		// 					// Activate any existing trigger
		// 					// 
		// 					$sClassList = implode("', '", MetaModel::EnumParentClasses($sClass, ENUM_PARENT_CLASSES_ALL));

		// 					$oTriggerSet = new DBObjectSet(
		// 						DBObjectSearch::FromOQL("SELECT TriggerOnThresholdReached AS t WHERE t.target_class IN ('$sClassList') AND stop_watch_code MATCHES :stop_watch_code AND threshold_index = :threshold_index"),
		// 						array(), // order by
		// 						array('stop_watch_code' => $sAttCode, 'threshold_index' => $iThreshold)
		// 					);
		// 					while ($oTrigger = $oTriggerSet->Fetch())
		// 					{
		// 						try
		// 						{
		// 							$oTrigger->DoActivate($oObj->ToArgs('this'));
		// 						}
		// 						catch(Exception $e)
		// 						{
		// 							utils::EnrichRaisedException($oTrigger, $e);
		// 						}
		// 					}
		// 				}
		// 			}
		// 		}
		// 	}
		}

		// $iProcessed = count($aList);
		// return "Triggered $iProcessed threshold(s):".implode(", ", $aList);
}

// class NotificationPoint implements iRestServiceProvider
// {
// 	public function ListOperations($sVersion)
// 	{
// 		$aOps = array();
// 		if (in_array($sVersion, array('1.0', '1.1', '1.2', '1.3')))
// 		{
// 			$aOps[] = array(
// 				'verb' => 'ext/notification',
// 				'description' => 'Cron job target for generating expiry notifications'
// 			);
// 		}
// 		return $aOps;
// 	}
		
// 	public function ExecOperation($sVersion, $sVerb, $aParams)
// 	{
// 		$oResult = new RestResultWithObjects();
// 		switch ($sVerb)
// 		{
// 		case 'ext/get_related':
// 			//DoSomething
//             break;
		
// 		}
// 		return $oResult;
// 	}
// }
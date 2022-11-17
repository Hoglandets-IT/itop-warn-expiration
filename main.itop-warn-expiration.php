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
					"default_value" => 30,
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
}

class CheckDatepassThreshold implements iBackgroundProcess
{
	public function GetPeriodicity()
	{
		// TODO: Change back
		return 3600*4; // seconds
		// return 10;
	}

	/**
	 * Checks whether the object has already been notified in this period
	 * 
	 * @param $oTrigger The trigger to check for
	 * @param $oObject The object to check
	 *
	 * @return bool
	 * @throws \CoreException
	 * @throws \Exception
	 */
	public function HasBeenNotified(TriggerOnDatePass $oTrigger, Certificate $oObject) : bool {
		$watchAttr = strval($oTrigger->Get('watch_attribute'));
		$oExpDate = $oObject->Get($watchAttr);
		
		$actualNotifyDate = new DateTime($oExpDate . ' 00:00:00');

		$subtractDays = new DateInterval('P'.$oTrigger->Get('days_until').'D');
		$actualNotifyDate->sub($subtractDays);

		$actualNotifyDate = $actualNotifyDate->format('Y-m-d H:i:s');

		// Check if object has been notified after the date in watch_attribute - days_until
		// And before the date in watch_attribute
		$cOps = DBObjectSearch::FromOQL(<<<EOF
				SELECT CMDBChangeOpPlugin
				WHERE objclass = '{$oTrigger->Get('target_class')}'
				AND objkey = '{$oObject->GetKey()}'
				AND date >= '{$actualNotifyDate}'
				AND date <= '{$oExpDate} 23:59:59'
				AND description = 'Trigger {$oTrigger->GetKey()} sent notification'
		EOF);

		$cpr = new DBObjectSet($cOps);
		return $cpr->Count() > 0;

		return true;
	}

	function Process($iTimeLimit)
	{
		// echo "Start processing TriggerOnDatePass";
		// Get all TriggerOnDatePass objects
		$oTriggerSet = new DBObjectSet(
			DBObjectSearch::FromOQL('SELECT TriggerOnDatePass')
		);

		// Loop over objects to handle TriggerOnDatePass
		while ($oTrigger = $oTriggerSet->Fetch())
		{
			// echo "Start loop";
			// Check for timeout
			if (time() > $iTimeLimit)
			{
				return;
			}

			// Get attributes for object search
			$search_time = new DateTime();
			$now_time = $search_time->format('Y-m-d H:i:s');

			$search_time->add(new DateInterval('P'.$oTrigger->Get('days_until').'D'));
			$search_time = $search_time->format('Y-m-d H:i:s');

			// Search for all objects matching expiration criteria
			$oObjectSet = new DBObjectSet(
				DBObjectSearch::FromOQL(
					"SELECT {$oTrigger->Get('target_class')} WHERE {$oTrigger->Get('watch_attribute')} <= '{$search_time}' AND {$oTrigger->Get('watch_attribute')} > '{$now_time}' AND status = 'active'"
				)
			);


			// For each object
			while ($oObject = $oObjectSet->Fetch())
			{
				// echo "Start inner loop";
				if ($this->HasBeenNotified($oTrigger, $oObject))
				{
					// echo "Already notified";
					continue;
				}
				// echo "Notifying";
				$oTrigger->DoActivate($oObject->ToArgs('this'));

				$chop = new CMDBChangeOpPlugin();
				$chop->Set('objclass', $oTrigger->Get('target_class'));
				$chop->Set('objkey', $oObject->GetKey());
				$chop->Set('description', "Trigger {$oTrigger->GetKey()} sent notification");
				$chop->DBInsert();
				// echo "Notified";
				// die();
			}
		}
	}
}

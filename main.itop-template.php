<?php

class iTopTemplateUIExtension extends AbstractApplicationUIExtension
{
	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
	}

    public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{
	}

	public function OnFormSubmit($oObject, $sFormPrefix = '')
	{
	}

	public function OnFormCancel($sTempId)
	{
	}

	public function EnumUsedAttributes($oObject)
	{
		return array();
	}

	public function GetIcon($oObject)
	{
		return '';
	}

	public function GetHilightClass($oObject)
	{
		return HILIGHT_CLASS_NONE;
	}

	public function EnumAllowedActions(DBObjectSet $oSet)
	{
		return array();
	}
}

class CustomRestEndpoint implements iRestServiceProvider
{
	public function ListOperations($sVersion)
	{
		$aOps = array();
		if (in_array($sVersion, array('1.0', '1.1', '1.2', '1.3')))
		{
			$aOps[] = array(
				'verb' => 'ext/some-ext',
				'description' => 'some-description'
			);
		}
		return $aOps;
	}
		
	public function ExecOperation($sVersion, $sVerb, $aParams)
	{
		$oResult = new RestResultWithObjects();
		switch ($sVerb)
		{
		case 'ext/get_related':
			//DoSomething
            break;
		return $oResult;
	}
}
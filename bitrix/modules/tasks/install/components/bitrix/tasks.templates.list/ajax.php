<?php
define('STOP_STATISTICS',    true);
define('NO_AGENT_CHECK',     true);
define('DisableEventsCheck', true);

define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CUtil::JSPostUnescape();

CModule::IncludeModule('tasks');

__IncludeLang(dirname(__FILE__).'/lang/'.LANGUAGE_ID.'/'.basename(__FILE__));

$SITE_ID = isset($_GET["SITE_ID"]) ? $_GET["SITE_ID"] : SITE_ID;

if (check_bitrix_sessid())
{
	if (intval($_POST["id"]) > 0)
	{
		$rsTemplate = CTaskTemplates::GetByID(intval($_POST["id"]));
		if ($arTemplate = $rsTemplate->Fetch())
		{
			if ($_POST["mode"] == "delete")
			{
				if ($arTemplate["CREATED_BY"] == $USER->GetID())
				{
					$template = new CTaskTemplates();
					$template->Delete(intval($_POST["id"]));
				}
			}
		}
	}
}

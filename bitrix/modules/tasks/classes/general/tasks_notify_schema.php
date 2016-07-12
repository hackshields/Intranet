<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 */


IncludeModuleLangFile(__FILE__);

class CTasksNotifySchema
{
	public function __construct()
	{
	}

	public static function OnGetNotifySchema()
	{
		return array(
			"tasks" => array(
				"comment" => Array(
					"NAME" => GetMessage('TASKS_NS_COMMENT'),
					"MAIL" => true,
					"XMPP" => true,
				),
				"reminder" => Array(
					"NAME" => GetMessage('TASKS_NS_REMINDER'),
					"MAIL" => true,
					"XMPP" => true,
				),
				"manage" => Array(
					"NAME" => GetMessage('TASKS_NS_MANAGE'),
					"MAIL" => true,
					"XMPP" => true,
				),
			),
		);
	}
}

class CTasksPullSchema
{
	public static function OnGetDependentModule()
	{
		return Array(
			'MODULE_ID' => "tasks",
			'USE' => Array("PUBLIC_SECTION")
		);
	}
}

?>

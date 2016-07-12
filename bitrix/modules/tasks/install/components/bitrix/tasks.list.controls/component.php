<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arDefaultValues = array(
	'SHOW_TASK_LIST_MODES'   => 'Y',
	'SHOW_HELP_ICON'         => 'Y',
	'SHOW_SEARCH_FIELD'      => 'Y',
	'SHOW_TEMPLATES_TOOLBAR' => 'Y',
	'SHOW_QUICK_TASK_ADD'    => 'Y',
	'SHOW_ADD_TASK_BUTTON'   => 'Y',
	'SHOW_FILTER_BUTTON'     => 'Y'
);

// Set default values for omitted parameters
foreach ($arDefaultValues as $paramName => $paramDefaultValue)
{
	if ( ! array_key_exists($paramName, $arParams) )
		$arParams[$paramName] = $paramDefaultValue;
}

$this->IncludeComponentTemplate();

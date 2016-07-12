<?
$aMenuLinks = Array(
	Array(
		"Find Employee",
		"#SITE_DIR#company/index.php",
		Array(),
		Array(),
		""
	),
	Array(
		"Telephone Directory",
		"#SITE_DIR#company/telephones.php",
		Array(),
		Array(),
		""
	),
	Array(
		"Company Structure",
		"#SITE_DIR#company/vis_structure.php",
		Array(),
		Array(),
		""
	),
	Array(
		"Staff Changes",
		"#SITE_DIR#company/events.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('StaffChanges')"
	),
	Array(
		"Absence Chart",
		"#SITE_DIR#company/absence.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('StaffAbsence')"
	),
	Array(
		"Time Tracker",
		"#SITE_DIR#company/timeman.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('timeman')"
	),
	Array(
		"Reporting",
		"#SITE_DIR#company/work_report.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('timeman')"
	),
	Array(
		"Efficiency Report",
		"#SITE_DIR#company/report.php",
		Array(),
		Array(),
		"IsModuleInstalled('tasks')"
	),
	Array(
		"Honored Employees",
		"#SITE_DIR#company/leaders.php",
		Array(),
		Array(),
		""
	),
	Array(
		"Birthdays",
		"#SITE_DIR#company/birthdays.php",
		Array(),
		Array(),
		""
	),
	Array(
		"Shared Photos", 
		"#SITE_DIR#company/gallery/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Gallery')" 
	),
);
?>

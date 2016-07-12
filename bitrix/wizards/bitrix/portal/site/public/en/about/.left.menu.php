<?
$aMenuLinks = Array(
	Array(
		"Official Information", 
		"#SITE_DIR#about/index.php", 
		Array("#SITE_DIR#about/official.php"), 
		Array(), 
		"" 
	),
	Array(
		"Event Calendar", 
		"#SITE_DIR#about/calendar.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyCalendar')" 
	),
	Array(
		"Our Life", 
		"#SITE_DIR#about/life.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"About Company", 
		"#SITE_DIR#about/company/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Photo Gallery", 
		"#SITE_DIR#about/gallery/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyPhoto')" 
	),
	Array(
		"Video", 
		"#SITE_DIR#about/media.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyVideo')" 
	),
	Array(
		"Career", 
		"#SITE_DIR#about/career.php", 
		Array("#SITE_DIR#about/resume.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyCareer')" 
	),
	Array(
		"Business News (RSS)", 
		"#SITE_DIR#about/business_news.php", 
		Array(), 
		Array(), 
		"" 
	),
);
?>
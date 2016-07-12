<?
$aMenuLinks = Array(
	Array(
		"Authorization", 
		"#SITE_DIR#auth/index.php?login=yes", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Registration", 
		"#SITE_DIR#auth/index.php?register=yes", 
		Array(), 
		Array(), 
		"COption::GetOptionString(\"main\", \"new_user_registration\") == \"Y\"" 
	),
	Array(
		"Remind Pasword", 
		"#SITE_DIR#auth/index.php?forgot_password=yes", 
		Array(), 
		Array(), 
		"" 
	)
);
?>
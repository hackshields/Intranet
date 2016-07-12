function SetPrintCSS(isPrint)
{
	var link;

	if (document.getElementsByTagName)
		link = document.getElementsByTagName('link');
	else if (document.all)
		link = document.all.tags('link');
	else
		return;

	for (var index=0; index < link.length; index++)
	{
		if (!link[index].title || link[index].title != 'print')
			continue;

		if (isPrint)
		{
			link[index].disabled = false;
			link[index].rel = "stylesheet";
		}
		else
		{
			link[index].disabled = true;
			link[index].rel = "alternate stylesheet";
		}
	}
}

function AddToBookmark()
{
	var title = window.document.title;
	var url = window.document.location;

	if (window.sidebar)
	{
		window.sidebar.addPanel(title, url, "");
	}
	/*else if(window.opera)
	{
		var a = document.createElement("A");
		a.rel = "sidebar";
		a.target = "_search";
		a.title = title;
		a.href = url;
		a.click();
	}*/
	else if(document.all)
	{
		window.external.AddFavorite(url, title);
	}
	else
	{
		alert("Press Ctrl+D to bokmark this page");
	}
	
	return false;
}

function BackToDesignMode()
{
	if (document.all)
		window.location.href = window.location.href.replace('#print','');
	else
		SetPrintCSS(false);

	return false;
}

if (document.location.hash == '#print')
	SetPrintCSS(true);
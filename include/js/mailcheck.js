function emailCheck(emailStr) 
{
	// checks if the e-mail address is valid
	var emailPat = /^(\".*\"|[A-Za-z0-9\w\.\-]*)@(\[\d{1,3}(\.\d{1,3}){3}]|[A-Za-z\w\-]*(\.[A-Za-z]\w*)+)$/;
	var matchArray = emailStr.match(emailPat);
	if (matchArray == null) 
	{
		return false;
	}
	// make sure the IP address domain is valid
	var IPArray = matchArray[2].match(/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/);
	if (IPArray != null) 
	{
		for (var i=1;i<=4;i++) 
		{
			if (IPArray[i]>255) 
			{
				return false;
	      	}
	   	}
	}
	return true;
}

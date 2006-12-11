function FindProxyForURL(url,host)
{
	if (	isInNet(host,"10.0.0.0","255.0.0.0") ||
		isInNet(host,"172.16.0.0","255.255.0.0")     )
	{
		return "DIRECT";
	}
	else
	{
		return "PROXY proxy.technikum-wien.at:3128";
	}
}

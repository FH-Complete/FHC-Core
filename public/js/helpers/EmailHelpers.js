export async function splitMailsHelper(mails, event, subject, alertPluginRef, phrasenPluginRef) {
	let splititem = ",";
	let maillist = mails.join(splititem);
	let mailto = "";
	// take subject line length + '?subject=' length into account
	const subjectlength = subject && typeof subject === 'string' ? subject.length + 9 : 0 
	if (maillist.length > 2024)
	{
		if (await alertPluginRef.confirm({message: phrasenPluginRef.t('stv', 'zuvieleEMails') }) === false)
			return;
	}

	let firstrun = true;
	let useBcc = event?.ctrlKey || event?.metaKey;
	while (maillist.length > 0)
	{
		if (maillist.length + subjectlength > 2024)
		{
			let splitposition = maillist.lastIndexOf(splititem, 1900);
			mailto = maillist.substring(0, splitposition);
			maillist = maillist.substring(splitposition + 1);
		}
		else
		{
			mailto = maillist;
			maillist = "";
		}

		let mailLink = useBcc ? `mailto:?bcc=${mailto}` : `mailto:${mailto}`;
		if(subject && typeof subject === 'string') mailLink += `?subject=${subject}`
		if (firstrun)
		{
			window.location.href = mailLink;
			firstrun = false;
		}
		else
		{
			if (await alertPluginRef.confirm({message: phrasenPluginRef.t('stv', 'weitereEMail')}) === true)
			{
				window.location.href = mailLink;
			}
		}

	}
}
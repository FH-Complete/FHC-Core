export async function splitMailsHelper(mails, event, subject, body, alertPluginRef, phrasenPluginRef) {
	let splititem = ",";
	let maillist = mails.join(splititem);
	let mailto = "";
	const encodedBody = body && typeof body === 'string' ? encodeURIComponent(body) : null;
	const subjectlength = subject && typeof subject === 'string' ? subject.length + 9 : 0;
	const bodylength = encodedBody ? encodedBody.length + 6 : 0;
	const overhead = subjectlength + bodylength;

	debugger
	
	if (overhead > 2024)
	{
		await alertPluginRef.alertWarning({message: phrasenPluginRef.t('ui', 'bodyZuLang')});
		return;
	}

	if (maillist.length > 2024)
	{
		if (await alertPluginRef.confirm({message: phrasenPluginRef.t('ui', 'zuvieleEMails') }) === false)
			return;
	}

	let firstrun = true;
	let useBcc = event?.ctrlKey || event?.metaKey;
	while (maillist.length > 0)
	{
		if (maillist.length + overhead > 2024)
		{
			let splitposition = maillist.lastIndexOf(splititem, 1900 - overhead);
			mailto = maillist.substring(0, splitposition);
			maillist = maillist.substring(splitposition + 1);
		}
		else
		{
			mailto = maillist;
			maillist = "";
		}

		let mailLink = useBcc ? `mailto:?bcc=${mailto}` : `mailto:${mailto}`;
		if (subject && typeof subject === 'string') mailLink += `?subject=${subject}`;
		if (encodedBody) mailLink += `&body=${encodedBody}`;
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
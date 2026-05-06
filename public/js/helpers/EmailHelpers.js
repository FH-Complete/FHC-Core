export async function splitMailsHelper(mails, event, subject, body, alertPluginRef, phrasenPluginRef) {
	await phrasenPluginRef.loadCategory('ui')
	
	let splititem = ",";
	let maillist = mails.join(splititem);
	let mailto = "";
	let encodedBody = body && typeof body === 'string' ? encodeURIComponent(body) : null;
	const subjectlength = subject && typeof subject === 'string' ? subject.length + 9 : 0;
	let bodylength = encodedBody ? encodedBody.length + 6 : 0;
	let overhead = subjectlength + bodylength;

	if (overhead > 2024)
	{
		await alertPluginRef.alertWarning(phrasenPluginRef.t('ui', 'bodyZuLang'));
		encodedBody = null;
		bodylength = 0;
		overhead = subjectlength;
	}

	let firstrun = true;
	let useBcc = event?.ctrlKey || event?.metaKey;
	while (maillist.length > 0)
	{
		if (maillist.length + overhead > 2024)
		{
			let splitposition = maillist.lastIndexOf(splititem, 2024 - overhead);
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
			if (await alertPluginRef.confirm({message: phrasenPluginRef.t('ui', 'weitereEMail')}) === true)
			{
				window.location.href = mailLink;
			}
		}
	}
}
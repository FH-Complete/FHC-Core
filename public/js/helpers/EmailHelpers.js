export async function splitMailsHelper(mails, event, subject, body, alertPluginRef, phrasenPluginRef) {
	await phrasenPluginRef.loadCategory('ui');
	let splititem = ",";
	let maillist = mails.join(splititem);
	let useBcc = event?.ctrlKey || event?.metaKey;

	// build query parameters using URLSearchParams to get encoding
	const urlParams = new URLSearchParams();
	if (subject && typeof subject === 'string') {
		urlParams.append('subject', subject);
	}
	if (body && typeof body === 'string') {
		urlParams.append('body', body);
	}

	// initial overhead: "mailto:?bcc=" -> 12 chars, "mailto:" -> 7 chars
	const baseOverhead = useBcc ? 12 : 7;
	let queryString = urlParams.toString().replace(/\+/g, '%20');;
	let overhead = baseOverhead + (queryString ? 1 + queryString.length : 0); // +1 accounts for '?' or '&'

	// calculate overhead with body to exceed the limit
	if (overhead > 2024) {
		await alertPluginRef.alertWarning(phrasenPluginRef.t('ui', 'bodyZuLang'));
		urlParams.delete('body').replace(/\+/g, '%20');;
		queryString = urlParams.toString();
		overhead = baseOverhead + (queryString ? 1 + queryString.length : 0);
	}

	let firstrun = true;
	while (maillist.length > 0) {
		let mailto = "";
		if (maillist.length + overhead > 2024) {
			let splitposition = maillist.lastIndexOf(splititem, 2024 - overhead);

			// Fallback guard: if a single email address is somehow longer than the remaining space
			if (splitposition === -1) {
				splitposition = maillist.indexOf(splititem);
				if (splitposition === -1) splitposition = maillist.length;
			}

			mailto = maillist.substring(0, splitposition);
			maillist = maillist.substring(splitposition + 1);
		} else {
			mailto = maillist;
			maillist = "";
		}

		// construct the clean mailLink
		let mailLink = useBcc ? `mailto:?bcc=${mailto}` : `mailto:${mailto}`;
		if (queryString) {
			// If using BCC, the string already has a '?', so append with '&'. Otherwise, start with '?'
			mailLink += useBcc ? `&${queryString}` : `?${queryString}`;
		}

		if (firstrun) {
			window.location.href = mailLink;
			firstrun = false;
		} else {
			if (await alertPluginRef.confirm({message: phrasenPluginRef.t('ui', 'weitereEMail')}) === true) {
				window.location.href = mailLink;
			} else {
				break; // Stop processing further batches if the user cancels
			}
		}
	}
}
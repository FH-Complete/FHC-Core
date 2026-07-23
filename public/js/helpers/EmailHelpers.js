


export async function splitMailsHelper(mails, event, subject, alertPluginRef, phrasenPluginRef, uidCC= "") {
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

		let mailadressUid = uidCC + '@technikum-wien.at';

		let mailLink = useBcc ? `mailto:${mailadressUid}?cc=${mailadressUid}&bcc=${mailto}` : `mailto:${mailto}`;
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


/**
 * just splits the list of mails
 *
 * @param mails emailadresses
 * @param subject subject
 * @param useBcc useBcc
 * @returns array of links for splitted mails
 */
export function splitMailLinks(mails, subject = "", useBcc = false, uidCC= "", maxLength = 2024) {
	if (!Array.isArray(mails) || mails.length === 0) {
		return [];
	}

	const separator = ",";
	const encodedSubject = subject ? encodeURIComponent(subject) : "";

	// reserve space for the subject parameter
	const subjectLength = encodedSubject
		? `?subject=${encodedSubject}`.length
		: 0;

	const limit = maxLength - subjectLength;

	const links = [];
	let currentRecipients = [];

	for (const mail of mails) {
		if (!mail) continue;

		const testRecipients = [...currentRecipients, mail];
		const recipientString = testRecipients.join(separator);

		if (recipientString.length > limit && currentRecipients.length > 0) {

			links.push(createMailto(
				currentRecipients.join(separator),
				encodedSubject,
				useBcc,
				uidCC
			));

			currentRecipients = [mail];
		}
		else {
			currentRecipients.push(mail);
		}
	}

	if (currentRecipients.length > 0) {
		links.push(createMailto(
			currentRecipients.join(separator),
			encodedSubject,
			useBcc,
			uidCC
		));
	}

	return links;
}

function createMailto(recipients, encodedSubject, useBcc, uidCC) {
	let mailadressUid = uidCC + '@technikum-wien.at';

	let link = useBcc
		? `mailto:${encodeURIComponent(mailadressUid)}?cc=${encodeURIComponent(mailadressUid)}&bcc=${encodeURIComponent(recipients)}`
		: `mailto:${encodeURIComponent(recipients)}`;

	if (encodedSubject) {
		link += `${useBcc ? "&" : "?"}subject=${encodedSubject}`;
	}

	return link;
}

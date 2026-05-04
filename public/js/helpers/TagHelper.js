export function addTagInTable(addedTag, rows, matchKey, tagsKey = "tags")
{
	if (!addedTag || !Array.isArray(addedTag.response))
		return;

	const { response, ...baseTag } = addedTag;
	rows.forEach(row =>
	{
		const rowData = row.getData();
		let updates = {};
		let changed = false;

		addedTag.response.forEach(tag =>
		{
			if (rowData[matchKey] !== tag[matchKey])
				return;

			let tags;
			try {
				tags = JSON.parse(rowData[tagsKey] || "[]");
			} catch (e) {
				tags = [];
			}

			if (!Array.isArray(tags))
				tags = [];

			if (tags.some(t => t?.id === tag.id))
				return;

			tags.unshift({...baseTag, ...tag});

			updates[tagsKey] = JSON.stringify(tags);
			changed = true;
		});

		if (changed)
		{
			row.update(updates);
			row.reformat();
		}
	});
}

export function deleteTagInTable(deletedTag, rows, tagsKeys = ['tags'])
{
	if (!Array.isArray(tagsKeys))
		tagsKeys = [tagsKeys];

	rows.forEach(row => {
		let rowData = row.getData();
		let updates = {};
		let changed = false;

		tagsKeys.forEach(key => {
			let tags;

			try {
				tags = JSON.parse(rowData[key] || "[]");
			} catch (e) {
				tags = [];
			}

			if (!Array.isArray(tags))
				return;

			let filtered = tags.filter(tag => tag?.id !== deletedTag);

			if (filtered.length !== tags.length)
			{
				updates[key] = JSON.stringify(filtered);
				changed = true;
			}
		});

		if (changed) {
			row.update(updates);
			row.reformat();
		}
	});
}


export function updateTagInTable(updatedTag, rows, fields = ['tags'])
{
	if (!Array.isArray(fields))
		fields = [fields];

	rows.forEach(row =>
	{
		const rowData = row.getData();
		const updates = {};
		let changed = false;

		fields.forEach(field =>
		{
			let tags;

			try {
				tags = JSON.parse(rowData[field] || "[]");
			} catch (e) {
				return;
			}

			if (!Array.isArray(tags))
				return;

			const index = tags.findIndex(tag => String(tag?.id) === String(updatedTag.id));

			if (index === -1)
				return;

			tags[index] = {...tags[index], ...updatedTag};

			updates[field] = JSON.stringify(tags);
			changed = true;
		});

		if (changed)
		{
			row.update(updates);
			row.reformat();
		}
	});
}
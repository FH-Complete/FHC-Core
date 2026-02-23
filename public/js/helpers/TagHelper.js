export function addTagInTable(addedTag, rows, matchKey, tagsKey = "tags")
{
	if (!addedTag || !Array.isArray(addedTag.response))
		return;

	rows.forEach(row =>
	{
		const rowData = row.getData();
		let updated = false;

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

			let newTag = { ...addedTag, id: tag.id };

			tags.unshift(newTag);

			rowData[tagsKey] = JSON.stringify(tags);
			updated = true;
		});

		if (updated)
			row.update(rowData);
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
		let updated = false;

		fields.forEach(field =>
		{
			if (!rowData[field])
				return;

			let fieldData;
			try {
				fieldData = JSON.parse(rowData[field] || "[]");
			} catch (e) {
				return;
			}

			if (!Array.isArray(fieldData))
				return;

			let index = fieldData.findIndex(tag => tag?.id === updatedTag.id);

			if (index !== -1)
			{
				fieldData[index] = { ...updatedTag };
				let updatedFieldData = JSON.stringify(fieldData);

				if (updatedFieldData !== rowData[field])
				{
					rowData[field] = updatedFieldData;
					updated = true;
				}
			}
		});

		if (updated)
			row.update(rowData);
	});
}

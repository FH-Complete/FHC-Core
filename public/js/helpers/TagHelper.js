export function addTagInTable(addedTag, rows, matchKey, tagsKey = "tags")
{
	if (!addedTag || !Array.isArray(addedTag.response))
		return;

	rows.forEach(row =>
	{
		const rowData = row.getData();

		//add save check if String or Array and avoid same reference later for mutations
		let raw = rowData[tagsKey];
		let tags = typeof raw === "string"
			? JSON.parse(raw || "[]")
			: Array.isArray(raw)
				? [...raw]
				: [];

		let updated = false;

		for (const tag of addedTag.response)
		{
			if (rowData[matchKey] !== tag[matchKey])
				continue;

			//avoid double inserts
			if (tags.some(x => x.id === tag.id))
				continue;

			const newTag = {
				id: tag.id,
				prestudent_id: tag.prestudent_id,
				//add also information of addedTag
				beschreibung: addedTag.beschreibung,
				notiz: addedTag.notiz ?? "",
				style: addedTag.style,
				done: addedTag.done ?? false,
				typ_kurzbz: addedTag.tag_typ_kurzbz ?? addedTag.typ_kurzbz, //here seem to be 2 variations
				automatisiert: addedTag.automatisiert
			};

			tags.unshift(newTag);
			updated = true;
		}

		if (updated)
		{
			row.update({
				[tagsKey]: JSON.stringify(tags)
			});

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

		for (const key of tagsKeys) {
			let raw = rowData[key];

			let tags = [];

			try {
				if (typeof raw === "string") {
					tags = JSON.parse(raw || "[]");
				} else if (Array.isArray(raw)) {
					tags = [...raw];
				}
			} catch (e) {
				tags = [];
			}

			if (!Array.isArray(tags))
				continue;

			let filtered = tags.filter(tag => tag?.id !== deletedTag);

			if (filtered.length !== tags.length) {
				updates[key] = JSON.stringify(filtered);
				changed = true;
			}
		}

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

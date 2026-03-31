

export function idTagFormatter (id, tagData, tagComponent, typeId)
{
	if (!id) return;

	const parsedTags = tagData.map(tag => ({
		id: tag.notiz_id,
		typ_kurzbz: tag.titel?.toLowerCase(),
		beschreibung: tag.bezeichnung,
		notiz: tag.text || "",
		style: tag.style,
		done: tag.done,
		automatisiert: tag.automatisiert,
		typeId: id
	}));

	let container = document.createElement('div');
	container.className = "d-flex gap-1";

	let maxVisibleTags = 5;
	let expanded = false;
	const renderTags = () => {
		container.innerHTML = '';

		let filtered = parsedTags.filter(t => t != null);

		filtered.sort((a, b) => {
			let adone = a.done ? 1 : 0;
			let bdone = b.done ? 1 : 0;

			if (adone !== bdone) return adone - bdone;
			return b.id - a.id;
		});

		const tagsToShow = expanded
			? filtered
			: filtered.slice(0, maxVisibleTags);

		tagsToShow.forEach(tag => {
			let tagElement = document.createElement('span');
			tagElement.innerText = tag.beschreibung;
			tagElement.title = tag.notiz;
			tagElement.className = "tag " + tag.style;

			if (tag.done) {
				tagElement.className += " tag_done";
			}
			if (tag.automatisiert)
				tagElement.className += " tag_auto";

			tagElement.addEventListener('click', (event) => {
				event.stopPropagation();
				event.preventDefault();
				tagComponent.editTag(tag.id);
			});

			container.appendChild(tagElement);
		});

		if (filtered.length > maxVisibleTags) {
			let toggle = document.createElement('button');
			toggle.innerText = (expanded ? '- ' : '+ ') + (filtered.length - maxVisibleTags);
			toggle.className = "display_all";

			toggle.addEventListener('click', () => {
				expanded = !expanded;
				renderTags();
			});

			container.appendChild(toggle);
		}
	};

	renderTags();

	return container;
}
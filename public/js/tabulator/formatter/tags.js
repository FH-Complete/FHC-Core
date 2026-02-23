export function tagFormatter(cell, tagComponent)
{
	let tags = cell.getValue();
	if (!tags) return;

	let container = document.createElement('div');
	container.className = "d-flex gap-1";

	let parsedTags = JSON.parse(tags);
	let maxVisibleTags = 2;

	const rowData = cell.getRow().getData();
	if (rowData._tagExpanded === undefined) {
		rowData._tagExpanded = false;
	}

	const renderTags = () => {
		container.innerHTML = '';
		parsedTags = parsedTags.filter(item => item !== null);

		parsedTags.sort((a, b) => {
			let adone = a.done ? 1 : 0;
			let bbone = b.done ? 1 : 0;

			if (adone !== bbone)
			{
				return adone - bbone;
			}
			return b.id - a.id;
		});
		const tagsToShow = rowData._tagExpanded ? parsedTags : parsedTags.slice(0, maxVisibleTags);

		tagsToShow.forEach(tag => {
			if (!tag) return;
			let tagElement = document.createElement('span');
			tagElement.innerText = tag.beschreibung;
			tagElement.title = tag.notiz;
			tagElement.className = "tag " + tag.style;
			if (tag.done) tagElement.className += " tag_done";

			tagElement.addEventListener('click', (event) => {
				event.stopPropagation();
				event.preventDefault();
				tagComponent.editTag(tag.id);
			});

			container.appendChild(tagElement);
		});

		if (parsedTags.length > maxVisibleTags) {
			let toggle = document.createElement('button');
			toggle.innerText = (rowData._tagExpanded ? '- ' : '+ ') + (parsedTags.length - maxVisibleTags);
			toggle.className = "display_all";
			toggle.title = rowData._tagExpanded ? "Tags ausblenden" : "Tags einblenden";

			toggle.addEventListener('click', () => {
				rowData._tagExpanded = !rowData._tagExpanded;
				renderTags();
			});

			container.appendChild(toggle);
		}
	};

	renderTags();
	return container;
}
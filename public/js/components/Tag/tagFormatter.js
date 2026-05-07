export function idTagFormatter (id, tagData, tagComponent, typeId, semesterStart=null, semesterEnd=null)
{
	if (!id) return;

	const hasSemesterFilter = !!(semesterStart && semesterEnd);

	const semStart = hasSemesterFilter ? new Date(semesterStart) : null;
	const semEnd = hasSemesterFilter ? new Date(semesterEnd) : null;

	const parsedTags = tagData.map(tag => ({
		id: tag.notiz_id,
		typ_kurzbz: tag.titel?.toLowerCase(),
		beschreibung: tag.bezeichnung,
		notiz: tag.text || "",
		style: tag.style,
		done: tag.done,
		automatisiert: tag.automatisiert,
		typeId: id,
		validFrom: tag.start ? new Date(tag.start) : null,
		validTo: tag.ende ? new Date(tag.ende) : null
	}));

	const isInSemester = (tag) => {
		if (!hasSemesterFilter) return true;

		if (!tag.validFrom && !tag.validTo) return true;

		if (!tag.validFrom && !tag.validTo) return true;

		if (tag.validFrom && tag.validTo) {
			return tag.validFrom <= semEnd && tag.validTo >= semStart;
		}

		if (tag.validFrom && !tag.validTo) {
			return tag.validFrom <= semEnd;
		}

		if (!tag.validFrom && tag.validTo) {
			return tag.validTo >= semStart;
		}

		return false;
	};

	let container = document.createElement('div');
	container.className = "d-flex gap-1";

	let maxVisibleTags = 5;
	let expanded = false;
	const renderTags = () => {
		container.innerHTML = '';

		let filtered = parsedTags
			.filter(t => t != null)
			.filter(isInSemester);

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

			if(tag.automatisiert)
				tagElement.innerHTML = "<i class='fa-solid fa-lock'></i> " + tag.beschreibung;
			else
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
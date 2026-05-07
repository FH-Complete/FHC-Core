export function tagFormatter(
	cell,
	tagComponent,
	semesterStart = null,
	semesterEnd = null
) {

	// support both call versions
	// 1. previous Tabulator cell: old version
	// 2. custom enriched object: with start and end for tags plus semesterDates
	//    for check if valid tag

	const isTabulatorCell =
		cell && typeof cell.getValue === 'function';

	const normalized = isTabulatorCell
		? {
			tags: cell.getValue() || [],
			start: null,
			ende: null,
			rowData: cell.getRow().getData(),
		}
		: {
			tags: cell.tags || [],
			start: cell.start || null,
			ende: cell.ende || null,
			rowData: cell.rowData || {},
		};

	const tags = normalized.tags || [];

	if (!tags.length) {
		return "";
	}

	const mappedData = tagComponent.tags.map(tag => ({
		typ_kurzbz: tag.tag_typ_kurzbz,
		automatisiert: tag.automatisiert,
		validFrom: normalized.start
			? new Date(normalized.start)
			: null,
		validTo: normalized.ende
			? new Date(normalized.ende)
			: null
	}));

	const hasSemesterFilter =
		!!(semesterStart && semesterEnd);

	const semStart = hasSemesterFilter ? new Date(semesterStart) : null;

	const semEnd = hasSemesterFilter	? new Date(semesterEnd)	: null;

	const isInSemester = (tag) => {

		if (!hasSemesterFilter) {
			return true;
		}

		if (!tag.validFrom && !tag.validTo) {
			return true;
		}

		if (tag.validFrom && tag.validTo) {
			return (
				tag.validFrom <= semEnd &&
				tag.validTo >= semStart
			);
		}

		if (tag.validFrom && !tag.validTo) {
			return tag.validFrom <= semEnd;
		}

		if (!tag.validFrom && tag.validTo) {
			return tag.validTo >= semStart;
		}

		return false;
	};

	// parse tags if needed
	let parsedTags =
		typeof tags === 'string'
			? JSON.parse(tags)
			: tags;

	let container = document.createElement('div');
	container.className = "d-flex gap-1";

	let maxVisibleTags = 2;

	const rowData = normalized.rowData;

	if (rowData._tagExpanded === undefined) {
		rowData._tagExpanded = false;
	}

	const renderTags = () => {
		container.innerHTML = '';

		parsedTags = parsedTags.filter(tag => {
			const mapped = mappedData.find(
				m => m.typ_kurzbz === tag.typ_kurzbz
			);

			if (!mapped) {
				return true;
			}

			return isInSemester(mapped);
		});

		parsedTags.sort((a, b) => {
			let adone = a.done ? 1 : 0;
			let bbone = b.done ? 1 : 0;

			if (adone !== bbone)
			{
				return adone - bbone;
			}
			return b.id - a.id;
		});

		const tagsToShow = rowData._tagExpanded
			? parsedTags
			: parsedTags.slice(0, maxVisibleTags);

		tagsToShow.forEach(tag => {
			if (!tag) return;

			let tagElement = document.createElement('span');
			tagElement.innerText = tag.beschreibung;
			tagElement.title = tag.notiz;
			tagElement.className = "tag " + tag.style;
			if (tag.done) tagElement.className += " tag_done";

			const tagDef = mappedData.find(t => t.typ_kurzbz === tag.typ_kurzbz);

			if (!tagDef && tag.typ_kurzbz?.includes("_auto") || tagDef?.automatisiert) {
				tagElement.className += " tag_auto";
				tagElement.innerHTML = "<i class='fa-solid fa-lock'></i> " + tag.beschreibung;
			}

			tagElement.addEventListener('click', (event) => {
				event.stopPropagation();
				event.preventDefault();
				tagComponent.editTag(tag.id)
			});

			container.appendChild(tagElement);
		});

		// show expand button
		if ( parsedTags.length > maxVisibleTags) {
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
/**
 * The layout of the Benotungstool table: what the browser keeps (column order, width, visibility,
 * filters, sort), the sticky columns, and the scroll position during a render.
 */

/** Reads a value that the browser keeps. Private mode or a broken value give `fallback`. */
export function loadKept(key, fallback) {
	try {
		const value = localStorage.getItem(key);
		return value === null ? fallback : JSON.parse(value);
	} catch (e) {
		return fallback;
	}
}

export function saveKept(key, value) {
	try {
		localStorage.setItem(key, JSON.stringify(value));
	} catch (e) {
		// private mode: the value lasts until the next load
	}
}

/** Keeps the layout of the columns in `fields`. The Pruefung columns change per LV, so they are left out. */
export function saveLayout(key, table, fields) {
	saveKept(key, {
		columns: table.getColumnLayout()
			.filter(col => fields.includes(col.field))
			.map(col => ({ field: col.field, visible: col.visible, width: col.width })),
		sort: table.getSorters().map(s => ({ field: s.field, dir: s.dir })),
		filters: table.getFilters(),
		headerFilters: table.getHeaderFilters()
	});
}

/**
 * The column definitions in the kept order, with the kept width and visibility. New columns come last.
 * Apply it BEFORE Tabulator takes the columns: after that the definitions and the Vue titles conflict.
 */
export function restoreColumns(columns, layout) {
	if (!layout?.columns) return columns;

	const byField = new Map(columns.map(c => [c.field, c]));
	const restored = [];

	layout.columns.forEach(kept => {
		const column = byField.get(kept.field);
		if (!column) return;

		restored.push({ ...column, width: kept.width, visible: kept.visible });
		byField.delete(kept.field);
	});

	byField.forEach(column => restored.push(column));
	return restored;
}

/** Applies the kept filters and sort. Call it once, after the first rows: then the Pruefung columns exist. */
export function restoreFiltersAndSort(table, layout) {
	if (layout?.filters?.length) table.setFilter(layout.filters);

	(layout?.headerFilters ?? []).forEach(hf => table.setHeaderFilterValue(hf.field, hf.value));

	// a Pruefung column of another LV does not exist in this table
	const fields = table.getColumns().map(column => column.getField());
	const sorters = (layout?.sort ?? [])
		.filter(s => fields.includes(s.field))
		.map(s => ({ column: s.field, dir: s.dir }));
	if (sorters.length) table.setSort(sorters);
}

/**
 * Pins the selected columns to the left. A `sticky-on-<field>` class on the container switches one
 * column on (see Benotungstool.css), and `--sl-<field>` is its left offset: the width of the pinned
 * columns before it, so that the pinned columns stand next to each other.
 */
export function applyStickyColumns(element, table, fields, selected) {
	if (!element || !table) return;

	let offset = 0;
	// the display order, so a moved column keeps the right offset
	table.getColumns().forEach(column => {
		const field = column.getField();
		if (!fields.includes(field)) return;

		const pinned = selected.includes(field);
		element.classList.toggle('sticky-on-' + field, pinned);
		element.style.setProperty('--sl-' + field, offset + 'px');
		if (pinned) offset += column.getWidth();
	});
}

/**
 * Keeps the scroll position while `operation` renders the table again.
 *
 * Read and write .tabulator-tableholder directly: Tabulator does not update rowManager.scrollLeft.
 * Set the position again in the next two frames: Tabulator renders later and resets it, and
 * Vue.nextTick is too early.
 */
export function preserveScroll(table, operation) {
	const holder = table?.element?.querySelector('.tabulator-tableholder');
	const left = holder?.scrollLeft ?? 0;
	const top = holder?.scrollTop ?? 0;

	const result = operation();

	// nothing to keep; do not work against a scroll to the top that the user wants
	if (!holder || (!left && !top)) return result;

	const restore = () => {
		holder.scrollLeft = left;
		holder.scrollTop = top;
	};

	restore();
	requestAnimationFrame(() => { restore(); requestAnimationFrame(restore); });

	// setData returns a promise; set the position again after the load
	if (result && typeof result.then === 'function') result.then(() => requestAnimationFrame(restore));

	return result;
}

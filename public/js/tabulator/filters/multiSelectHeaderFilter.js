// Tabulator headerFilter editor: readonly input opening a checkbox list which is attached to the body,
// so it is not clipped by the table header. Filter value is the array of selected option values.
//
// params:
//   options   () => [{ value, label, badge: { cssClass, html } }] - resolved on every open, so
//             asynchronously loaded lists (noten, abgabetypen) are picked up even if they were
//             not available yet when the header filter was rendered
//   selected  { get, set } - holds the selected values outside of the editor, since tabulator
//             rebuilds the editor on every column redraw
//   minWidth  min width of the dropdown
export function multiSelectHeaderFilter(cell, onRendered, success, cancel, params = {}) {
	let selected = [...(params.selected?.get() ?? [])];
	let options = [];

	const wrapper = document.createElement('div');
	wrapper.style.cssText = 'position: relative; width: 100%;';

	const display = document.createElement('input');
	display.readOnly = true;
	display.placeholder = '';
	display.style.cssText = 'padding: 4px; width: 100%; box-sizing: border-box; cursor: default; border: 1px solid; outline: none; background: #fff; appearance: none; caret-color: transparent;';

	const dropdown = document.createElement('div');
	dropdown.style.cssText = 'display: none; position: fixed; background: #fff; border: 1px solid; z-index: 9999; min-width: '
		+ (params.minWidth ?? '180px') + '; box-shadow: 0 2px 6px rgba(0,0,0,0.15);';

	const updateDisplay = () => {
		display.value = options
			.filter(o => selected.includes(o.value))
			.map(o => o.label)
			.join(', ');
	};

	const buildOptionRow = (opt) => {
		const row = document.createElement('label');
		row.style.cssText = opt.badge
			? 'display: flex; align-items: center; gap: 0; cursor: pointer; white-space: nowrap; padding-right: 8px;'
			: 'display: flex; align-items: center; gap: 6px; padding: 4px 8px; cursor: pointer; white-space: nowrap;';
		row.addEventListener('mousedown', e => e.preventDefault());

		const checkbox = document.createElement('input');
		checkbox.type = 'checkbox';
		checkbox.value = opt.value;
		checkbox.checked = selected.includes(opt.value);
		checkbox.style.cssText = 'margin: 0 6px;';
		checkbox.addEventListener('change', () => {
			selected = checkbox.checked
				? [...selected, opt.value]
				: selected.filter(v => v !== opt.value);
			params.selected?.set([...selected]);
			updateDisplay();
			success([...selected]);
		});
		row.appendChild(checkbox);

		if (opt.badge) {
			// icon badge — same look as the cell
			const badge = document.createElement('div');
			badge.className = opt.badge.cssClass;
			badge.style.cssText = `min-width: 36px; height: 36px; display: flex; align-items: center;
			justify-content: center; flex-shrink: 0; padding: 0px 17px 0px 17px;`;
			badge.innerHTML = opt.badge.html;
			row.appendChild(badge);
		}

		const labelText = document.createElement('span');
		labelText.textContent = opt.label;
		if (opt.badge) labelText.style.cssText = 'margin-left: 6px;';
		row.appendChild(labelText);

		return row;
	};

	const renderOptions = () => {
		options = params.options?.() ?? [];
		dropdown.replaceChildren(...options.map(buildOptionRow));
		updateDisplay();
	};

	renderOptions();

	display.addEventListener('click', () => {
		if (dropdown.style.display === 'none') {
			renderOptions();
			const rect = display.getBoundingClientRect();
			dropdown.style.top = rect.bottom + 'px';
			dropdown.style.left = rect.left + 'px';
			dropdown.style.display = 'block';
		} else {
			dropdown.style.display = 'none';
		}
	});

	display.addEventListener('blur', () => {
		setTimeout(() => { dropdown.style.display = 'none'; }, 150);
	});

	document.body.appendChild(dropdown);
	wrapper.appendChild(display);
	cell.getElement().addEventListener('remove', () => dropdown.remove());
	onRendered(() => display.focus());

	return wrapper;
}

export default multiSelectHeaderFilter;

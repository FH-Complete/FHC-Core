export function centeredTextFormatter(cell) {
	const longForm = cell.getValue()
	if(!longForm) return
	const data = cell.getData()
	const entry = Object.entries(data).find(entry => entry[1] == longForm)

	// shortFormKey must have same keyname as longForm but with 'Short' appended 
	const shortForm = data[entry[0]+'Short']

	if(shortForm && longForm) {
		return `<div style="display: flex; justify-content: start; align-items: center; height: 100%; width: 100%;">
				<span class="full-text" style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">
					${longForm}
				</span>
				<span class="short-text" style="font-weight: bold; display: none;">
					${shortForm}
				</span>
				</div>`;
	} else {
		return '<div style="display: flex; justify-content: start; align-items: center; height: 100%">' +
			'<p style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">'+longForm+'</p></div>'
	}
}
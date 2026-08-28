function dateinput(headerValue, rowValue, rowData, config) {
	if (!luxon)
		return this.like(headerValue, rowValue, rowData, config);
	
	let inputFormat = config.inputFormat || 'iso';
	let outputFormat = config.outputFormat || 'dd.MM.yyyy';

	let value;
	if (inputFormat == 'iso')
		value = luxon.DateTime.fromISO(rowValue);
	else
		value = luxon.DateTime.fromFormat(rowValue, inputFormat);
	
	let range = [];
	range = headerValue.split(' - ');
	if (range.length == 1) {
		range = headerValue.split('-');
	}
	if (range.length == 2) {
		range = range.map(r => {
			r = r.trim();
			if (r) {
				let d = luxon.DateTime.fromFormat(r, outputFormat);
				if (!d.isValid && inputFormat != 'iso' && inputFormat != outputFormat)
					d = luxon.DateTime.fromFormat(r, inputFormat);
				if (!d.isValid)
					d = luxon.DateTime.fromISO(r);
				if (d.isValid)
					return d;
			}
			return null;
		});
		if (!range[0] && !range[1])
			return true;
		else if (!range[0])
			return this['<='](range[1], value, rowData, config);
		else if (!range[1])
			return this['>='](range[0], value, rowData, config);

		let smaller = this['<='](range[1], value, rowData, config);
		let bigger = this['>='](range[0], value, rowData, config);
		return smaller && bigger;
	}

	// fallback
	return this.like(headerValue, value.toFormat(outputFormat), rowData, config);
}

export {
	dateinput
};
export default {
	dateinput
};

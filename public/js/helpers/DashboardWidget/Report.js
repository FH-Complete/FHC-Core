/**
 * Compile variable values into label.
 *
 * Usage:
 * { filtername } or { filtername:sqlfield }
 *
 * The sqlfield in the first option defaults to either 'name' or the first field
 * not named 'value' in the query. If a field is not found 'value' is displayed.
 *
 * @param string	string
 * @param object	values
 * @param object	details
 * @return string
 */
function compileLabel(string, values, details) {
	return string.replace(
		/\{\s*([^\}]+)\s*\}/g,
		(match, key) => {
			const [ name, field ] = key.split(':');
			if (values[name] === undefined)
				return match;
			
			const options = details[name].detail?.options;
			if (options && field != 'value') {
				const option = options.find(opt => opt.value == values[name]);
				if (!field) {
					if (option?.label !== undefined)
						return option.label;
				} else {
					if (option?.original && option?.original[field] !== undefined)
						return option.original[field];
				}
			}
			return values[name];
		}
	);
}

export {
	compileLabel,
};

export default {
	compileLabel,
};

function parseFilterExpression(expression)
{
	const collections = [];

	try {
		const orParts = expression.split('||').map(part => part.trim());

		orParts.forEach(part => {

			const andParts = part.split('&&').map(p => p.trim());

			const collection = { positives: [], negatives: [] };

			andParts.forEach(term => {

				const comparisonMatch = term.match(/^(<=|>=|<|>|=|!=)\s*(\d+(?:[.,]\d+)?)$/);

				if (comparisonMatch)
				{
					const operator = comparisonMatch[1];
					const numberStr = comparisonMatch[2].replace(',', '.');
					const number = parseFloat(numberStr);
					collection.positives.push({ type: 'comparison', operator, number });
				}
				else if (term.startsWith('!'))
				{
					const excludeTerm = term.substring(1).trim().replace(/\*/g, '.*');
					collection.negatives.push({ type: 'regex', regex: new RegExp(excludeTerm, 'i') });
				}
				else
				{
					const includeTerm = term.replace(/\*/g, '.*');
					collection.positives.push({ type: 'regex', regex: new RegExp(includeTerm, 'i') });
				}
			});
			collections.push(collection);
		});
	} catch (e) {}
	return collections;
}

export function extendedHeaderFilter(headerValue, rowValue)
{
	if (typeof headerValue === 'boolean')
	{
		return rowValue === headerValue;
	}

	const collections = parseFilterExpression(headerValue);

	try {

		return collections.some(collection => {

			let positives = collection.positives.length === 0 || collection.positives.every(condition => {

				if (condition.type === 'comparison')
				{
					let value = parseFloat(rowValue);
					if (isNaN(value)) return false;

					switch (condition.operator) {
						case '<':
							return value < condition.number;
						case '>':
							return value > condition.number;
						case '<=':
							return value <= condition.number;
						case '>=':
							return value >= condition.number;
						case '=':
							return value === condition.number;
						case '!=':
							return value !== condition.number;
						default:
							return false;
					}
				}
				else if (condition.type === 'regex')
				{
					return condition.regex.test(rowValue);
				}
				return false;
			});

			let negatives = collection.negatives.every(condition => {
				return !condition.regex.test(rowValue);
			});

			return positives && negatives;
		});
	} catch (e) {

	}
}
export function tagHeaderFilter(headerValue, rowValue) {

	let data;

	try {
		data = typeof rowValue === 'string' ? JSON.parse(rowValue) : rowValue;
	} catch (error) {
		return false;
	}

	let combinedText;

	if (Array.isArray(data))
	{
		combinedText = data
			.map(item => `${item?.beschreibung} ${item?.notiz}`)
			.join(' ');
	}
	else if (typeof data === 'object' && data !== null)
	{
		combinedText = `${data?.beschreibung} ${data?.notiz}`;
	}
	else
	{
		combinedText = String(data);
	}

	return extendedHeaderFilter(headerValue, combinedText)
}


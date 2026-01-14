function parseFilterExpression(expression)
{
	const collections = [];

	try {
		const orParts = expression.split('||').map(part => part.trim());

		orParts.forEach(part => {

			const andParts = part.split('&&').map(p => p.trim());

			const collection = { positives: [], negatives: [] };

			andParts.forEach(term => {

				const comparisonMatch = term.match(/^(<=|>=|<|>|=|!=)\s*(-?\d+(?:[.,]\d+)?)$/);

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

function collectChildren(rowData, childrenField, remembered)
{
	const children = rowData[childrenField];
	if (Array.isArray(children))
	{
		for (let child of children)
		{
			remembered.add(child);
			collectChildren(child, childrenField, remembered);
		}
	}
}

export function extendedHeaderFilter(headerValue, rowValue, rowData, filterParams)
{
	if (!extendedHeaderFilter._remembered)
	{
		extendedHeaderFilter._remembered = new Set();
		extendedHeaderFilter._lastHeaderKey = null;
	}

	const headerKey = headerValue === null || headerValue === undefined ? '' : String(headerValue);

	if (extendedHeaderFilter._lastHeaderKey !== headerKey)
	{
		extendedHeaderFilter._lastHeaderKey = headerKey;
		extendedHeaderFilter._remembered.clear();
	}

	if (rowData && extendedHeaderFilter._remembered.has(rowData))
	{
		return true;
	}

	const fields = Array.isArray(filterParams?.field)
		? filterParams.field
		: [filterParams?.field];

	if (fields.length > 1 && rowData)
	{
		rowValue = fields
			.map(f => rowData[f] ?? '')
			.filter(Boolean)
			.join(' ');
	}
	if (typeof headerValue === 'boolean')
	{
		return rowValue === headerValue;
	}

	const collections = parseFilterExpression(headerValue);

	function matchValue(value)
	{
		try {

			const text = String(value ?? '');

			return collections.some(collection => {

				let positives = collection.positives.length === 0 || collection.positives.every(condition => {

					if (condition.type === 'comparison')
					{
						let num = parseFloat(text.replace(',', '.'));
						if (isNaN(num))
							return false;

						switch (condition.operator) {
							case '<':
								return num < condition.number;
							case '>':
								return num > condition.number;
							case '<=':
								return num <= condition.number;
							case '>=':
								return num >= condition.number;
							case '=':
								return num === condition.number;
							case '!=':
								return num !== condition.number;
							default:
								return false;
						}
					}
					else if (condition.type === 'regex')
					{
						return condition.regex.test(text);
					}
					return false;
				});

				let negatives = collection.negatives.every(condition => {
					return !condition.regex.test(text);
				});

				return positives && negatives;
			});
		} catch (e) {

		}
		return false;
	}

	if (matchValue(rowValue))
	{
		if (rowData && filterParams)
		{
			const childrenField = filterParams?.children || '_children';
			collectChildren(rowData, childrenField, extendedHeaderFilter._remembered);
		}
		return true;
	}

	if (rowData && filterParams)
	{
		const childrenField = filterParams?.children || '_children';
		const field = filterParams?.field;

		const children = rowData[childrenField];
		if (Array.isArray(children))
		{
			for (let child of children)
			{
				let childValue = child[field];
				if (extendedHeaderFilter(headerValue, childValue, child, filterParams))
				{
					return true;
				}
			}
		}
	}

	return false;
}
export function tagHeaderFilter(headerValue, rowValue, rowData, filterParams)
{

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
			.filter(item => item?.done === false)
			.map(item => `${item?.beschreibung} ${item?.notiz}`)
			.join(' ');
	}
	else if (typeof data === 'object' && data !== null)
	{
		combinedText = data?.erledigt === false ? `${data?.beschreibung} ${data?.notiz}` : '';
	}
	else
	{
		combinedText = String(data);
	}

	return extendedHeaderFilter(headerValue, combinedText, rowData, filterParams)
}
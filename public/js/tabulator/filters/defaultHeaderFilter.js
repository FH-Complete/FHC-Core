function parseFilterExpression(expression){
	const includeGroups = [];
	const excludeTerms = [];
	const comparisons = [];

	const andParts = expression.split('&&').map(part => part.trim());

	andParts.forEach(part => {
		const orTerms = part.split('||').map(p => p.trim());
		const orRegexes = [];

		orTerms.forEach(term => {

			const comparisonMatch = term.match(/^(<=|>=|<|>|=|!=)\s*(\d+)$/);

			if (comparisonMatch)
			{
				const operator = comparisonMatch[1];
				const number = parseFloat(comparisonMatch[2]);

				comparisons.push({ operator, number });
			}
			else if (term.startsWith('!'))
			{
				const excludeTerm = term.substring(1).trim().replace(/\*/g, '.*');
				excludeTerms.push(new RegExp(excludeTerm, 'i'));
			}
			else
			{
				const includeTerm = term.replace(/\*/g, '.*');
				orRegexes.push(new RegExp(includeTerm, 'i'));
			}
		});

		if (orRegexes.length > 0)
		{
			includeGroups.push(orRegexes);
		}
	});

	return { includeGroups, excludeTerms, comparisons };
}

export function defaultHeaderFilter(headerValue, rowValue)
{
	const { includeGroups, excludeTerms, comparisons } = parseFilterExpression(headerValue);

	const includes = includeGroups.every(group =>
		group.some(regex => regex.test(rowValue))
	);

	const excludes = excludeTerms.every(regex => !regex.test(rowValue));

	const comparisonCheck = comparisons.every(({ operator, number }) => {
		let value = rowValue;

		if (!isNaN(number))
		{
			value = parseFloat(rowValue);
			if (isNaN(value)) return false;
		}

		switch (operator) {
			case '<':
				return value < number;
			case '>':
				return value > number;
			case '<=':
				return value <= number;
			case '>=':
				return value >= number;
			case '=':
				return value === number;
			case '!=':
				return value !== number;
			default:
				return false;
		}
	});

	return includes && excludes && comparisonCheck;
}

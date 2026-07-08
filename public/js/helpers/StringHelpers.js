export function capitalize(string) {
	if (!string) return '';
	
	// ref unwrap if we receive such
	if (Vue.isRef(string)) {
		return Vue.computed(() => {
			const val = Vue.unref(string);
			return (val && typeof val === 'string') ? val.charAt(0).toUpperCase() + val.slice(1) : '';
		});
	}

	// just a plain string, return a plain string
	if (typeof string === 'string') {
		return string.charAt(0).toUpperCase() + string.slice(1);
	}

	return '';
	
}

export function convertCase(string, from, to) {
	const possibleCases = ["kebab", "snake", "camel"];
	if (
		from === to ||
		!possibleCases.includes(from) ||
		!possibleCases.includes(to)
	)
		return string;

	const individualWords =
		from === "kebab"
			? string.split("-")
			: from === "snake"
				? string.split("_")
				: splitWordsInCamelCase(string);

	const formattedIndividualWords = ["snake", "kebab"].includes(to)
		? individualWords.map((word) => word.toLowerCase())
		: individualWords.map((word, index) => {
				if (!index) return word.toLowerCase();
				return word.slice(0, 1).toUpperCase() + word.slice(1).toLowerCase();
			});

	const joinCharacter = to === "snake" ? "_" : to === "kebab" ? "-" : "";
	return formattedIndividualWords.join(joinCharacter);
}

function splitWordsInCamelCase(string) {
	const lastUpperCaseLetterIndex = string
		.split("")
		.findLastIndex((character) => {
			return character.toUpperCase() === character;
		});

	if (lastUpperCaseLetterIndex < 1) {
		return [string];
	}

	let splitWords = splitWordsInCamelCase(
		string.slice(0, lastUpperCaseLetterIndex),
	);
	splitWords.push(string.slice(lastUpperCaseLetterIndex));
	return splitWords;
}

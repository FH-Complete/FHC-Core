/**
 * Validates the thesis title on the frontend
 * @param {string} title - The raw input from the title field
 * @returns {object} Validation result containing status and cleaned title
 */
export function validateThesisTitle(title) {
	if (!title) {
		return { isValid: false, error: 'empty' };
	}

	// Replicate strip_tags / trim
	const cleanedTitle = title.replace(/<\/?[^>]+(>|$)/g, "").trim();

	if (cleanedTitle === '') {
		return { isValid: false, error: 'empty' };
	}

	// Replicate the emoji/pictograph rejection
	const emojiRegex = /\p{Extended_Pictographic}/u;
	if (emojiRegex.test(cleanedTitle)) {
		return { isValid: false, error: 'invalid_characters' };
	}

	return { isValid: true, cleanedTitle: cleanedTitle };
}
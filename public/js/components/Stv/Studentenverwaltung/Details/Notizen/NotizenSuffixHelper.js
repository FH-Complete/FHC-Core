import ApiNotizPerson from '../../../../../api/factory/notiz/person.js';

export async function getSuffix(modelValue) {
	const response = await this.$api
		.call(ApiNotizPerson.getCountNotes(modelValue.person_id));
	const suffix = ' (' + response.data + ')';
	return suffix;
}
import ApiNotizPerson from '../../../../../api/factory/notiz/person.js';

export async function getSuffix(api, modelValue) {
	const response = await api
		.call(ApiNotizPerson.getCountNotes(modelValue.person_id));
	const suffix = '(' + response.data + ')';
	return suffix;
}
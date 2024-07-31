export default {
	updatePersonUnrulyStatus(person_id, unrulyParam) {

		try {
			const payload = {person_id, unruly: unrulyParam}
			const url = '/api/frontend/v1/unrulyperson/UnrulyPerson/updatePersonUnrulyStatus';
			return this.$fhcApi.post(url, payload, null);
		} catch (error) {
			throw error;
		}

	}
}
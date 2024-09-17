export default {
	updatePersonUnrulyStatus(person_id, unrulyParam) {

		try {
			const payload = {person_id, unruly: unrulyParam}
			const url = '/api/frontend/v1/checkperson/CheckPerson/updatePersonUnrulyStatus';
			return this.$fhcApi.post(url, payload, null);
		} catch (error) {
			throw error;
		}

	},
	filterPerson(payload, base = ''){

		try {
			const url = base + '/api/frontend/v1/checkperson/CheckPerson/filterPerson';
			return axios.post(url, payload)
			// return this.$fhcApi.post(url, payload, null);
		} catch (error) {
			throw error;
		}

	}
}
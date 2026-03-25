export default {
	search(query) {
		return {
			method: 'get',
			url: 'api/frontend/v1/tempus/coursepicker/search',
			params: {  query }
		};
	}
};

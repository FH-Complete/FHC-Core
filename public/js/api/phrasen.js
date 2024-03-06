export default {
	loadCategory(category) {
		return this.$fhcApi.get('/api/frontend/v1/phrasen/loadModule/' + category);
	}
};

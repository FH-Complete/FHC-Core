export default {
	search(searchsettings) {
		const url = '/api/frontend/v1/searchbar/search';
		return this.$fhcApi.post(url, searchsettings);
	},
	searchdummy(searchsettings) {
		const url = 'public/js/apps/api/dummyapi.php/Search';
		return this.$fhcApi.post(url, searchsettings);
	}
};
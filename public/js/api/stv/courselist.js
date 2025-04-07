export default {
	getCourselist(url, config, params) {
		console.log("her");
		return this.$fhcApi.get('api/frontend/v1/stv/Lvtermine/getLvsStudent/' + params.id);
	},
}
export default {
	getGruppen(url, config, params) {
		return this.$fhcApi.get('api/frontend/v1/stv/Gruppen/getGruppen/' + params.id);
	},
	deleteGroup(params) {
		return this.$fhcApi.post('api/frontend/v1/stv/Gruppen/deleteGruppe/', params);
	}
}
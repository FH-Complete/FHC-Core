export default {
	getStg() {
		return this.$fhcApi.get('api/frontend/v1/stv/filter/getStg');
	},
	setStg(studiengang_kz) {
		return this.$fhcApi.post('api/frontend/v1/stv/filter/setStg', {
			studiengang_kz
		});
	}
};
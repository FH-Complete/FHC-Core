export default {
	getVorlagen() {
		return this.$fhcApi.get('api/frontend/v1/vorlagen/vorlagen/getVorlagen/');
	},
	getVorlagenByLoggedInUser() {
		return this.$fhcApi.get('api/frontend/v1/vorlagen/vorlagen/getVorlagenByLoggedInUser/');
	}
}
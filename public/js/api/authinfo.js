
export default {
	getAuthUID() {
		return this.$fhcApi.get(
			'/api/frontend/v1/AuthInfo/getAuthUID',
			{ }
		);
	},
	
};
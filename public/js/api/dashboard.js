export default {
	async getViewData() {
		const url = `/api/frontend/v1/Cis4FhcApi/getViewData`;
		return this.$fhcApi.get(url, null, null)
	},
}
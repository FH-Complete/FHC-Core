export default {
	getMenu: function () {
		return this.$fhcApi.get(
			"/api/frontend/v1/CisMenu/getMenu",
			{}
		);
	}

}
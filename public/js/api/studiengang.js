export default {
	studiengangInformation: function () {
		return this.$fhcApi.get(
			"/api/frontend/v1/Studgang/getStudiengangInfo",
			{}
		);
	}

}
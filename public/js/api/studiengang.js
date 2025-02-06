export default {
	studiengangInformation: function () {
		return this.$fhcApi.get(
			"/api/frontend/v1/Studgang/getStudiengangInfo",
			{}
		);
	},
	getStudiengangByKz: function (studiengang_kz) {
		return this.$fhcApi.get(
			"/api/frontend/v1/organisation/StudiengangEP/getStudiengangByKz",
			{
				"studiengang_kz": studiengang_kz
			}
		);
	}
}
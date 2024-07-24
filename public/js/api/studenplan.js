
export default {
	getRoomInfo(ort_kurzbz, start_date, end_date) {
		return this.$fhcApi.get(
			'/api/frontend/v1/Stundenplan/roomInformation',
			{  ort_kurzbz, start_date, end_date}
		);
	},
	getStunden() {
		return this.$fhcApi.get(
			'/api/frontend/v1/Stundenplan/Stunden',
			{}
		);
	},
	getReservierungen(ort_kurzbz, start_date, end_date) {
		return this.$fhcApi.get(
			'/api/frontend/v1/Stundenplan/Reservierungen',
			{  ort_kurzbz, start_date, end_date}
		);
	},
};
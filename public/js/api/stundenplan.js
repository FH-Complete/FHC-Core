
export default {
	getRoomInfo(ort_kurzbz, start_date, end_date) {
		return this.$fhcApi.get(
			'/api/frontend/v1/Stundenplan/getRoomplan',
			{  ort_kurzbz, start_date, end_date}
		);
	},
	getStunden() {
		return this.$fhcApi.get(
			'/api/frontend/v1/Stundenplan/Stunden',
			{}
		);
	},
	getOrtReservierungen(ort_kurzbz, start_date, end_date) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Stundenplan/Reservierungen/${ort_kurzbz}`,
			{ start_date, end_date}
		);
	},
	getStundenplanReservierungen(start_date, end_date) {
		return this.$fhcApi.get(
			'/api/frontend/v1/Stundenplan/Reservierungen',
			{ start_date, end_date }
		);
	},
	getLehreinheitStudiensemester(lehreinheit_id) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Stundenplan/getLehreinheitStudiensemester/${lehreinheit_id}`,
			{}
		);
	},
	studiensemesterDateInterval(date) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Stundenplan/studiensemesterDateInterval/${date}`,
			{}
		);
	},
	StundenplanEvents(start_date, end_date, lv_id) {
		return this.$fhcApi.get(
			'/api/frontend/v1/Stundenplan/StundenplanEvents',
			{ start_date, end_date, lv_id }
		);
	},
	
	
};
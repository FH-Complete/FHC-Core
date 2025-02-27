
export default {
	getRoomInfo(ort_kurzbz, start_date, end_date) {
		return this.$fhcApi.get(
			'/api/frontend/v1/Stundenplan/getRoomplan',
			{  ort_kurzbz, start_date, end_date}
		);
	},
	getStundenplan(start_date, end_date, lv_id) {
		return this.$fhcApi.get(
			'/api/frontend/v1/Stundenplan/getStundenplan',
			{ start_date, end_date, lv_id }
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
	getMoodleEventsByUserid(timestart, timeend) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Stundenplan/fetchMoodleEvents`,
			{
				timestart: timestart,
				timeend: timeend,
			}
		);
	},
	
};
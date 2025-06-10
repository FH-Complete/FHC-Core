
export default {
	getRoomInfo(ort_kurzbz, start_date, end_date) {
		return this.$fhcApi.get(
			'/api/frontend/v1/LvPlan/getRoomplan',
			{  ort_kurzbz, start_date, end_date}
		);
	},
	getStunden() {
		return this.$fhcApi.get(
			'/api/frontend/v1/LvPlan/Stunden',
			{}
		);
	},
	getOrtReservierungen(ort_kurzbz, start_date, end_date) {
		return this.$fhcApi.get(
			`/api/frontend/v1/LvPlan/Reservierungen/${ort_kurzbz}`,
			{ start_date, end_date}
		);
	},
	getLvPlanReservierungen(start_date, end_date) {
		return this.$fhcApi.get(
			'/api/frontend/v1/LvPlan/Reservierungen',
			{ start_date, end_date }
		);
	},
	getLehreinheitStudiensemester(lehreinheit_id) {
		return this.$fhcApi.get(
			`/api/frontend/v1/LvPlan/getLehreinheitStudiensemester/${lehreinheit_id}`,
			{}
		);
	},
	studiensemesterDateInterval(date) {
		return this.$fhcApi.get(
			`/api/frontend/v1/LvPlan/studiensemesterDateInterval/${date}`,
			{}
		);
	},
	LvPlanEvents(start_date, end_date, lv_id) {
		return this.$fhcApi.get(
			'/api/frontend/v1/LvPlan/LvPlanEvents',
			{ start_date, end_date, lv_id }
		);
	},
	
	
};
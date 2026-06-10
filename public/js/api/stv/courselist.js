export default {
	getCourselist(url, config, params) {
		//corresponding logic controller Stundenplan.php
		return this.$fhcApi.get('api/frontend/v1/stv/LvTermine/getStundenplan/'
			+ params.student_uid + '/'
			+ params.start_date + '/'
			+ params.end_date + '/'
			+ params.group_consecutiveHours + '/'
			+ params.dbStundenplanTable
		);
	},
	getStudiensemester(){
		return this.$fhcApi.get('api/frontend/v1/stv/LvTermine/getStudiensemester/');
	},
}
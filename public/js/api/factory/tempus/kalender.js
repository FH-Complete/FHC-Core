
export default {
	getPlan(filter, start_date, end_date)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Kalender/getPlan',
			params: { ...filter, start_date, end_date }
		};
	},
	getPlanLecturer(start_date, end_date)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Kalender/getPlanLecturer',
			params: { start_date, end_date }
		};
	},
	getPlanStudent(start_date, end_date)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Kalender/getPlanStudent',
			params: { start_date, end_date }
		};
	},

	syncToLecturer(kalender_id)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Kalender/syncToLecturer',
			params: { kalender_id }
		};
	},
	syncToStudent(kalender_id)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Kalender/syncToStudent',
			params: { kalender_id }
		};
	},
	sync()
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Kalender/sync',
		};
	},
	getLektorZeitsperren(emp, start_date, end_date) {
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Kalender/getZeitsperren',
			params: { emp, start_date, end_date }
		};
	},
	getLektorZeitwuensche(emp, start_date, end_date) {
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Kalender/getZeitwuensche',
			params: { emp, start_date, end_date }
		};
	},
	getStunden() {
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Kalender/getStunden',
		};
	},
	updateKalenderEvent(kalender_id, updatedInfos) {
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Kalender/updateKalenderEvent',
			params: { kalender_id, updatedInfos}
		};
	},
	addKalenderEvent(lehreinheit_id, ort_kurzbz, start_date, end_date) {
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Kalender/addKalenderEvent',
			params: { lehreinheit_id, ort_kurzbz, start_date, end_date}
		};
	},
	getRaumvorschlag(start_date, end_date, lehreinheit_id) {
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Kalender/getRaumvorschlag',
			params: { start_date, end_date, lehreinheit_id}
		};
	},

	getHistory(kalender_id) {
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Kalender/getHistory',
			params: { kalender_id }
		};
	},
	deleteEntry(kalender_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Kalender/deleteEntry',
			params: { kalender_id }
		};
	},

};

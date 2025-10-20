
export default {
	getRoomplan(ort_kurzbz, start_date, end_date) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Kalender/getRoomplan',
			params: { ort_kurzbz, start_date, end_date }
		};
	},
	getStundenplan(start_date, end_date) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Kalender/getStundenplan',
			params: { start_date, end_date}
		};
	},
	getStunden() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Kalender/Stunden',
			params: {}
		};
	},
	getOrtReservierungen(ort_kurzbz, start_date, end_date) {
		return {
			method: 'get',
			url: `/api/frontend/v1/Kalender/Reservierungen/${ort_kurzbz}`,
			params: { start_date, end_date}
		};
	},
	getStundenplanReservierungen(start_date, end_date) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Kalender/Reservierungen',
			params: { start_date, end_date }
		};
	},
	getLehreinheitStudiensemester(lehreinheit_id) {
		return {
			method: 'get',
			url: `/api/frontend/v1/Kalender/getLehreinheitStudiensemester/${lehreinheit_id}`,
			params: {}
		};
	},
	updateKalenderEvent(kalender_id, ort_kurzbz, start_date, end_date) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Kalender/updateKalenderEvent',
			params: { kalender_id, ort_kurzbz, start_date, end_date}
		};
	},
	addKalenderEvent(lehreinheit_id, ort_kurzbz, start_date, end_date) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Kalender/addKalenderEvent',
			params: { lehreinheit_id, ort_kurzbz, start_date, end_date}
		};
	},

};

export default {
	getReservableMap(ort_kurzbz, start_date, end_date) {
		return {
			method: 'post',
			url: `/api/frontend/v1/calendar/RoomPlan/getReservableMap/${ort_kurzbz}`,
			params: { start_date, end_date }
		};
	},

	getRoomCreationInfo() {
		return {
			method: 'get',
			url: '/api/frontend/v1/calendar/RoomPlan/getRoomCreationInfo'
		};
	},
	getGruppen(query) {
		return {
			method: 'get',
			url: `/api/frontend/v1/calendar/RoomPlan/getGruppen?query=${encodeURIComponent(query)}`
		};
	},
	getLektor(query) {
		return {
			method: 'get',
			url: `/api/frontend/v1/calendar/RoomPlan/getLektor?query=${encodeURIComponent(query)}`
		};
	},
	addRoomReservation(formData) {
		return {
			method: 'post',
			url: '/api/frontend/v1/calendar/RoomPlan/addRoomReservation',
			params: formData
		};
	},
	deleteRoomReservation(reservierung_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/calendar/RoomPlan/deleteRoomReservation',
			params: {
				reservierung_id: reservierung_id
			}
		};
	}
}
export default {

	getLehrfunktionen()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/lektor/getLehrfunktionen/'
		};
	},


	getLektoren()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/lektor/getLektoren/'
		};
	},


	getByLehreinheit(lehreinheit_id)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/lektor/getLektorenByLE/' + encodeURIComponent(lehreinheit_id)
		};
	},


	add(newData)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/lektor/add/',
			params: newData
		};
	},

	update(updatedData)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/lektor/update/',
			params: updatedData
		};
	},


	deletePerson(deleteData)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/lektor/deletePerson/',
			params: deleteData
		};
	},
	deleteFromLVPlan(deleteData)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/lektor/deleteLVPlan/',
			params: deleteData
		};
	},
	getLektorDaten(lehreinheit_id, mitarbeiter_uid)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/lektor/getLektorDaten/' + encodeURIComponent(lehreinheit_id) + '/' + encodeURIComponent(mitarbeiter_uid)
		};
	},






}

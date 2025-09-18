export default {

	getByLeEmp(lehreinheit_id, mitarbeiter_uid)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/lektor/getLektorDaten/' + encodeURIComponent(lehreinheit_id) + '/' + encodeURIComponent(mitarbeiter_uid),
		};
	},

	cancelByLeEmp(needUpdate)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/lektor/cancelVertrag/',
			params: needUpdate
		};
	},



}

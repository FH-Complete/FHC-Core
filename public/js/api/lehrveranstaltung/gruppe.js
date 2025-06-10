export default {

	delete(deleteData)
	{

		return {
			method: 'post',
			url: '/api/frontend/v1/lv/gruppe/delete/',
			params: deleteData
		};
	},

	add(newData)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/gruppe/add/',
			params: newData
		};
	},
	getByLehreinheit(lehreinheit_id)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/gruppe/getByLehreinheit/' + encodeURIComponent(lehreinheit_id)
		};
	},

	deleteFromLVPlan(deleteData)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/gruppe/deleteLVPlan/',
			params: deleteData
		};
	},


/*------------- details -------- */
	getAll()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/gruppe/getAll/'
		};
	},

	getBenutzer()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/gruppe/getBenutzer/'
		};
	}

}

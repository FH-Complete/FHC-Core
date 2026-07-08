export default {
	add(newData)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/DirektGruppe/add/',
			params: newData
		};
	},
	delete(deleteData)
	{

		return {
			method: 'post',
			url: '/api/frontend/v1/lv/DirektGruppe/delete/',
			params: deleteData
		};
	},
	getByLehreinheit(lehreinheit_id)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/DirektGruppe/getByLehreinheit/' + encodeURIComponent(lehreinheit_id)
		};
	},
}

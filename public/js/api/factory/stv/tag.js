export default {

	getTag(data)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/Tags/getTag',
			params: data
		};
	},

	getTags(data)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/Tags/getTags'
		};
	},

	addTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/Tags/addTag',
			params: data
		};
	},

	updateTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/Tags/updateTag',
			params: data
		};
	},
	doneTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/Tags/doneTag',
			params: data
		};
	},

	deleteTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/Tags/deleteTag',
			params: data
		};
	},

	//TODO check if necessary to expand to other idTypes
	getAllTagsPrestudent(prestudent_id){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/Tags/getAllTags',
			params: prestudent_id
		};
	},

	getSemDates(studiensemester_kurzbz){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/Tags/getSemDates',
			params: studiensemester_kurzbz
		};
	},

	getAllStartAndEndAutomatedTags(){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/Tags/getAllStartAndEndAutomatedTags',
		};
	},

	rebuildTagsforTypeId(data){
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/Tags/rebuildTagsForTypeId/',
			params: data
		};
	}
};
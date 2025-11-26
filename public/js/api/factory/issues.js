export default {

	getOpenIssuesByProperties(person_id, oe_kurzbz, fehlertyp_kurzbz, apps, behebung_parameter)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/Issues/getOpenIssuesByProperties',
			params: { person_id, oe_kurzbz, fehlertyp_kurzbz, apps, behebung_parameter }
		};
	}

}
export default {

	getOpenIssuesByProperties(person_id, oe_kurzbz, fehlertyp_kurzbz, apps, behebung_parameter, hauptzustaendig)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/issues/Issues/getOpenIssuesByProperties',
			params: { person_id, oe_kurzbz, fehlertyp_kurzbz, apps, behebung_parameter, hauptzustaendig }
		};
	}

}
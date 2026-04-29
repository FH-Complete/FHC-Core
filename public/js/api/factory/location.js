
export default {
	getLocationsByCompanyType(companyType) {
        return {
            method: 'get',
            url: '/api/frontend/v1/organisation/LocationApi/getLocationsByCompanyType',
            params: { companyType }
        };
    }
};

export default {
	getAllRooms(params) {
        console.log("API Call with params: ", params);
        return {
            method: 'get',
            url: 'api/frontend/v1/Ort/getAllRooms',
            params: {
                "filter[organizationalUnitShortCode]" : params.organizationalUnitShortCode,
                "filter[locationId]" : params.locationId,
                "filter[buildingComponent]" : params.buildingComponent,
                "filter[isForTrainingProgram]" : params.isForTrainingProgram,
                "filter[isReservationNeeded]" : params.isReservationNeeded,
                "filter[isActive]" : params.isActive,
            }
        }
    },
};
export default {

	getAbschlusspruefung (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/abschlusspruefung/getAbschlusspruefung/' + params.id);
	},
	addNewAbschlusspruefung(data) {
		return this.$fhcApi.post('api/frontend/v1/stv/abschlusspruefung/insertAbschlusspruefung/', data
		);
	},
	loadAbschlusspruefung(id){
		return this.$fhcApi.post('api/frontend/v1/stv/abschlusspruefung/loadAbschlusspruefung/', {id});
	},
	updateAbschlusspruefung(data) {
		return this.$fhcApi.post('api/frontend/v1/stv/abschlusspruefung/updateAbschlusspruefung/', data
		);
	},
	deleteAbschlusspruefung(id){
		return this.$fhcApi.post('api/frontend/v1/stv/abschlusspruefung/deleteAbschlusspruefung/', {id});
	},
	getTypenAbschlusspruefung(){
		return this.$fhcApi.get('api/frontend/v1/stv/abschlusspruefung/getTypenAbschlusspruefung/');
	},
	getTypenAntritte(){
		return this.$fhcApi.get('api/frontend/v1/stv/abschlusspruefung/getTypenAntritte/');
	},
	getBeurteilungen(){
		return this.$fhcApi.get('api/frontend/v1/stv/abschlusspruefung/getBeurteilungen/');
	},
	getAkadGrade(studiengang_kz){
		return this.$fhcApi.post('api/frontend/v1/stv/abschlusspruefung/getAkadGrade/', {studiengang_kz});
	},
	getTypStudiengang(studiengang_kz){
		return this.$fhcApi.post('api/frontend/v1/stv/abschlusspruefung/getTypStudiengang/', {studiengang_kz});
	},
	getMitarbeiter(searchString){
		return this.$fhcApi.get('api/frontend/v1/stv/abschlusspruefung/getMitarbeiter/' + searchString);
	},
	getPruefer(searchString){
		return this.$fhcApi.get('api/frontend/v1/stv/abschlusspruefung/getPruefer/' + searchString);
	},
	getNoten(){
		return this.$fhcApi.get('api/frontend/v1/stv/abschlusspruefung/getNoten/');
	},
	checkForExistingExams(uids) {
		return this.$fhcApi.post('api/frontend/v1/stv/abschlusspruefung/checkForExistingExams/', {uids}
		);
	}
}
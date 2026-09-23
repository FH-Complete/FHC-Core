export default {
	getAnrechnungen(url, config, params) {
		return this.$fhcApi.get('api/frontend/v1/stv/anrechnungen/getAnrechnungen/' + params.id);
	},
	getLehrveranstaltungen(prestudent_id){
		return this.$fhcApi.get('api/frontend/v1/stv/anrechnungen/getLehrveranstaltungen/' + prestudent_id);
	},
	getBegruendungen(){
		return this.$fhcApi.get('api/frontend/v1/stv/anrechnungen/getBegruendungen/');
	},
	getLvsKompatibel(lv_id){
		return this.$fhcApi.get('api/frontend/v1/stv/anrechnungen/getLvsKompatibel/' + lv_id);
	},
	getLektoren(studiengang_kz){
		return this.$fhcApi.get('api/frontend/v1/stv/anrechnungen/getLektoren/' + studiengang_kz);
	},
	addNewAnrechnung(form, data){
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/anrechnungen/insertAnrechnung/', data);
	},
	loadAnrechnung(anrechnung_id){
		return this.$fhcApi.get('api/frontend/v1/stv/anrechnungen/loadAnrechnung/' + anrechnung_id);
	},
	editAnrechnung(form, data){
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/anrechnungen/updateAnrechnung/', data);
	},
	deleteAnrechnung(anrechnung_id){
		return this.$fhcApi.post('api/frontend/v1/stv/anrechnungen/deleteAnrechnung/' + anrechnung_id);
	},
}
export default {
	getPruefungen(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/pruefung/getPruefungen/' + params.id);
	},
	loadPruefung(pruefung_id){
		return this.$fhcApi.get('api/frontend/v1/stv/pruefung/loadPruefung/' + pruefung_id);
	},
	getTypenPruefungen(){
		return this.$fhcApi.get('api/frontend/v1/stv/pruefung/getTypenPruefungen');
	},
	getAllLehreinheiten(data){
		return this.$fhcApi.post('api/frontend/v1/stv/pruefung/getAllLehreinheiten/', data)
	},
	getLvsByStudent(uid){
		return this.$fhcApi.get('api/frontend/v1/stv/pruefung/getLvsByStudent/' + uid)
	},
	getLvsandLesByStudent(uid){
		return this.$fhcApi.get('api/frontend/v1/stv/pruefung/getLvsandLesByStudent/' + uid);
	},
	getLvsAndMas(uid){
		return this.$fhcApi.get('api/frontend/v1/stv/pruefung/getLvsAndMas/' + uid)
	},
	getMitarbeiterLv(id){
		return this.$fhcApi.get('api/frontend/v1/stv/pruefung/getMitarbeiterLv/' + id)
	},
	getNoten(){
		return this.$fhcApi.get('api/frontend/v1/stv/pruefung/getNoten');
	},
	checkZeugnisnoteLv(data){
		return 	this.$fhcApi.post('api/frontend/v1/stv/pruefung/checkZeugnisnoteLv/', data)
	},
	addPruefung(form, data){
		return this.$fhcApi.post(form,'api/frontend/v1/stv/pruefung/insertPruefung/',  data);
	},
	updatePruefung(form, id, data){
		return this.$fhcApi.post(form,'api/frontend/v1/stv/pruefung/updatePruefung/' + id,  data);
	},
	deletePruefung(id){
		return this.$fhcApi.post('api/frontend/v1/stv/pruefung/deletePruefung/' + id)
	}
}
export default {
	//------------- address.js-----------
	getAdressen (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/kontakt/getAdressen/' + params.id);
	},
	addNewAddress(form, id, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/kontakt/addNewAddress/' + id,
			data
		);
	},
	loadAddress(address_id){
		return this.$fhcApi.post('api/frontend/v1/stv/kontakt/loadAddress/', {address_id});
	},
	updateAddress(form, address_id, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/kontakt/updateAddress/' + address_id,
			data
		);
	},
	deleteAddress(address_id) {
		return this.$fhcApi.post('api/frontend/v1/stv/kontakt/deleteAddress/', {address_id});
	},
	getPlaces(plz) {
		return this.$fhcApi.get('api/frontend/v1/stv/address/getPlaces/' + plz);
	},
	getFirmen(searchString) {
		return this.$fhcApi.get('api/frontend/v1/stv/kontakt/getFirmen/' + searchString);
	},
	getNations() {
		return this.$fhcApi.get('api/frontend/v1/stv/address/getNations/');
	},
	getAdressentypen() {
		return this.$fhcApi.get('api/frontend/v1/stv/kontakt/getAdressentypen/');
	},

	//------------- bankverbindung.js-----------
	getBankverbindung (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/kontakt/getBankverbindung/' + params.id);
	},
	addNewBankverbindung(form, id, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/kontakt/addNewBankverbindung/' + id,
			data
		);
	},
	loadBankverbindung(bankverbindung_id){
		return this.$fhcApi.post('api/frontend/v1/stv/kontakt/loadBankverbindung/', {bankverbindung_id});
	},
	updateBankverbindung(form, bankverbindung_id, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/kontakt/updateBankverbindung/' + bankverbindung_id,
			data
		);
	},
	deleteBankverbindung(bankverbindung_id) {
		return this.$fhcApi.post('api/frontend/v1/stv/kontakt/deleteBankverbindung/', {bankverbindung_id});
	},

	//------------- contact.js-----------
	getKontakte (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/kontakt/getKontakte/' + params.id);
	},
	addNewContact(form, id, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/kontakt/addNewContact/' + id,
			data
		);
	},
	loadContact(kontakt_id){
		return this.$fhcApi.post('api/frontend/v1/stv/kontakt/loadContact/', {kontakt_id});
	},
	updateContact(form, kontakt_id, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/kontakt/updateContact/' + kontakt_id,
			data
		);
	},
	deleteContact(kontakt_id) {
		return this.$fhcApi.post('api/frontend/v1/stv/kontakt/deleteContact/', {kontakt_id});
	},
	getStandorteByFirma(searchString){
		return this.$fhcApi.get('api/frontend/v1/stv/kontakt/getStandorteByFirma/' + searchString);
	},
	getKontakttypen(){
		return this.$fhcApi.get('api/frontend/v1/stv/kontakt/getKontakttypen/');
	}
};
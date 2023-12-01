import {CoreRESTClient} from '../../../../RESTClient.js';
import AddressList from "./Kontakt/Address.js";
import ContactList from "./Kontakt/Contact.js";
import BankaccountList from "./Kontakt/Bankaccount.js";

import PvToast from "../../../../../../index.ci.php/public/js/components/primevue/toast/toast.esm.min.js";
import PvAutoComplete from "../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
export default {
	components: {
		AddressList,
		ContactList,
		BankaccountList,
		PvToast,
		PvAutoComplete
	},
	props: {
		modelValue: Object
	},
	data() {
		return {
			adressen: [],
			kontakte: [],
			bankverbindungen: []
		}
	},
	created(){
		CoreRESTClient
			.get('components/stv/Kontakt/getAdressen/' + this.modelValue.person_id)
			.then(result => {
				this.adressen = result.data;
			})
			.catch(err => {
				console.error(err.response.data || err.message);
			});
		/*		CoreRESTClient
					.get('components/stv/Kontakt/getKontakte/' + this.modelValue.person_id)
					.then(result => {
						this.kontakte = result.data;
					})
					.catch(err => {
						console.error(err.response.data || err.message);
					});
				CoreRESTClient
					.get('components/stv/Kontakt/getBankverbindung/' + this.modelValue.person_id)
					.then(result => {
						this.bankverbindungen = result.data;
					})
					.catch(err => {
						console.error(err.response.data || err.message);
					});*/
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
		<fieldset class="overflow-hidden">
		

		
			<legend>Adressen</legend>		
<!--			{{this.adressen}}-->

				<!--props notwendig, um auf Funktion in child zuzugreifen-->
<!--				<button type="button" class="btn btn btn-outline-warning" @click="actionNewAdress()">new Adress</button>
				<button type="button" class="btn btn btn-outline-warning" @click="actionEditAdress(111444)">edit 111444</button>-->
				
				<address-list ref="adressList" :uid="modelValue.person_id"></address-list>
		</fieldset>
		<br>
		<fieldset class="overflow-hidden">
			<legend>Kontakt</legend>
	<!--		{{this.kontakte}}-->
			<contact-list ref="contactList" :uid="modelValue.person_id"></contact-list>
		</fieldset>
		<br>
		<fieldset class="overflow-hidden">
			<legend>Bankverbindungen</legend>
<!--			{{this.bankverbindungen}}-->
			<bankaccount-list ref="bankaccountList" :uid="modelValue.person_id"></bankaccount-list>
		</fieldset>
	</div>`
};

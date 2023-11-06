import {CoreRESTClient} from '../../../../RESTClient.js';
import AddressList from "./Kontakt/Address.js";
import ContactList from "./Kontakt/Contact.js";
import BankaccountList from "./Kontakt/Bankaccount.js";
import PvToast from "../../../../../../index.ci.php/public/js/components/primevue/toast/toast.esm.min.js";
export default {
	components: {
		AddressList,
		ContactList,
		BankaccountList,
		PvToast
	},
	props: {
		student: Object
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
			.get('components/stv/Student/getAdressen/' + this.student.person_id)
			.then(result => {
				this.adressen = result.data;
			})
			.catch(err => {
				console.error(err.response.data || err.message);
			});
		/*		CoreRESTClient
					.get('components/stv/Student/getKontakte/' + this.student.person_id)
					.then(result => {
						this.kontakte = result.data;
					})
					.catch(err => {
						console.error(err.response.data || err.message);
					});
				CoreRESTClient
					.get('components/stv/Student/getBankverbindung/' + this.student.person_id)
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
		<!--	{{this.adressen}}-->
				<address-list ref="adressList" :uid="student.person_id"></address-list>
		</fieldset>
		<fieldset class="overflow-hidden">
			<legend>Kontakt</legend>
	<!--		{{this.kontakte}}-->
			<contact-list ref="contactList" :uid="student.person_id"></contact-list>
		</fieldset>
		<fieldset class="overflow-hidden">
			<legend>Bankverbindungen</legend>
<!--			{{this.bankverbindungen}}-->
			<bankaccount-list ref="bankaccountList" :uid="student.person_id"></bankaccount-list>
		</fieldset>
	</div>`
};

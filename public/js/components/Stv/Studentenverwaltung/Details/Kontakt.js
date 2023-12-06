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
	template: `
	<div class="stv-details-details h-100 pb-3">
		<fieldset class="overflow-hidden">
			<legend>Adressen</legend>
			<address-list ref="adressList" :uid="modelValue.person_id"></address-list>
		</fieldset>
		<br>
		<fieldset class="overflow-hidden">
			<legend>Kontakt</legend>
			<contact-list ref="contactList" :uid="modelValue.person_id"></contact-list>
		</fieldset>
		<br>
		<fieldset class="overflow-hidden">
			<legend>Bankverbindungen</legend>
			<bankaccount-list ref="bankaccountList" :uid="modelValue.person_id"></bankaccount-list>
		</fieldset>
	</div>`
};

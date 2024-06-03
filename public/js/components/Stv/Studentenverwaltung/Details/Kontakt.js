import AddressList from "./Kontakt/Address.js";
import ContactList from "./Kontakt/Contact.js";
import BankaccountList from "./Kontakt/Bankaccount.js";

export default {
	components: {
		AddressList,
		ContactList,
		BankaccountList,
	},
	props: {
		modelValue: Object,
		config: Object
	},
	data() {
		return {
			adressen: [],
			kontakte: [],
			bankverbindungen: []
		}
	},
	template: `
	<div class="stv-details-kontakt h-100 pb-3">
		<fieldset class="overflow-hidden">
			<legend>{{this.$p.t('person', 'adressen')}}</legend>
			<address-list ref="adressList" :uid="modelValue.person_id"></address-list>
		</fieldset>
		<br>
		<fieldset class="overflow-hidden">
			<legend>{{this.$p.t('global', 'kontakt')}}</legend>
			<contact-list ref="contactList" :uid="modelValue.person_id"></contact-list>
		</fieldset>
		<br>
		<fieldset v-if="config.showBankaccount" class="overflow-hidden">
			<legend>{{this.$p.t('person', 'bankverbindungen')}}</legend>
			<bankaccount-list ref="bankaccountList" :uid="modelValue.person_id"></bankaccount-list>
		</fieldset>
	</div>`
};

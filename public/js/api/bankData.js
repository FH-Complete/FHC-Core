export default {
	getBankData() {
		return this.$fhcApi.get('api/frontend/v1/Bank/getBankData');
	},
	postBankData(name, bic, iban) {
		return this.$fhcApi.post(
			'api/frontend/v1/Bank/postBankData', {
				name: name,
				bic: bic,
				iban: iban
			});
	}
};


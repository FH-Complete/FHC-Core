import person from "./vertraege/person.js";

export default {
	person,
	configPrintDocument() {
		return this.$fhcApi.get('api/frontend/v1/vertraege/config/printDocument');
	}
}
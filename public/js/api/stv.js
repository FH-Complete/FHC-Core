import verband from './stv/verband.js';
import filter from './stv/filter.js';
import konto from './stv/konto.js';

export default {
	verband,
	filter,
	konto,
	configStudent() {
		return this.$fhcApi.get('api/frontend/v1/stv/config/student');
	},
	configStudents() {
		return this.$fhcApi.get('api/frontend/v1/stv/config/students');
	}
};
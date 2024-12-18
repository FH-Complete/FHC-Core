import verband from './stv/verband.js';
import students from './stv/students.js';
import filter from './stv/filter.js';
import konto from './stv/konto.js';
import mobility from './stv/mobility.js';

export default {
	verband,
	students,
	filter,
	konto,
	mobility,
	configStudent() {
		return this.$fhcApi.get('api/frontend/v1/stv/config/student');
	},
	configStudents() {
		return this.$fhcApi.get('api/frontend/v1/stv/config/students');
	}
};
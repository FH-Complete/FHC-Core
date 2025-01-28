import verband from './stv/verband.js';
import students from './stv/students.js';
import filter from './stv/filter.js';
import konto from './stv/konto.js';
import kontakt from './stv/kontakt.js';
import prestudent from './stv/prestudent.js';
import status from './stv/status.js';
import details from './stv/details.js';
import exam from './stv/exam.js';
import abschlusspruefung from './stv/abschlusspruefung.js';
import grades from './stv/grades.js';

export default {
	verband,
	students,
	filter,
	konto,
	kontakt,
	prestudent,
	status,
	details,
	exam,
	abschlusspruefung,
	grades,
	configStudent() {
		return this.$fhcApi.get('api/frontend/v1/stv/config/student');
	},
	configStudents() {
		return this.$fhcApi.get('api/frontend/v1/stv/config/students');
	}
};

import verband from './stv/verband.js';
import students from './stv/students.js';
import filter from './stv/filter.js';
import konto from './stv/konto.js';
import group from './stv/group.js';
import kontakt from './stv/kontakt.js';
import prestudent from './stv/prestudent.js';
import status from './stv/status.js';
import details from './stv/details.js';
import exam from './stv/exam.js';
import abschlusspruefung from './stv/abschlusspruefung.js';
import grades from './stv/grades.js';
import mobility from './stv/mobility.js';
import archiv from './stv/archiv.js';
import documents from './stv/documents.js';
import exemptions from './stv/exemptions.js';
import jointstudies from "./stv/jointstudies.js";

export default {
	verband,
	students,
	filter,
	konto,
	group,
	kontakt,
	prestudent,
	status,
	details,
	exam,
	abschlusspruefung,
	grades,
	mobility,
	archiv,
	documents,
	exemptions,
	jointstudies,
	configStudent() {
		return this.$fhcApi.get('api/frontend/v1/stv/config/student');
	},
	configStudents() {
		return this.$fhcApi.get('api/frontend/v1/stv/config/students');
	}
};

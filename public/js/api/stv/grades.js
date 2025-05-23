export default {
	list() {
		return this.$fhcApi.get('api/frontend/v1/stv/grades/list');
	},
	getCertificate(prestudent_id, all) {
		all = all ? '/all' : '';
		return this.$fhcApi.get('api/frontend/v1/stv/grades/getCertificate/' + prestudent_id + all);
	},
	getTeacherProposal(prestudent_id, all) {
		all = all ? '/all' : '';
		return this.$fhcApi.get('api/frontend/v1/stv/grades/getTeacherProposal/' + prestudent_id + all);
	},
	getRepeaterGrades(prestudent_id, all) {
		all = all ? '/all' : '';
		return this.$fhcApi.get('api/frontend/v1/stv/grades/getRepeaterGrades/' + prestudent_id + all);
	},
	updateCertificate({lehrveranstaltung_id, student_uid, studiensemester_kurzbz, note, lehrveranstaltung_bezeichnung}) {
		return this.$fhcApi.post(
			'api/frontend/v1/stv/grades/updateCertificate',
			{
				lehrveranstaltung_id,
				student_uid,
				studiensemester_kurzbz,
				note
			},
			{
				errorHeader: lehrveranstaltung_bezeichnung
			}
		);
	},
	deleteCertificate({lehrveranstaltung_id, student_uid, studiensemester_kurzbz, lehrveranstaltung_bezeichnung}) {
		return this.$fhcApi.post(
			'api/frontend/v1/stv/grades/deleteCertificate',
			{
				lehrveranstaltung_id,
				student_uid,
				studiensemester_kurzbz
			},
			{
				errorHeader: lehrveranstaltung_bezeichnung
			}
		);
	},
	copyTeacherProposalToCertificate({lehrveranstaltung_id, student_uid, studiensemester_kurzbz, lehrveranstaltung_bezeichnung}) {
		return this.$fhcApi.post(
			'api/frontend/v1/stv/grades/copyTeacherProposalToCertificate',
			{
				lehrveranstaltung_id,
				student_uid,
				studiensemester_kurzbz
			},
			{
				errorHeader: lehrveranstaltung_bezeichnung
			}
		);
	},
	copyRepeaterGradeToCertificate({studierendenantrag_lehrveranstaltung_id, lv_bezeichnung}) {
		return this.$fhcApi.post(
			'api/frontend/v1/stv/grades/copyRepeaterGradeToCertificate',
			{
				studierendenantrag_lehrveranstaltung_id
			},
			{
				errorHeader: lv_bezeichnung
			}
		);
	},
	getGradeFromPoints(points, lehrveranstaltung_id, manualErrorHandling) {
		const config = manualErrorHandling ? {errorHandling: false} : {};
		return this.$fhcApi.post('api/frontend/v1/stv/grades/getGradeFromPoints', {points, lehrveranstaltung_id}, config);
	}
}
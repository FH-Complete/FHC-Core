export default {
	list() {
		return this.$fhcApi.get('api/frontend/v1/stv/grades/list');
	},
	getCertificate(prestudent_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/grades/getCertificate/' + encodeURIComponent(prestudent_id);
		if (!!studiensemester_kurzbz) {
			url = url + '/' + encodeURIComponent(studiensemester_kurzbz);
		}
		return this.$fhcApi.get(url);
	},
	getTeacherProposal(prestudent_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/grades/getTeacherProposal/' + encodeURIComponent(prestudent_id);
		if (!!studiensemester_kurzbz) {
			url = url + '/' + encodeURIComponent(studiensemester_kurzbz);
		}
		return this.$fhcApi.get(url);
	},
	getRepeaterGrades(prestudent_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/grades/getRepeaterGrades/' + encodeURIComponent(prestudent_id);
		if (!!studiensemester_kurzbz) {
			url = url + '/' + encodeURIComponent(studiensemester_kurzbz);
		}
		return this.$fhcApi.get(url);
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
	getGradeFromPoints(points, lehrveranstaltung_id, studiensemester_kurzbz, manualErrorHandling) {
		const config = manualErrorHandling ? {errorHandling: false} : {};
		return this.$fhcApi.post('api/frontend/v1/stv/grades/getGradeFromPoints',
			{
				"points": points,
				"lehrveranstaltung_id": lehrveranstaltung_id,
				"studiensemester_kurzbz": studiensemester_kurzbz
			},
			config
			);
	}
}
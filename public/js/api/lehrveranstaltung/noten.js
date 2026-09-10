import Grades from '../factory/stv/grades.js';

export default {
	...Grades,
	getCertificate(lv_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/lv/noten/getCertificate/' + encodeURIComponent(lv_id);
		if (!!studiensemester_kurzbz) {
			url = url + '/' + encodeURIComponent(studiensemester_kurzbz);
		}
		return {
			method: 'get',
			url: url
		};
	},
	getTeacherProposal(lv_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/lv/noten/getTeacherProposal/' + encodeURIComponent(lv_id);
		if (!!studiensemester_kurzbz) {
			url = url + '/' + encodeURIComponent(studiensemester_kurzbz);
		}
		return {
			method: 'get',
			url: url
		};
	},
};
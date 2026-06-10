/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

export default {
	abmeldung: {
		getDetails(antrag_id, prestudent_id) {
			const url = '/api/frontend/v1/studstatus/abmeldung/'
				+ (antrag_id !== undefined ? 'getDetailsForAntrag/' + antrag_id : 'getDetailsForNewAntrag/' + prestudent_id);
			return this.$fhcApi.get(url);
		},
		create(stdsem, prestudent_id, grund) {
			return this.$fhcApi.post('/api/frontend/v1/studstatus/abmeldung/createAntrag', {
				studiensemester: stdsem,
				prestudent_id,
				grund
			}, {
				errorHandling: 'strict'
			});
		},
		cancel(antrag_id) {
			if (!Array.isArray(antrag_id))
				return this.$fhcApi.post(
					'/api/frontend/v1/studstatus/abmeldung/cancelAntrag',
					{ antrag_id }
				);
			return Promise.allSettled(antrag_id.map(antrag => this.$fhcApi.post(
				'/api/frontend/v1/studstatus/abmeldung/cancelAntrag',
				{ antrag_id: antrag.studierendenantrag_id },
				{ errorHeader: '#' + antrag.studierendenantrag_id }
			)));
		}
	},
	unterbrechung: {
		getDetails(antrag_id, prestudent_id) {
			const url = '/api/frontend/v1/studstatus/unterbrechung/'
				+ (antrag_id !== undefined ? 'getDetailsForAntrag/' + antrag_id : 'getDetailsForNewAntrag/' + prestudent_id);
			return this.$fhcApi.get(url);
		},
		create(studiensemester, prestudent_id, grund, datum_wiedereinstieg, attachment) {
			return this.$fhcApi.post('/api/frontend/v1/studstatus/unterbrechung/createAntrag', {
				studiensemester,
				prestudent_id,
				grund,
				datum_wiedereinstieg,
				attachment
			}, {
				errorHandling: 'strict'
			});
		},
		cancel(antrag_id) {
			return this.$fhcApi.post('/api/frontend/v1/studstatus/unterbrechung/cancelAntrag', {
				antrag_id
			}, {
				errorHandling: 'strict'
			});
		}
	},
	wiederholung: {
		getDetails(prestudent_id) {
			const url = '/api/frontend/v1/studstatus/wiederholung/getDetailsForNewAntrag/' + prestudent_id;
			return this.$fhcApi.get(url)
		},
		getLvs(antrag_id) {
			const url = '/api/frontend/v1/studstatus/wiederholung/getLvs/' + antrag_id;
			return this.$fhcApi.get(url)
		},
		create(prestudent_id, studiensemester) {
			return this.$fhcApi.post('/api/frontend/v1/studstatus/wiederholung/createAntrag', {
				prestudent_id,
				studiensemester
			}, {
				errorHandling: 'strict'
			});
		},
		cancel(prestudent_id, studiensemester) {
			return this.$fhcApi.post('/api/frontend/v1/studstatus/wiederholung/cancelAntrag', {
				prestudent_id,
				studiensemester
			}, {
				errorHandling: 'strict'
			});
		},
		saveLvs(forbiddenLvs, mandatoryLvs) {
			return this.$fhcApi.post('/api/frontend/v1/studstatus/wiederholung/saveLvs', {
				forbiddenLvs,
				mandatoryLvs
			});
		}
	},
	leitung: {
		getStgs() {
			return this.$fhcApi.get('/api/frontend/v1/studstatus/leitung/getActiveStgs');
		},
		getAntraege(url, config, params) {
			return this.$fhcApi
				.get('/api/frontend/v1/studstatus/leitung/getAntraege/' + url)
				.then(res => res.data); // Return data for tabulator
		},
		getHistory(antrag_id) {
			return this.$fhcApi.get('/api/frontend/v1/studstatus/leitung/getHistory/' + antrag_id)
		},
		getPrestudents(query, signal) {
			return this.$fhcApi.post(
				'/api/frontend/v1/studstatus/leitung/getPrestudents',
				{ query },
				{
                                    signal: signal,
                                    timeout: 30000
                                }
			);
		},
		approve(antrag) {
			if (!Array.isArray(antrag))
				return this.$fhcApi.post(
					'/api/frontend/v1/studstatus/leitung/approveAntrag',
					antrag
				);
			return Promise.allSettled(antrag.map(a => this.$fhcApi.post(
				'/api/frontend/v1/studstatus/leitung/approveAntrag',
				a,
				{ errorHeader: '#' + a.studierendenantrag_id }
			)));
		},
		reject(antrag) {
			if (!Array.isArray(antrag))
				return this.$fhcApi.post(
					'/api/frontend/v1/studstatus/leitung/rejectAntrag',
					antrag
				);
			return Promise.allSettled(antrag.map(a => this.$fhcApi.post(
				'/api/frontend/v1/studstatus/leitung/rejectAntrag',
				a,
				{ errorHeader: '#' + a.studierendenantrag_id }
			)));
		},
		reopen(antrag) {
			if (!Array.isArray(antrag))
				return this.$fhcApi.post(
					'/api/frontend/v1/studstatus/leitung/reopenAntrag',
					antrag
				);
			return Promise.allSettled(antrag.map(a => this.$fhcApi.post(
				'/api/frontend/v1/studstatus/leitung/reopenAntrag',
				a,
				{ errorHeader: '#' + a.studierendenantrag_id }
			)));
		},
		pause(antrag) {
			if (!Array.isArray(antrag))
				return this.$fhcApi.post(
					'/api/frontend/v1/studstatus/leitung/pauseAntrag',
					antrag
				);
			return Promise.allSettled(antrag.map(a => this.$fhcApi.post(
				'/api/frontend/v1/studstatus/leitung/pauseAntrag',
				a,
				{ errorHeader: '#' + a.studierendenantrag_id }
			)));
		},
		unpause(antrag) {
			if (!Array.isArray(antrag))
				return this.$fhcApi.post(
					'/api/frontend/v1/studstatus/leitung/unpauseAntrag',
					antrag
				);
			return Promise.allSettled(antrag.map(a => this.$fhcApi.post(
				'/api/frontend/v1/studstatus/leitung/unpauseAntrag',
				a,
				{ errorHeader: '#' + a.studierendenantrag_id }
			)));
		},
		object(antrag) {
			if (!Array.isArray(antrag))
				return this.$fhcApi.post(
					'/api/frontend/v1/studstatus/leitung/objectAntrag',
					antrag
				);
			return Promise.allSettled(antrag.map(a => this.$fhcApi.post(
				'/api/frontend/v1/studstatus/leitung/objectAntrag',
				a,
				{ errorHeader: '#' + a.studierendenantrag_id }
			)));
		},
		approveObjection(antrag) {
			if (!Array.isArray(antrag))
				return this.$fhcApi.post(
					'/api/frontend/v1/studstatus/leitung/approveObjection',
					antrag
				);
			return Promise.allSettled(antrag.map(a => this.$fhcApi.post(
				'/api/frontend/v1/studstatus/leitung/approveObjection',
				a,
				{ errorHeader: '#' + a.studierendenantrag_id }
			)));
		},
		denyObjection(antrag) {
			if (!Array.isArray(antrag))
				return this.$fhcApi.post(
					'/api/frontend/v1/studstatus/leitung/denyObjection',
					antrag
				);
			return Promise.allSettled(antrag.map(a => this.$fhcApi.post(
				'/api/frontend/v1/studstatus/leitung/denyObjection',
				a,
				{ errorHeader: '#' + a.studierendenantrag_id }
			)));
		}
	}
};
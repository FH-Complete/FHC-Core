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

import FhcStudentenverwaltung from "../components/Stv/Studentenverwaltung.js";
import FhcBase from "../plugins/FhcBase/FhcBase.js";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(),
	routes: [
		{
			name: 'index',
			path: `/${ciPath}/studentenverwaltung`,
			component: FhcStudentenverwaltung
		},
		{
			name: 'studiensemester',
			path: `/${ciPath}/studentenverwaltung/:studiensemester_kurzbz`,
			component: FhcStudentenverwaltung,
			props: (route) => {
				return {
					url_studiensemester_kurzbz: route.params.studiensemester_kurzbz
				};
			},
			beforeEnter: (to, from, next) => {
				const isSemester = /^[WS]S\d{4}$/.test(to.params.studiensemester_kurzbz);
				if (!isSemester) {
					return next({name: 'index'});
				}
				next();
			}
		},
		{
			name: 'studiengang',
			path: `/${ciPath}/studentenverwaltung/:studiensemester_kurzbz/:studiengang`,
			component: FhcStudentenverwaltung,
			props: (route) => {
				return {
					url_studiensemester_kurzbz: route.params.studiensemester_kurzbz,
					url_studiengang: route.params.studiengang,
				};
			},
			beforeEnter: (to, from, next) => {
				const isSemester = /^[WS]S\d{4}$/.test(to.params.studiensemester_kurzbz);
				if (!isSemester) {
					return next({name: 'index'});
				}
				const isStudiengang = /^[A-Z]{3}/.test(to.params.studiengang);
				if (!isStudiengang) {
					return next({
						name: 'studiensemester',
						params: {
							studiensemester_kurzbz: to.params.studiensemester_kurzbz
						}
					});
				}
				next();
			}
		},
		{
			path: `/${ciPath}/studentenverwaltung/:studiensemester_kurzbz/prestudent/:prestudent_id`,
			component: FhcStudentenverwaltung,
			props: (route) => {
				return {
					url_studiensemester_kurzbz: route.params.studiensemester_kurzbz,
					url_mode: 'prestudent',
					url_prestudent_id: route.params.prestudent_id
				};
			},
			beforeEnter: (to, from, next) => {
				const isSemester = /^[WS]S\d{4}$/.test(to.params.studiensemester_kurzbz);
				if (!isSemester) {
					return next({name: 'index'});
				}
				next();
			}
		},
		{
			path: `/${ciPath}/studentenverwaltung/:studiensemester_kurzbz/prestudent/:prestudent_id/:tab`,
			component: FhcStudentenverwaltung,
			props: (route) => {
				return {
					url_studiensemester_kurzbz: route.params.studiensemester_kurzbz,
					url_mode: 'prestudent',
					url_prestudent_id: route.params.prestudent_id,
					url_tab: route.params.tab
				};
			},
			beforeEnter: (to, from, next) => {
				const isSemester = /^[WS]S\d{4}$/.test(to.params.studiensemester_kurzbz);
				if (!isSemester) {
					return next({name: 'index'});
				}
				next();
			}
		},
		{
			path: `/${ciPath}/studentenverwaltung/:studiensemester_kurzbz/student/:id`,
			component: FhcStudentenverwaltung,
			props: (route) => {
				return {
					url_studiensemester_kurzbz: route.params.studiensemester_kurzbz,
					url_mode: 'student',
					url_student_id: route.params.id
				};
			},
			beforeEnter: (to, from, next) => {
				const isSemester = /^[WS]S\d{4}$/.test(to.params.studiensemester_kurzbz);
				if (!isSemester) {
					return next({name: 'index'});
				}
				next();
			}
		},
		{
			path: `/${ciPath}/studentenverwaltung/:studiensemester_kurzbz/person/:person_id`,
			component: FhcStudentenverwaltung,
			props: (route) => {
				return {
					url_studiensemester_kurzbz: route.params.studiensemester_kurzbz,
					url_mode: 'person',
					url_prestudent_id: route.params.person_id
				};
			},
			beforeEnter: (to, from, next) => {
				const isSemester = /^[WS]S\d{4}$/.test(to.params.studiensemester_kurzbz);
				if (!isSemester) {
					return next({name: 'index'});
				}
				next();
			}
		},
		{
			name: 'search',
			path: `/${ciPath}/studentenverwaltung/:studiensemester_kurzbz/search/:searchstr`,
			component: FhcStudentenverwaltung,
			props(route) {
				return {
					url_studiensemester_kurzbz: route.params.studiensemester_kurzbz,
					url_mode: 'search',
					url_prestudent_id: route.params.searchstr
				};
			},
			beforeEnter(to, from, next) {
				const isSemester = /^[WS]S\d{4}$/.test(to.params.studiensemester_kurzbz);
				if (!isSemester) {
					return next({name: 'index'});
				}
				next();
			}
		},
		{
			name: 'search_w_types',
			path: `/${ciPath}/studentenverwaltung/:studiensemester_kurzbz/search/:types/:searchstr`,
			component: FhcStudentenverwaltung,
			props(route) {
				return {
					url_studiensemester_kurzbz: route.params.studiensemester_kurzbz,
					url_mode: 'search',
					url_prestudent_id: route.params.type + '/' + route.params.searchstr
				};
			},
			beforeEnter(to, from, next) {
				const isSemester = /^[WS]S\d{4}$/.test(to.params.studiensemester_kurzbz);
				if (!isSemester) {
					return next({name: 'index'});
				}
				next();
			}
		},
		{
			path: '/:pathMatch(.*)*',
			redirect: {
				name: 'index'
			}
		}
	]
});

router.afterEach((to, from, failure) => {
	let title = 'Studierendenverwaltung FH-Complete';
	if (to.params.studiengang) {
		title = to.params.studiengang + ' ' + title;
	}
	if (to.params.studiensemester_kurzbz) {
		title = to.params.studiensemester_kurzbz + ' ' + title;
	}
	document.title = title;
});

const app = Vue.createApp({
	name: 'StudentenverwaltungApp'
});

app
	.use(router)
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(FhcBase)
	.mount('#main');

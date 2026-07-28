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
import fhcapifactory from "./api/fhcapifactory.js";

import PluginsPhrasen from "../plugins/Phrasen.js";


const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory('/' + ciPath),
	routes: [
		{
			name: 'index',
			path: '/studvw',
			component: FhcStudentenverwaltung,
			children: [
				{
					name: 'stdsem',
					path: 'stdsem/:stdsem',
					component: FhcStudentenverwaltung,
					children: [
						{ name: 'search', path: 'search/:query', component: FhcStudentenverwaltung },
						{ name: 'searchtypes', path: 'search/:types/:query', component: FhcStudentenverwaltung },
						{ name: 'prestudent', path: 'prestudent/:prestudent_id', component: FhcStudentenverwaltung },
						{ name: 'student', path: 'student/:student_uid', component: FhcStudentenverwaltung },
						{ name: 'person', path: 'person/:person_id', component: FhcStudentenverwaltung },
						{ name: 'treemenu', path: ':treemenu(.*)*', component: FhcStudentenverwaltung },
					]
				}
			]
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
	if (to.params.treemenu) {
		const index = to.params.treemenu.findIndex(
			(e, i) => i%2 == 0 && e == 'stg'
		);
		if (index >= 0) {
			title = to.params.treemenu[index + 1].toUpperCase() + ' ' + title;
		}
	}
	if (to.params.stdsem) {
		title = to.params.stdsem.toUpperCase() + ' ' + title;
	}
	document.title = title;
});

FhcApps.router.makeExtendable(router);

const app = Vue.createApp({
	name: 'StudentenverwaltungApp'
});

FhcApps.makeExtendable(app);

app
	.use(router)
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(PluginsPhrasen)
	.mount('#main');

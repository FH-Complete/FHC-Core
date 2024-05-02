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

import Phrasen from "../plugin/Phrasen.js";


const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(),
	routes: [
		{ path: `/${ciPath}/studentenverwaltung`, component: FhcStudentenverwaltung },
		{ path: `/${ciPath}/studentenverwaltung/prestudent/:prestudent_id`, component: FhcStudentenverwaltung },
		{ path: `/${ciPath}/studentenverwaltung/prestudent/:prestudent_id/:tab`, component: FhcStudentenverwaltung },
		{ path: `/${ciPath}/studentenverwaltung/student/:id`, component: FhcStudentenverwaltung },
		{ path: `/${ciPath}/studentenverwaltung/person/:person_id`, component: FhcStudentenverwaltung }
	]
});

const app = Vue.createApp();

app
	.use(router)
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(Phrasen)
	.mount('#main');

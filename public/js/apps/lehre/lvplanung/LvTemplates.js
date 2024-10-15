/**
 * Copyright (C) 2023 fhcomplete.org
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

import LvTemplateUebersicht from '../../../lehre/lvplanung/LvTemplateUebersicht.js';
import {CoreNavigationCmpt} from '../../../components/navigation/Navigation.js';
import FhcAlert from '../../../plugin/FhcAlert.js';
import FhcApi from "../../../plugin/FhcApi.js";
import Phrasen from "../../../plugin/Phrasen.js";


const lvTemplatesApp = Vue.createApp({
	components: {
		CoreNavigationCmpt,
		LvTemplateUebersicht
	}
});

lvTemplatesApp
	.use(primevue.config.default,{zIndex: {overlay: 9999}})
	.use(FhcAlert)
	.use(FhcApi)
	.use(Phrasen)
	.mount('#main')
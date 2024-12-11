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

import BankData from "../../components/Cis/BankData.js";
import fhcapifactory from "../../api/fhcapifactory.js";
import Phrasen from "../../plugin/Phrasen.js";

const bankDataApp = Vue.createApp({
	name: 'BankDataApp',
	components: {
		BankData
	},
	template: `<bank-data></bank-data>`
});

bankDataApp.use(Phrasen).mount('#content');


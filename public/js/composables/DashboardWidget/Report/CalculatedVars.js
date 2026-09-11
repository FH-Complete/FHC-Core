/**
 * Copyright (C) 2026 fhcomplete.org
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

import CurrentStudienjahr from './CalculatedVars/CurrentStudienjahr.js';
import CalcDate from './CalculatedVars/CalcDate.js';
import UserStgs from './CalculatedVars/UserStgs.js';

export function useCalculatedVars() {
	let calculations = {
		CurrentStudienjahr,
		CalcDate,
		UserStgs,
	};
	
	const components = Vue.ref({});
	const options = Vue.ref([]);

	const $p = Vue.inject('$p');
	const $api = Vue.inject('$api');
	const $fhcAlert = Vue.inject('$fhcAlert');

	for (var key in calculations) {
		let label = calculations[key].label;
		if (Array.isArray(label)) {
			label = $p.t(label);
		}
		options.value.push({
			value: key,
			label,
		});
		if (calculations[key].component) {
			components.value[key] = Vue.markRaw(calculations[key].component);
		}
	}

	async function getValueForVar(variable) {
		if (variable.type.match(/^calc:/)) {
			const instructions = variable.type.split(':');

			if (calculations[instructions[1]]?.calculate)
				return calculations[instructions[1]].calculate.bind({
					$p,
					$api,
					$fhcAlert,
				})(instructions);
		}
		return variable?.value;
	}

	return {
		components,
		options,
		getValueForVar,
	};
}
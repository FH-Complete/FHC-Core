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

import ApiReport from '../../../api/factory/report.js';

export function useCalculatedVars() {
	let calculations = {
		CurrentStudienjahr,
		CalcDate,
		UserStgs,
	};
	
	const components = Vue.ref({});
	const options = Vue.ref([]);
	const details = Vue.ref({});

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
		if (!variable.type.match(/^calc:/))
			return variable?.value;

		const instructions = variable.type.split(':');

		if (!calculations[instructions[1]]?.calculate)
			return variable?.value;

		let result = await calculations[instructions[1]].calculate.bind({
			$p,
			$api,
			$fhcAlert,
		})(instructions);

		if (variable.detail.type == 'select') {
			result = await $api.call(ApiReport.vardetails(variable.detail.kurzbz, result));

			return result.data;
		}

		return result;
	}

	async function getValuesForVars(vars, uservars, emitSharedData) {
		let result = {};

		for (var key in vars) {
			result[key] = await getValueForVar(vars[key]);

			if (vars[key].type.substr(0, 5) == 'calc:') {
				const requiresArray = vars[key].detail.multiple ? true : false;
				const isArray = Array.isArray(result[key]);

				// Set details for var
				const varOptions = isArray ? result[key] : [ result[key] ];
				details.value[key] = varOptions.find(Boolean).hasOwnProperty('value')
					? varOptions
					: varOptions.map(value => ({ label: value, value }));

				// Maps result to value(s)
				if (isArray && result[key].find(Boolean).hasOwnProperty('value'))
					result[key] = result[key].map(v => v.value);
				else if (!isArray && result[key].hasOwnProperty('value'))
					result[key] = result[key].value;

				if (requiresArray && !isArray) {
					result[key] = [ result[key] ];
				} else if (!requiresArray && isArray) {
					const isSelect = vars[key].detail.type == 'select';

					let val = undefined;

					if (uservars)
						val = uservars[key]?.value;
					
					if (val && !result[key].some(v1 => v1 == val)) {
						val = undefined;
						delete uservars[key].value;
					}
					
					if (!val)
						val = result[key].find(Boolean); // get first element

					let detail = {
						...vars[key].detail,
						type: 'select',
						options: details.value[key],
					};

					emitSharedData(key, detail);

					result[key] = val;
				}
			} else {
				if (vars[key].type == 'user') {
					// check if user selection is valid and overwrite
					if (!uservars || !uservars[key])
						delete result[key];
					else
						result[key] = uservars[key].value;
				}
				
				// Set details for var
				if (vars[key].detail?.options)
					details.value[key] = vars[key].detail.options;
				else if (result[key])
					details.value[key] = [ { label: result[key], value: result[key] } ];
				else
					details.value[key] = [];
			}
		}

		return result;
	}

	return {
		components,
		options,
		details,
		getValueForVar,
		getValuesForVars,
	};
}

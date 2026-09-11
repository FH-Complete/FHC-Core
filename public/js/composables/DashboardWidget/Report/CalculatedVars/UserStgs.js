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

import CalcRight from '../../../../components/Dashboard/WidgetAdmin/Edit/Report/Vars/Calc/Right.js';

import ApiStudiengang from '../../../../api/factory/studiengang.js';

export default {
	label: 'UserStgs',
	component: CalcRight,
	async calculate(instructions) {
		try {
			const result = await this.$api.call(ApiStudiengang.getAllowed(instructions[2]));
			return result.data;
		} catch(error) {
			this.$fhcAlert.handleSystemError(error);
		}
		return null;
	},
}
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

import CalcDate from '../../../../components/Dashboard/WidgetAdmin/Edit/Report/Vars/Calc/Date.js';

export default {
	label: 'CalcDate',
	component: CalcDate,
	async calculate(instructions) {
		let duration = instructions[2] || 'P';
		if (duration[0] == '-')
			duration = luxon.Duration.fromISO(duration.substr(1)).negate();
		else
			duration = luxon.Duration.fromISO(duration);

		return luxon.DateTime.now().plus(duration);
	},
}
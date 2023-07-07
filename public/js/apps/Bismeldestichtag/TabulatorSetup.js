/**
 * Copyright (C) 2022 fhcomplete.org
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

/**
 *
 */
export const BismeldestichtagTabulatorOptions = {
	maxHeight: "100%",
	minHeight: 50,
	layout: 'fitColumns',
	columns: [
		{title: 'Meldestichtag',field: 'meldestichtag', headerFilter: true, formatter: function(cell){
				return cell.getValue().replace(/(.*)-(.*)-(.*)/, '$3.$2.$1');
			}
		},
		{title: 'Studiensemester', field: 'studiensemester_kurzbz', headerFilter: true}
	]
};

/**
 *
 */
export const BismeldestichtagTabulatorEventHandlers = [
	{
		event: "rowClick",
		handler: function(e, row) {
			let data = row.getData();
			alert(data.Studiensemester + ': ' + data.Meldestichtag);
		}
	}
];


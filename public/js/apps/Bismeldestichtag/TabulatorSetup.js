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
	index: 'meldestichtag_id',
	columns: [
		{title: 'Meldestichtag',field: 'meldestichtag', headerFilter: true, formatter: function(cell){
				return BismeldestichtagTabulatorHelperFunctions._formatDate(cell.getValue());
			}
		},
		{title: 'Studiensemester', field: 'studiensemester_kurzbz', headerFilter: true, sorter:function(a, b, aRow, bRow, column, dir, sorterParams) {

				//aRow, bRow - the row components for the values being compared
				let semesterStartA = new Date(aRow.getData().semester_start);
				let semesterStartB = new Date(bRow.getData().semester_start);

				return semesterStartA - semesterStartB; // difference between studiensemester start dates
			}
		},
		{title: 'Semesterstart',field: 'semester_start', headerFilter: true, visible: false, formatter: function(cell){
				return BismeldestichtagTabulatorHelperFunctions._formatDate(cell.getValue());
			}
		},
		{title: 'ID', field: 'meldestichtag_id', headerFilter: true, visible: false},
		{title: 'Insertamum', field: 'insertamum', headerFilter: true, visible: false},
		{title: 'Insertvon', field: 'insertvon', headerFilter: true, visible: false},
		{title: 'Löschen', field: 'loeschen', headerFilter: false, formatter:function(cell){
				return	'<button class="btn btn-outline-secondary delete-btn" data-meldestichtag-id="'+cell.getRow().getIndex()+'">'+
							'<i class="fa fa-xmark"></i>'+
						'</button>';
			}
		}
	]
};

/**
 *
 */
export const BismeldestichtagTabulatorEventHandlers = [
	{
		event: "rowClick",
		handler: function(e, row) {
			if (e.target.nodeName == 'DIV') {
				let data = row.getData();
				alert(data.studiensemester_kurzbz + ': ' + BismeldestichtagTabulatorHelperFunctions._formatDate(data.meldestichtag));
			}
		}
	}
];

let BismeldestichtagTabulatorHelperFunctions = {
	_formatDate: function(date) {
		return date.replace(/(.*)-(.*)-(.*)/, '$3.$2.$1');
	}
}

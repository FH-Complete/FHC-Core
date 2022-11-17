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
export const LogsViewerTabulatorOptions = {
	height: 700,
	layout: 'fitColumns',
	columns: [
		{title: 'Log ID', field: 'LogId', headerFilter: true},
		{title: 'Request ID', field: 'RequestId', headerFilter: true},
		{title: 'Execution time', field: 'ExecutionTime', headerFilter: true},
		{title: 'Executed by', field: 'ExecutedBy', headerFilter: true},
		{title: 'Description', field: 'Description', headerFilter: true},
		{title: 'Data', field: 'Data', headerFilter: true},
		{title: 'Web service type', field: 'WebserviceType', headerFilter: true}
	],
	rowFormatter: function(row) {
		if (row.getData().RequestId.includes("error"))
		{
			row.getElement().style.color = "red";
		}
		else if (row.getData().RequestId.includes("warning"))
		{
			row.getElement().style.color = "orange";
		}
	}
};

/**
 *
 */
export const LogsViewerTabulatorEventHandlers = [
	{
		event: "rowClick",
		handler: function(e, row) {
			alert(row.getData().Data);
		}
	}
];


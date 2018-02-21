/*
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * javascript file for infocenterDetails page
 */

$(document).ready(
	function ()
	{
		//initialise table sorter
		addTablesorter("doctable", [[2, 1], [1, 0]], ["zebra"]);
		addTablesorter("nachgdoctable", [[2, 0], [1, 1]], ["zebra"]);
		addTablesorter("msgtable", [[0, 1], [2, 0]], ["zebra", "filter"], 2);
		addTablesorter("logtable", [[0, 1]], ["filter"], 2);
		addTablesorter("notiztable", [[0, 1]], ["filter"], 2);

		//add pager
		tablesortAddPager("logtable", "logpager", 23);
		tablesortAddPager("notiztable", "notizpager", 10);

		//initialise datepicker
		$.datepicker.setDefaults($.datepicker.regional['de']);
		$(".dateinput").datepicker({
			"dateFormat": "dd.mm.yy"
		});

		//add click events to "formal geprüft" checkboxes
/*					$(".prchbox input[type=checkbox]").click(
		 function()
		 {
		 var akteid = this.;
		 var personid = ;
		 window.location = "../saveFormalGeprueft?akte_id="+akteid+"&formal_geprueft=" + this.checked + "&person_id="+personid;
		 }
		 );*/

		//add submit event to message send link
		$("#sendmsglink").click(
			function ()
			{
				$("#sendmsgform").submit();
			}
		);

		//prevent opening modal when Statusgrund not chosen
		$("#absageModal").on('show.bs.modal', function (e)
			{
				if ($("[name=statusgrund]").val() === "null")
				{
					$("#statusgrselect").addClass("has-error");
					return e.preventDefault();
				}
			}
		);

		$("[name=statusgrund]").change(function ()
			{
				$("#statusgrselect").removeClass("has-error");
			}
		);
	}
);

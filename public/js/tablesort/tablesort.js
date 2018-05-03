/**
 * adds tablesorter to specified tableid, german date format, default theme
 * @param tableid
 * @param sortList columns to sort by, as array of arrays (each array contains column number and 1/0 for asc/desc order)
 * @param widgets optional widgets like zebra or filter
 * @param minrows optional minimal amount of rows for filter row to be shown (only relevant for filter widget)
 */
function addTablesorter(tableid, sortList, widgets, minrows)
{
	$("#" + tableid).tablesorter(
		{
			theme: "default",
			dateFormat: "ddmmyyyy",
			sortList: sortList,
			widgets: widgets
		}
	);

	if($("#" + tableid + " tr.tablesorter-filter-row").length)
	{
		//hide filters if less than n datarows (+ 2 for headings and filter row itself), default 0
		var minrows = minrows || 0;
		if ($("#" + tableid + " tr").length < minrows + 2)
		{
			$("#" + tableid + " tr.tablesorter-filter-row").hide();
		}
	}
}

/**
 * adds pager for specified tableid. Assumes bootstap icons are available!
 * @param tableid
 * @param pagerid
 * @param size number of rows for each page
 */
function tablesortAddPager(tableid, pagerid, size)
{
	var html =
		'<div id="' + pagerid + '" class="pager"> ' +
		'<form class="form-inline">' +
		'<i class="fa fa-step-backward first"></i>&nbsp;' +
		'<i class="fa fa-backward prev"></i>' +
		'<span class="pagedisplay"></span>' +
		'<i class="fa fa-forward next"></i>&nbsp;' +
		'<i class="fa fa-step-forward last"></i>' +
		'</form>' +
		'</div>';

	var rowcount = $("#" + tableid + " tr").length;

	//not show pager if only one table page
	if (rowcount > size)
	{
		var table = $("#" + tableid);
		table.after(html);

		table.tablesorterPager(
			{
				container: $("#" + pagerid),
				size: size,
				cssDisabled: 'disabled',
				savePages: false,
				output: '{startRow} – {endRow} / {totalRows} Zeilen'
			}
		);
	}
}
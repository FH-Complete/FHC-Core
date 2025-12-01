export default {
	getCourselist(le_id, start_date, end_date, stundenplan)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lvPlan/getLeEvents/' + encodeURIComponent(le_id) + "/" + encodeURIComponent(start_date) + "/" + encodeURIComponent(end_date) + "/" + encodeURIComponent(stundenplan),
		};
	},
	exportCalendar(le_id, stundenplan)
	{
		return FHC_JS_DATA_STORAGE_OBJECT.app_root +  'content/statistik/termine.xls.php?lehreinheit_id=' + encodeURIComponent(le_id) + '&db_stpl_table='+encodeURIComponent(stundenplan);
	}
};

export default {
	getCourselist(lv_id, start_date, end_date, stundenplan)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lvPlan/getLvEvents/' + encodeURIComponent(lv_id) + "/" + encodeURIComponent(start_date) + "/" + encodeURIComponent(end_date) + "/" + encodeURIComponent(stundenplan),
		};
	},
	exportCalendar(lv_id, stundenplan)
	{
		return FHC_JS_DATA_STORAGE_OBJECT.app_root +  'content/statistik/termine.xls.php?lehrveranstaltung_id=' + encodeURIComponent(lv_id) + '&db_stpl_table='+encodeURIComponent(stundenplan);
	}
};

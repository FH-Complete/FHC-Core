/**
 * Javascript file for Reihungstest overview page
 */

/**
* Global function used by FilterWidget JS to refresh the side menu
* NOTE: it is called from the FilterWidget JS therefore must be a global function
*/
function refreshSideMenuHook()
{
	FHC_NavigationWidget.refreshSideMenuHook('organisation/Reihungstest/setNavigationMenuArrayJson');
}

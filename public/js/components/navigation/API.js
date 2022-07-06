import {CoreRESTClient} from '../../RESTClient.js';

const CORE_NAVIGATION_CMPT_TIMEOUT = 2000;

/**
 *
 */
export const CoreNavigationAPIs = {
	/**
	 *
	 */
	getHeader: function(navigationPage) {
		return CoreRESTClient.get(
			'system/Navigation/header',
			{
				navigation_page: navigationPage
			},
			{
				timeout: CORE_NAVIGATION_CMPT_TIMEOUT
			}
		);
	},
	/**
	 *
	 */
	getMenu: function(navigationPage) {
		return CoreRESTClient.get(
			'system/Navigation/menu',
			{
				navigation_page: navigationPage
			},
			{
				timeout: CORE_NAVIGATION_CMPT_TIMEOUT
			}
		);
	}
}


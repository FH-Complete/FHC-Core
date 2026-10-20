/**
 * check if an absolute url is a URL to Compat CI-Controller
 *
 * @param {string} link
 * @returns {boolean}
 */
const isCompatLink = function(link) {
	let ci_router_url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
	let isCompatLink = link.startsWith(ci_router_url + '/Cis/Compat/');
	return isCompatLink;
};

/**
 * calc Param Object with path and query members for use with router-link component
 *
 * @param {string} link
 * @returns {object}
 */
const calcCompatRouterLink = function (link) {
	let ci_router_url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
	let uri = link.replace(ci_router_url, '').split('?');
	let path = uri[0];
	let query = (uri.length === 2) ? VueRouter.parseQuery(uri[1]) : {};
	return {
		"path": path,
		"query": query
	};
};

const isRouterLink = function(router, url) {
	if(url === null) {
		return false;
	}
	const robj = router.resolve(url, router.currentRoute);
	console.log('isRouterLink');
};

export {
	isCompatLink,
	calcCompatRouterLink,
	isRouterLink
};

export default {
	isCompatLink,
	calcCompatRouterLink,
	isRouterLink
};
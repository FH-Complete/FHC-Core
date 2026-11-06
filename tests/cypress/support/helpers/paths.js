/**
 * The paths of every request and page visit, relative to BASE_URL.
 *
 * The suite uses the entry point of the CIS4 page (FhcApi: app_root + ci_router, cis.php sets
 * index_page). So a test passes the same config file and DB role as a real user. CommonJS, so the
 * Node side (suites/noten.js) and the specs share this file.
 */

const ENTRY = "cis.php";

/** buildApiPath("Noten/getCisConfig") -> "/cis.php/api/frontend/v1/Noten/getCisConfig" */
const buildApiPath = (endpoint) => `/${ENTRY}/api/frontend/v1/${endpoint}`;

/** buildPagePath("Cis/Benotungstool") -> "/cis.php/Cis/Benotungstool" */
const buildPagePath = (route) => `/${ENTRY}/${route}`;

module.exports = { ENTRY, buildApiPath, buildPagePath };

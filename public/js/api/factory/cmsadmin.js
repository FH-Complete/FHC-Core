export default {

	// --- CmsAdmin ---

	getTree(menu, filter) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getTree',
			params: { menu, ...(filter ? { filter } : {}) }
		};
	},
	getSubtree(content_id) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getSubtree',
			params: { content_id }
		};
	},
	getContent(content_id) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getContent',
			params: { content_id }
		};
	},
	getContentsprache(content_id, sprache, version) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getContentsprache',
			params: { content_id, sprache, version }
		};
	},
	// Feeds the tree filter: child count, insertamum, updateamum and the own-edit flag.
	getTreeMeta() {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getTreeMeta'
		};
	},
	// Where each content sits. The tree asks for the whole visible list at once, so the
	// hierarchy toggle costs one request and not one per node.
	getAncestors(content_ids) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getAncestors',
			params: { content_ids: content_ids.join(',') }
		};
	},
	// Views of one content. One counting query, so the tab loads it right away.
	// The server gates both click endpoints on the config flag and on the admin right,
	// so a caller without both gets an error instead of numbers.
	getClickCount(content_id, months) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getClickCount',
			params: { content_id, months }
		};
	},
	// The ranking over the whole log. Takes seconds, so the tab asks for it on request.
	getClickRanking(months, sprache) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getClickRanking',
			params: { months, sprache }
		};
	},
	getTemplates() {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getTemplates'
		};
	},
	getOrganisationseinheiten() {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getOrganisationseinheiten'
		};
	},
	getSprachen() {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getSprachen'
		};
	},
	// Fills the DMS category field of the contentcomponent insert dialog.
	getDmsKategorien() {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getDmsKategorien'
		};
	},
	// Fills the person field of the contentcomponent insert dialog.
	getMitarbeiter() {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getMitarbeiter'
		};
	},
	// Fills the document field of the contentcomponent insert dialog.
	getDmsKategorieDokumente(kategorie_kurzbz) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getDmsKategorieDokumente',
			params: { kategorie_kurzbz }
		};
	},
	getUsage(content_id) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getUsage',
			params: { content_id }
		};
	},
	postContent(parent_content_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdmin/postContent',
			params: { parent_content_id }
		};
	},
	postTranslation(content_id, sprache, version, target_sprache) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdmin/postTranslation',
			params: { content_id, sprache, version, target_sprache }
		};
	},
	postVersion(content_id, sprache) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdmin/postVersion',
			params: { content_id, sprache }
		};
	},
	putProperties(content_id, sprache, version, template_kurzbz, oe_kurzbz, aktiv, menu_open, beschreibung, titel, sichtbar) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdmin/putProperties',
			params: { content_id, sprache, version, template_kurzbz, oe_kurzbz, aktiv, menu_open, beschreibung, titel, sichtbar }
		};
	},
	deleteContent(content_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdmin/deleteContent',
			params: { content_id }
		};
	},
	deleteContentsprache(content_id, sprache, version) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdmin/deleteContentsprache',
			params: { content_id, sprache, version }
		};
	},

	// --- CmsAdminInhalt ---

	getFormSchema(template_kurzbz) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdminInhalt/getFormSchema',
			params: { template_kurzbz }
		};
	},
	getFormData(content_id, sprache, version) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdminInhalt/getFormData',
			params: { content_id, sprache, version }
		};
	},
	getLock(content_id, sprache, version) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdminInhalt/getLock',
			params: { content_id, sprache, version }
		};
	},
	getVersions(content_id, sprache) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdminInhalt/getVersions',
			params: { content_id, sprache }
		};
	},
	putFormData(content_id, sprache, version, values) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminInhalt/putFormData',
			params: { content_id, sprache, version, values }
		};
	},
	postLock(contentsprache_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminInhalt/postLock',
			params: { contentsprache_id }
		};
	},
	deleteLock(contentsprache_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminInhalt/deleteLock',
			params: { contentsprache_id }
		};
	},
	deleteLockForced(contentsprache_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminInhalt/deleteLockForced',
			params: { contentsprache_id }
		};
	},

	// --- CmsAdminStruktur ---

	getGruppen(content_id) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdminStruktur/getGruppen',
			params: { content_id }
		};
	},
	getAllGruppen() {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdminStruktur/getAllGruppen'
		};
	},
	getChilds(content_id, sprache) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdminStruktur/getChilds',
			params: { content_id, sprache }
		};
	},
	// The hierarchy tab lists them above the children. A content can have several parents.
	getParents(content_id, sprache) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdminStruktur/getParents',
			params: { content_id, sprache }
		};
	},
	getPossibleChilds(content_id, sprache) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdminStruktur/getPossibleChilds',
			params: { content_id, sprache }
		};
	},
	postGruppe(content_id, gruppe_kurzbz) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminStruktur/postGruppe',
			params: { content_id, gruppe_kurzbz }
		};
	},
	deleteGruppe(content_id, gruppe_kurzbz) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminStruktur/deleteGruppe',
			params: { content_id, gruppe_kurzbz }
		};
	},
	postChild(content_id, child_content_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminStruktur/postChild',
			params: { content_id, child_content_id }
		};
	},
	deleteChild(contentchild_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminStruktur/deleteChild',
			params: { contentchild_id }
		};
	},
	putChildSort(contentchild_id, direction) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminStruktur/putChildSort',
			params: { contentchild_id, direction }
		};
	},
	// Writes the whole order after a drag and drop. The ids carry the wanted order.
	putChildOrder(content_id, contentchild_ids) {
		return {
			method: 'post',
			url: '/api/frontend/v1/CmsAdminStruktur/putChildOrder',
			params: { content_id, contentchild_ids }
		};
	}
};

export default {

	// --- CmsAdmin ---

	getTree(menu, filter) {
		return {
			method: 'get',
			url: '/api/frontend/v1/CmsAdmin/getTree',
			params: { menu, ...(filter ? { filter } : {}) }
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
	}
};

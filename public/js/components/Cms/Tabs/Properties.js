import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';

export default {
	name: 'CmsProperties',
	props: {
		contentId: Number,
		sprache: String,
		version: Number,
		contentInfo: Object
	},
	emits: ['reload-content-info', 'reload-tree', 'select-content'],
	data() {
		return {
			titel: '',
			sichtbar: false,
			template_kurzbz: '',
			oe_kurzbz: '',
			aktiv: false,
			menu_open: false,
			beschreibung: '',
			templates: [],
			organisationseinheiten: [],
			templateChanged: false,
			saving: false
		};
	},
	watch: {
		contentId() { this.loadData(); },
		sprache() { this.loadData(); },
		version() { this.loadData(); },
		contentInfo() { this.loadContentFields(); }
	},
	methods: {
		loadData() {
			this.templateChanged = false;
			if (this.contentId == null || !this.sprache || !this.version) return;

			this.$api
				.call(ApiCmsAdmin.getContentsprache(this.contentId, this.sprache, this.version))
				.then(result => {
					const d = result.data;
					this.titel = d.titel || '';
					this.sichtbar = !!d.sichtbar;
				});

			this.loadContentFields();
		},

		loadContentFields() {
			if (!this.contentInfo) return;
			this.template_kurzbz = this.contentInfo.template_kurzbz || '';
			this.oe_kurzbz = this.contentInfo.oe_kurzbz || '';
			this.aktiv = !!this.contentInfo.aktiv;
			this.menu_open = !!this.contentInfo.menu_open;
			this.beschreibung = this.contentInfo.beschreibung || '';
		},

		loadLookups() {
			this.$api
				.call(ApiCmsAdmin.getTemplates())
				.then(result => { this.templates = result.data || []; });
			this.$api
				.call(ApiCmsAdmin.getOrganisationseinheiten())
				.then(result => { this.organisationseinheiten = result.data || []; });
		},

		onTemplateChange() {
			this.templateChanged = true;
		},

		oeLabel(oe) {
			let label = oe.organisationseinheittyp_kurzbz + ' ' + oe.bezeichnung;
			if (!oe.aktiv) label += ' (' + this.$p.t('cms/inaktiv') + ')';
			return label;
		},

		save() {
			this.saving = true;
			this.$api
				.call(ApiCmsAdmin.putProperties(
					this.contentId, this.sprache, this.version,
					this.template_kurzbz, this.oe_kurzbz,
					this.aktiv, this.menu_open, this.beschreibung,
					this.titel, this.sichtbar
				))
				.then(() => {
					this.$fhcAlert.alertSuccess(this.$p.t('cms/gespeichert'));
					this.$emit('reload-content-info');
					this.$emit('reload-tree');
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => { this.saving = false; });
		}
	},
	mounted() {
		this.loadLookups();
		this.loadData();
	},
	template: `
		<div class="p-3" v-if="contentId != null">
			<h5>{{ $p.t('cms/giltFuerAlleSprachen') }}</h5>
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ $p.t('cms/vorlage') }}</label>
				<div class="col-sm-9">
					<select class="form-select" v-model="template_kurzbz"
						@change="onTemplateChange">
						<option v-for="t in templates" :key="t.template_kurzbz"
							:value="t.template_kurzbz">{{ t.bezeichnung }}</option>
					</select>
					<div v-if="templateChanged" class="text-danger mt-1 fw-bold">
						{{ $p.t('cms/vorlageWarnung') }}
					</div>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ $p.t('cms/organisationseinheit') }}</label>
				<div class="col-sm-9">
					<select class="form-select" v-model="oe_kurzbz">
						<option v-for="oe in organisationseinheiten" :key="oe.oe_kurzbz"
							:value="oe.oe_kurzbz"
							:class="{ 'text-muted': !oe.aktiv }">{{ oeLabel(oe) }}</option>
					</select>
				</div>
			</div>
			<div class="mb-3 row">
				<div class="col-sm-9 offset-sm-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" v-model="aktiv"
							id="prop-aktiv">
						<label class="form-check-label" for="prop-aktiv">
							{{ $p.t('cms/aktiv') }}
						</label>
					</div>
				</div>
			</div>
			<div class="mb-3 row">
				<div class="col-sm-9 offset-sm-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" v-model="menu_open"
							id="prop-menu-open">
						<label class="form-check-label" for="prop-menu-open">
							{{ $p.t('cms/menuOpen') }}
						</label>
					</div>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ $p.t('cms/beschreibung') }}</label>
				<div class="col-sm-9">
					<textarea class="form-control" rows="3" v-model="beschreibung"></textarea>
				</div>
			</div>

			<hr>

			<h5>{{ $p.t('cms/giltFuerDieseVersion') }}</h5>
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ $p.t('cms/titel') }}</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" v-model="titel" maxlength="256">
				</div>
			</div>
			<div class="mb-3 row">
				<div class="col-sm-9 offset-sm-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" v-model="sichtbar"
							id="prop-sichtbar">
						<label class="form-check-label" for="prop-sichtbar">
							{{ $p.t('cms/sichtbar') }}
						</label>
					</div>
				</div>
			</div>

			<hr>

			<div class="d-flex flex-wrap gap-2 mb-3">
				<button class="btn btn-primary" @click="save" :disabled="saving">
					{{ $p.t('cms/speichern') }}
				</button>
			</div>
		</div>
	`
};

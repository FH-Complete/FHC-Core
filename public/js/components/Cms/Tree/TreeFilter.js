import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';

// Empty means "do not narrow by this field". Keep the inputs as strings, the tree parses.
function emptyCriteria() {
	return {
		template_kurzbz: '',
		mine: false,
		updatedFrom: '',
		updatedTo: '',
		createdFrom: '',
		createdTo: '',
		minChildren: '',
		maxChildren: '',
		minLength: '',
		maxLength: '',
		emptyOnly: false,
		sort: ''
	};
}

export { emptyCriteria };

export default {
	name: 'CmsTreeFilter',
	props: {
		// The news menu is a flat list of its own. It carries none of these fields.
		advanced: { type: Boolean, default: true }
	},
	emits: ['filter', 'criteria'],
	data() {
		return {
			text: '',
			open: false,
			templates: [],
			criteria: emptyCriteria()
		};
	},
	computed: {
		// Counts the narrowing fields only. The sort order hides no entry.
		activeCount() {
			const c = this.criteria;
			let count = 0;
			if (c.template_kurzbz) count++;
			if (c.mine) count++;
			if (c.updatedFrom || c.updatedTo) count++;
			if (c.createdFrom || c.createdTo) count++;
			if (c.minChildren !== '' || c.maxChildren !== '') count++;
			if (c.minLength !== '' || c.maxLength !== '') count++;
			if (c.emptyOnly) count++;
			return count;
		}
	},
	methods: {
		submit() {
			this.$emit('filter', this.text);
		},
		toggle() {
			this.open = !this.open;
			if (this.open && !this.templates.length) this.loadTemplates();
		},
		loadTemplates() {
			this.$api
				.call(ApiCmsAdmin.getTemplates())
				.then(result => { this.templates = result.data || []; })
				.catch(this.$fhcAlert.handleSystemError);
		},
		reset() {
			this.text = '';
			// The watcher emits the cleared criteria.
			this.criteria = emptyCriteria();
			this.$emit('filter', '');
		}
	},
	watch: {
		// v-model writes before the watcher runs, so the emitted object is complete.
		criteria: {
			handler(criteria) { this.$emit('criteria', { ...criteria }); },
			deep: true
		}
	},
	created() {
		this.sortOptions = [
			{ value: '', phrase: 'cms/sortHierarchie' },
			{ value: 'titel', phrase: 'cms/titel' },
			{ value: 'updateamum', phrase: 'cms/sortGeaendert' },
			{ value: 'insertamum', phrase: 'cms/sortErstellt' },
			{ value: 'childcount', phrase: 'cms/sortChilds' },
			{ value: 'contentlength', phrase: 'cms/sortInhaltslaenge' }
		];
	},
	template: `
		<div>
			<div class="input-group input-group-sm">
				<input type="text"
					class="form-control"
					:placeholder="$p.t('cms/filterPlatzhalter')"
					v-model="text"
					@keydown.enter="submit">
				<button class="btn btn-outline-secondary" type="button" @click="submit">
					<i class="fa-solid fa-magnifying-glass"></i>
				</button>
			</div>

			<button v-if="advanced"
				type="button"
				class="btn btn-sm btn-link px-0 text-decoration-none"
				@click="toggle">
				<i class="fa-solid fa-fw" :class="open ? 'fa-caret-down' : 'fa-caret-right'"></i>
				{{ $p.t('cms/filterErweitert') }}
				<span v-if="activeCount" class="badge bg-primary ms-1">{{ activeCount }}</span>
			</button>

			<div v-if="advanced && open" class="border rounded p-2 mb-2">
				<div class="mb-2">
					<label class="form-label mb-1">{{ $p.t('cms/vorlage') }}</label>
					<select class="form-select form-select-sm"
						v-model="criteria.template_kurzbz">
						<option value="">{{ $p.t('cms/alleVorlagen') }}</option>
						<option v-for="t in templates"
							:key="t.template_kurzbz"
							:value="t.template_kurzbz">{{ t.bezeichnung }}</option>
					</select>
				</div>

				<div class="form-check mb-2">
					<input type="checkbox"
						class="form-check-input"
						id="cms-filter-mine"
						v-model="criteria.mine">
					<label class="form-check-label" for="cms-filter-mine">
						{{ $p.t('cms/nurEigeneBearbeitungen') }}
					</label>
				</div>

				<div class="form-check mb-2">
					<input type="checkbox"
						class="form-check-input"
						id="cms-filter-empty"
						v-model="criteria.emptyOnly">
					<label class="form-check-label" for="cms-filter-empty">
						{{ $p.t('cms/nurLeereInhalte') }}
					</label>
				</div>

				<div class="mb-2">
					<label class="form-label mb-1">{{ $p.t('cms/sortGeaendert') }}</label>
					<div class="input-group input-group-sm">
						<input type="date" class="form-control"
							:title="$p.t('cms/vonDatum')"
							v-model="criteria.updatedFrom">
						<input type="date" class="form-control"
							:title="$p.t('cms/bisDatum')"
							v-model="criteria.updatedTo">
					</div>
				</div>

				<div class="mb-2">
					<label class="form-label mb-1">{{ $p.t('cms/sortErstellt') }}</label>
					<div class="input-group input-group-sm">
						<input type="date" class="form-control"
							:title="$p.t('cms/vonDatum')"
							v-model="criteria.createdFrom">
						<input type="date" class="form-control"
							:title="$p.t('cms/bisDatum')"
							v-model="criteria.createdTo">
					</div>
				</div>

				<div class="mb-2">
					<label class="form-label mb-1">{{ $p.t('cms/sortChilds') }}</label>
					<div class="input-group input-group-sm">
						<input type="number" min="0" class="form-control"
							:placeholder="$p.t('cms/mindestens')"
							v-model="criteria.minChildren">
						<input type="number" min="0" class="form-control"
							:placeholder="$p.t('cms/hoechstens')"
							v-model="criteria.maxChildren">
					</div>
				</div>

				<div class="mb-2">
					<label class="form-label mb-1">{{ $p.t('cms/sortInhaltslaenge') }}</label>
					<div class="input-group input-group-sm">
						<input type="number" min="0" class="form-control"
							:placeholder="$p.t('cms/mindestens')"
							v-model="criteria.minLength">
						<input type="number" min="0" class="form-control"
							:placeholder="$p.t('cms/hoechstens')"
							v-model="criteria.maxLength">
					</div>
				</div>

				<div class="mb-2">
					<label class="form-label mb-1">{{ $p.t('cms/sortierung') }}</label>
					<select class="form-select form-select-sm"
						v-model="criteria.sort">
						<option v-for="o in sortOptions" :key="o.value" :value="o.value">
							{{ $p.t(o.phrase) }}
						</option>
					</select>
				</div>

				<button type="button" class="btn btn-sm btn-outline-secondary" @click="reset">
					{{ $p.t('cms/filterZuruecksetzen') }}
				</button>
			</div>
		</div>
	`
};

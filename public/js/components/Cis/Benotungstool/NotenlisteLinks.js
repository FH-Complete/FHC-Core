// components/Lehre/Benotungstool/NotenlisteLinks.js
import LehreinheitenModule from '../../DropdownModes/LehreinheitenModule.js';

export const NotenlisteLinks = {
	name: "NotenlisteLinks",
	props: {
		lehrveranstaltung:   { type: Object, default: null },
		sem_kurzbz:          { type: String, default: null },
		selectedLehreinheit: { type: Object, default: null }
	},
	computed: {
		LehreinheitenModule() {
			return LehreinheitenModule;
		},
		lehreinheiten() {
			// reuse the already-loaded LE options from the shared module
			const all = LehreinheitenModule.options ?? [];
			// when a single Lehreinheit is selected, restrict the list to just that one
			if (this.selectedLehreinheit?.lehreinheit_id != null) {
				return all.filter(le => le.lehreinheit_id === this.selectedLehreinheit.lehreinheit_id);
			}
			return all;
		},
		baseUrl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ 'cis/private/lehre/notenliste.xls.php';
		},
		ready() {
			const lv = this.lehrveranstaltung;
			return !!(lv && lv.studiengang_kz != null && lv.lv_semester != null
				&& lv.lehrveranstaltung_id != null && this.sem_kurzbz);
		},
		gesamtUrl() {
			if (!this.ready) return null;
			return this.buildUrl();
		}
	},
	methods: {
		buildUrl(lehreinheit_id = null) {
			const lv = this.lehrveranstaltung;
			const params = new URLSearchParams({
				stg:   lv.studiengang_kz,
				sem:   lv.lv_semester,
				lvid:  lv.lehrveranstaltung_id,
				stsem: this.sem_kurzbz
			});
			if (lehreinheit_id != null) params.set('lehreinheit_id', lehreinheit_id);
			return this.baseUrl + '?' + params.toString();
		}
	},
	template: `
        <div v-if="ready">
            <div class="fw-bold mb-2">{{ $capitalize($p.t('benotungstool/c4notenlisten')) }}</div>

            <div class="mb-1">
                <a class="Item" :href="gesamtUrl" target="_blank" rel="noopener">
                    {{ $capitalize($p.t('benotungstool/c4gesamtliste')) }} {{ lehrveranstaltung.lv_bezeichnung }}
                </a>
            </div>

            <div v-for="le in lehreinheiten" :key="le.lehreinheit_id" class="mb-1" style="padding-left: 1.5rem;">
                <a class="Item" :href="buildUrl(le.lehreinheit_id)" target="_blank" rel="noopener">
                    {{ le.infoString }}
                </a>
            </div>
        </div>
        <div v-else class="text-muted">
            {{ $capitalize($p.t('benotungstool/c4keineStudentenGefunden')) }}
        </div>
    `
};

export default NotenlisteLinks;
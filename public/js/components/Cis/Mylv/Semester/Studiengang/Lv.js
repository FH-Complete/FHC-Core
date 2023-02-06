import LvPruefungen from "./Lv/Pruefungen.js";
import LvInfo from "./Lv/Info.js";
import Phrasen from "../../../../../mixins/Phrasen.js";

// TODO(chris): L10n

export default {
	mixins: [
		Phrasen
	],
	inject: ['studien_semester'],
	props: {
		lehrveranstaltung_id: Number,
		bezeichnung: String,
		module: String,
		farbe: String,
		lvinfo: Boolean,
		benotung: Boolean,
		lvnote: String,
		znote: String,
		studiengang_kuerzel: String,
		semester: String,
		orgform_kurzbz: String,
		sprache: String,
		ects: Number,
		incoming: Number,
		positiv: Boolean
	},
	data: () => {
		return {
			pruefungen: null,
			info: null
		}
	},
	computed: {
		bodyStyle() {return {};
			const bodyStyle = {};
			if (this.farbe)
				bodyStyle['background-color'] = '#' + this.farbe;
			return bodyStyle;
		},
		grade() {
			return this.benotung ? this.znote || this.lvnote || null : null;
		}
	},
	methods: {
		openPruefungen() {
			if (!this.pruefungen) {
				this.pruefungen = true;
				LvPruefungen.popup({
					lehrveranstaltung_id: this.lehrveranstaltung_id, 
					bezeichnung: this.bezeichnung
				}).then(() => this.pruefungen = false).catch(() => this.pruefungen = false);
			}
		},
		openInfos() {
			if (!this.info) {
				this.info = true;
				// TODO(chris): load all this params on ajax?
				LvInfo.popup({
					lehrveranstaltung_id: this.lehrveranstaltung_id, 
					bezeichnung: this.bezeichnung,
					studiengang_kuerzel: this.studiengang_kuerzel,
					semester: this.semester,
					studien_semester: this.studien_semester,
					orgform_kurzbz: this.orgform_kurzbz,
					sprache: this.sprache,
					ects: this.ects,
					incoming: this.incoming
				}).then(() => this.info = false).catch(() => this.info = false);
			}
		}
	},
	template: `<div class="mylv-semester-studiengang-lv card">
		<div v-if="module" class="card-header">
			{{module}}
		</div>
		<div class="card-body d-flex justify-content-center align-items-center" :style="bodyStyle">
			<h6 class="card-title">{{bezeichnung}}</h6>
		</div>
		<div class="card-footer">
			<div class="row">
				<a href="#" class="col-auto text-start text-decoration-none" @click.prevent="openPruefungen">
					<i class="fa fa-check text-success" v-if="positiv"></i>
					{{ grade || p.t('lehre/noGrades') }}
				</a>
				<div v-if="lvinfo" class="col text-end">
					<a class="card-link" href="#" @click.prevent="openInfos">
						<i class="fa fa-info-circle" aria-hidden="true"></i>
					</a>
				</div>
			</div>
		</div>
	</div>`
};
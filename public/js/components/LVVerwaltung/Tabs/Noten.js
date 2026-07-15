import NotenZeugnis from "../../Stv/Studentenverwaltung/Details/Noten/Zeugnis.js";
import NotenTeacher from "../../Stv/Studentenverwaltung/Details/Noten/Teacher.js";
import NotenRepeater from "../../Stv/Studentenverwaltung/Details/Noten/Repeater.js";
import ApiLVNoten from "../../../api/lehrveranstaltung/noten.js";
import { highlightGesamtnote } from "../../../helpers/DocumentHelper.js";
const LOCAL_STORAGE_ID = 'lv_details_noten_2025_12_02_stdsem_all';

export default {
	name: "LVTabNoten",
	components: {
		NotenZeugnis,
		NotenTeacher,
		NotenRepeater
	},
	provide() {
		return {
			config: this.config
		}
	},
	props: {
		modelValue: Object,
		config: Object
	},
	data() {
		return {
			stdsem: '',
			endpoint: ApiLVNoten,
			tabulatorOptions: {
				visibleColumns: {
					vorname: true,
					nachname: true,
					lehrveranstaltung_bezeichnung: false
				},
				headerFilter: {
					vorname: true,
					nachname: true,
					lehrveranstaltung_bezeichnung: false
				},
				persistenceZeugnisID: 'lv-details-noten-zeugnis-2025120401',
				persistenceTeacherID: 'lv-details-noten-teacher-2025120401',
			},
			zeugnisLoaded: false,
			teacherLoaded: false,
		};
	},
	methods: {
		reload() {
			this.zeugnisLoaded = false;
			this.teacherLoaded = false;

			this.$refs.zeugnis.$refs.table.reloadTable();
			this.$refs.teacher.$refs.table.reloadTable();
		},
		saveStdsem(event) {
			window.localStorage.setItem(LOCAL_STORAGE_ID, event.target.value);
		},
		onZeugnisLoaded() {
			this.zeugnisLoaded = true;
			this.checkHighlight();
		},
		onTeacherLoaded() {
			this.teacherLoaded = true;
			this.checkHighlight();
		},
		checkHighlight()
		{
			if (!this.zeugnisLoaded || !this.teacherLoaded)
				return;

			if (!this.$refs.zeugnis || !this.$refs.teacher)
				return;

			let zeugnisTable = this.$refs.zeugnis.$refs.table.tabulator;
			let teacherTable = this.$refs.teacher.$refs.table.tabulator;

			if (!zeugnisTable || !teacherTable)
				return;

			highlightGesamtnote(zeugnisTable, teacherTable);
		}
	},
	created() {
		const savedPath = window.localStorage.getItem(LOCAL_STORAGE_ID);
		this.stdsem = savedPath || '';
	},
	template: `
	<div class="stv-details-noten d-flex flex-column overflow-hidden">
		<div class="mb-3">
			<select class="form-select" v-model="stdsem" @input="saveStdsem" v-if="config?.semesterSelect ?? true">
				<option value="">{{ $p.t('ui/current_semester') }}</option>
				<option value="true">{{ $p.t('ui/all_semester') }}</option>
			</select>
		</div>
		<div class="row">
			<div class="col-8">
				<noten-zeugnis 
					ref="zeugnis" 
					:id="modelValue.lehrveranstaltung_id"
					:all-semester="!!stdsem"
					:endpoint="endpoint"
					:optionalTabulatorOptions="tabulatorOptions"
					@loaded="onZeugnisLoaded"/>
			</div>
			<div class="col-4">
				<noten-teacher 
					ref="teacher"
					:id="modelValue.lehrveranstaltung_id"
					:all-semester="!!stdsem"
					:endpoint="endpoint"
					@copied="reload"
					:optionalTabulatorOptions="tabulatorOptions"
					@loaded="onTeacherLoaded"/>
			</div>
		</div>
	</div>`
};
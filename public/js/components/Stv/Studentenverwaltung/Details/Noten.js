import NotenZeugnis from './Noten/Zeugnis.js';
import NotenTeacher from './Noten/Teacher.js';
import NotenRepeater from './Noten/Repeater.js';

import ApiStvGrades from '../../../../api/factory/stv/grades.js';
import { highlightGesamtnote } from "../../../../helpers/DocumentHelper.js";

const LOCAL_STORAGE_ID = 'stv_details_noten_2024-11-25_stdsem_all';

export default {
	name: "TabGrades",
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
			endpoint: ApiStvGrades,
			zeugnisLoaded: false,
			teacherLoaded: false,
		};
	},
	computed: {
		gradeListLink() {
			return this.$api.getUri('/person/gradelist/index/' + this.modelValue.uid);
		}
	},
	methods: {
		reload() {
			this.$refs.zeugnis.$refs.table.reloadTable();
			this.$refs.teacher.$refs.table.reloadTable();
			this.$refs.repeater.$refs.table.reloadTable();
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
	template: /* html */`
	<div class="stv-details-noten d-flex flex-column overflow-hidden">
		<div class="d-flex justify-content-between my-2">
			<div>
				<select class="form-select" v-model="stdsem" @input="saveStdsem">
					<option value="">{{ $p.t('ui/current_semester') }}</option>
					<option value="true">{{ $p.t('ui/all_semester') }}</option>
				</select>
			</div>
			<div>
				<a :href="gradeListLink" target="_blank">
					{{ $p.t('stv/grades_gradelist') }}
					<i class="fa-solid fa-arrow-up-right-from-square"></i>
				</a>
			</div>
		</div>
		<div class="row">
			<div class="col-8">
				<noten-zeugnis ref="zeugnis" :id="modelValue.prestudent_id" :all-semester="!!stdsem" :endpoint="endpoint" @loaded="onZeugnisLoaded"></noten-zeugnis>
			</div>
			<div class="col-4">
				<noten-teacher ref="teacher" :id="modelValue.prestudent_id" :endpoint="endpoint" :all-semester="!!stdsem" @copied="reload" @loaded="onTeacherLoaded"></noten-teacher>
				<noten-repeater class="mt-4" ref="repeater" :student="modelValue" :all-semester="!!stdsem" @copied="reload"></noten-repeater>
			</div>
		</div>
	</div>`
};
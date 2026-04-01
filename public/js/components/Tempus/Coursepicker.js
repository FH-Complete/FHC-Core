import FormInput from '../Form/Input.js';
import draggable from "../../directives/draggable.js";
import ApiCoursePicker from '../../api/factory/tempus/coursepicker.js';

export default {
	components: {
		FormInput
	},
	directives: {
		draggable
	},
	props: {
		stg: {
			type: [String, Number],
			default: null,
		},
		studiensemester: {
			type: String,
			default: null
		},
	},
	emits: ['select-lecturer', 'select-kw'],
	data() {
		return {
			searchparam: '',
			allCourses: [],
			abortController: null,
		}
	},
	computed: {
		courses() {
			const query = (this.searchparam ?? '').trim().toLowerCase();
			if (!query)
				return this.allCourses;

			return this.allCourses.filter(course =>
				course.showname.toLowerCase().includes(query) ||
				course.lektoren?.some(l =>
					l.name.toLowerCase().includes(query) ||
					l.kurzbz.toLowerCase().includes(query)
				)
			);
		}
	},
	watch: {
		stg(val) {
			this.searchparam = '';
			this.loadCoursesByStg(val);
		},
		studiensemester() {
			if (this.stg)
				this.loadCoursesByStg(this.stg);
		},
	},
	methods: {
		async loadCoursesByStg(stg) {
			if (!stg) {
				this.allCourses = [];
				return;
			}

			this.$api.call(ApiCoursePicker.getByStg(this.stg, this.studiensemester))
				.then(result => {
					this.allCourses = result.data.map(e => ({
						lvnr: e.lvnr.join(' '),
						unr: e.unr,
						lektoren: e.lektoren,
						lehrfach_id: e.lehrfach_id,
						studiengang_kz: e.studiengang_kz,
						fachbereich_kurzbz: e.fachbereich_kurzbz,
						semester: e.semester,
						verband: e.verband,
						gruppe: e.gruppe,
						gruppe_kurzbz: e.gruppe_kurzbz,
						raumtyp: e.raumtyp,
						raumtypalternativ: e.raumtypalternativ,
						semesterstunden: e.planstunden,
						stundenblockung: e.stundenblockung,
						wochenrythmus: e.wochenrythmus,
						verplant: e.verplant,
						offenestunden: e.offenestunden,
						start_kw: e.start_kw,
						anmerkung: e.anmerkung,
						studiensemester_kurzbz: e.studiensemester_kurzbz,
						lehrfach: e.lehrfach,
						lehrform: e.lehrform,
						lehrfach_bez: e.lehrfach_bez,
						lehrfach_farbe: e.lehrfach_farbe,
						lehrverband: e.lehrverband.join(' '),
						lehreinheit_id: e.lehreinheit_id,
						lem: e.lem,
						showname: `${e.lehrfach} ${e.lehrform}`,
						orig: {
							type: 'lehreinheit',
							lehreinheit_id: e.lehreinheit_id[0],
							blockung: e.stundenblockung,
							entry: e,
						}
					}));
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		dragLehreinheitCollection(course) {
			const orig = course.orig;
			return {
				type: 'lehreinheit',
				id: orig.lehreinheit_id,
				orig: orig,
				stundenblockung: course.stundenblockung,
			};
		},
		keydown: function(evt, course) {

			switch(evt.key)
			{
				case "1":
					course.tag = '#Singleweek';
					course.mode = 'single';
					break
				case "2":
					course.tag = '#Multiweek';
					course.mode = 'multi';
					break
			}
		},
		selectLecturer: function(lektor) {
			this.$emit('select-lecturer', lektor);
		}
	},
	template: `
	<div class="course-picker d-flex flex-column h-100">
		<div class="p-2">
				<form-input
					:label="$p.t('ui', 'suche')"
					type="text"
					v-model="searchparam"
				/>
			</div>
		<div v-if="!stg" class="d-flex flex-column align-items-center justify-content-center text-center text-muted py-5 px-3 h-100">
			<span class="small fw-semibold mb-1">Keine Lehreinheiten</span>
			<span class="small">Wähle einen Studiengang, um Lehreinheiten anzuzeigen.</span>
		 </div>
		<div v-else class="overflow-auto px-2 pb-2 flex-grow-1">
			<div
				v-for="course in courses"
				:key="course.lehreinheit_id"
				class="course-picker-row"
				v-draggable:move.noimage="dragLehreinheitCollection(course)"
				tabindex="0"
			>
				<div class="d-flex gap-1">
					<span class="fw-semibold small w-50" :tooltip="course.lehrfach_bez">{{ course.lehrfach }} {{ course.lehrform }}</span>
					<span class="fw-semibold small w-50" :tooltip="course.raumtypalternativ">{{ course.raumtyp }}</span>
				</div>
				<div class="d-flex gap-1 text-muted">
					<span class="w-50" :tooltip="course.anmerkung">{{ course.lehrverband }}</span>
					<span 
						style="cursor:pointer"
						class="text-decoration-underline w-50"
						@click.stop="$emit('select-kw', course.start_kw)">KW: {{ course.start_kw }}
					</span>
				</div>
				<div class="d-flex gap-1 text-muted">
					<span
						v-for="(lektor, i) in course.lektoren"
						:key="lektor.uid"
						style="cursor:pointer"
						class="text-decoration-underline w-50"
						@click.stop="selectLecturer(lektor)">
							{{ lektor.kurzbz }}
						<span v-if="i < course.lektoren.length - 1"><br /></span>
					</span>
					<span class="w-50">WR: {{ course.wochenrythmus }} Bl: {{ course.stundenblockung }}</span>
				</div>
				<div class="d-flex gap-1 text-muted">
					<span class="w-50">Offen: {{ course.offenestunden }}</span>
					<span class="w-50">{{ course.semesterstunden }}</span>
				</div>
			</div>
		</div>
	</div>
`
}
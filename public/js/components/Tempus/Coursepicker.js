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
						lehreinheit_id: e.lehreinheit_id,
						lektoren: e.lektoren,
						raumtyp: e.raumtyp,
						raumtypalternativ: e.raumtypalternativ,
						semesterstunden: e.planstunden,
						stundenblockung: e.stundenblockung,
						wochenrythmus: e.wochenrythmus,
						offenestunden: e.offenestunden,
						start_kw: e.start_kw,
						anmerkung: e.anmerkung,
						lehrfach: e.lehrfach,
						lehrform: e.lehrform,
						lehrfach_bez: e.lehrfach_bez,
						lehrfach_farbe: e.lehrfach_farbe,
						lehrverband: e.lehrverband,
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
		courseStyle(course) {
			if (!course.lehrfach_farbe)
				return {};
			return '--event-bg:#' + course.lehrfach_farbe;

		},
		selectLecturer(lektor) {
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
				:style="courseStyle(course)"
				class="course-picker-row"
				v-draggable:move.noimage="dragLehreinheitCollection(course)"
				tabindex="0"
			>
				<div class="d-flex gap-1">
					<span class="fw-semibold small w-50" v-tooltip="course.lehrfach_bez">{{ course.lehrfach }} {{ course.lehrform }}</span>
					<span class="fw-semibold small w-50" v-tooltip="course.raumtypalternativ">{{ course.raumtyp }}</span>
				</div>
				
				<!--TODO(david) entfernen, dient nur für das mappen mit der lvverwaltung-->
				<div class="d-flex gap-1">
					<span class="small w-50" v-tooltip="course.lehreinheit_id">{{ course.lehreinheit_id[0] }} </span>
				</div>
				
				<div class="d-flex gap-1 text-muted">
					<div class="w-50 d-flex flex-column" v-tooltip="course.anmerkung">
						<span
							v-for="verband in course.lehrverband"
							:key="verband">
							{{ verband }}
						</span>
					</div>
					<span
						style="cursor:pointer"
						class="text-decoration-underline w-50"
						@click.stop="$emit('select-kw', course.start_kw)">KW: {{ course.start_kw }}
					</span>
				</div>

				<div class="d-flex gap-1 text-muted">
					<div class="w-50 d-flex flex-column"
						v-tooltip="course.lektoren.length > 3 ? course.lektoren.map(l => l.kurzbz).join(', ') : null">
						<span
							v-for="lektor in course.lektoren.slice(0, 3)"
							:key="lektor.uid"
							style="cursor:pointer"
							class="text-decoration-underline"
							@click.stop="selectLecturer(lektor)">
								{{ lektor.kurzbz }}
						</span>
						<span v-if="course.lektoren.length > 3" class="text-muted fst-italic">
							+{{ course.lektoren.length - 3 }} weitere...
						</span>
					</div>
					<span class="w-50 align-self-start">WR: {{ course.wochenrythmus }} Bl: {{ course.stundenblockung }}</span>
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
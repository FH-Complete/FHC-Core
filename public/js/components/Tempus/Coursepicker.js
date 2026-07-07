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
			sortBy: null,
			multiWeekIds: new Set()
		}
	},
	computed: {
		courses() {
			const query = (this.searchparam ?? '').trim().toLowerCase();

			let result;
			if (query)
			{
				result = this.allCourses.filter(course =>
					course.showname.toLowerCase().includes(query) ||
					course.lektoren?.some(lektor =>
						lektor.name.toLowerCase().includes(query) ||
						lektor.kurzbz.toLowerCase().includes(query)
					)
				);
			}
			else
				result = this.allCourses;

			if (this.sortBy === 'lektor-asc' || this.sortBy === 'lektor-desc')
			{
				let dir = this.sortBy === 'lektor-asc' ? 1 : -1;

				return result.sort((a, b) => {
					let an = a.lektoren?.[0]?.kurzbz ?? '';
					let bn = b.lektoren?.[0]?.kurzbz ?? '';
					return an.localeCompare(bn) * dir;
				});
			}
			else if (this.sortBy === 'kw-asc')
			{
				return result.sort((a, b) => (a.start_kw ?? 0) - (b.start_kw ?? 0));
			}
			else if (this.sortBy === 'kw-desc')
			{
				return result.sort((a, b) => (b.start_kw ?? 0) - (a.start_kw ?? 0));
			}
			else if (this.sortBy === 'stunden-asc')
			{
				return result.sort((a, b) => (a.offenestunden ?? 0) - (b.offenestunden ?? 0));
			}
			else if (this.sortBy === 'stunden-desc')
			{
				return result.sort((a, b) => (b.offenestunden ?? 0) - (a.offenestunden ?? 0));
			}

			return result;
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
				multiweek: this.isMultiWeek(course),
			};
		},
		courseStyle(course) {
			if (!course.lehrfach_farbe)
				return {};
			return '--event-bg:#' + course.lehrfach_farbe;

		},
		selectLecturer(lektor) {
			this.$emit('select-lecturer', lektor);
		},
		setSort(field) {
			if (this.sortBy === `${field}-asc`)
				this.sortBy = `${field}-desc`;
			else if (this.sortBy === `${field}-desc`)
				this.sortBy = null;
			else
				this.sortBy = `${field}-asc`;
		},
		sortIcon(field) {
			if (this.sortBy === `${field}-asc`)
				return 'fa-arrow-up';
			if (this.sortBy === `${field}-desc`)
				return 'fa-arrow-down';
			return 'fa-sort';
		},
		isSortActive(field) {
			return this.sortBy === `${field}-asc` || this.sortBy === `${field}-desc`;
		},
		reload()
		{
			this.loadCoursesByStg(this.stg);
		},
		toggleMultiWeek(course)
		{
			let id = course.lehreinheit_id[0];
			let weekIds = new Set(this.multiWeekIds);
			if (weekIds.has(id))
				weekIds.delete(id);
			else
				weekIds.add(id);

			this.multiWeekIds = weekIds;
		},
		isMultiWeek(course)
		{
			return this.multiWeekIds.has(course.lehreinheit_id[0]);
		},
	},
	template: `
	<div class="course-picker d-flex flex-column h-100">
		<div class="p-2">
			<form-input
				:label="$p.t('ui', 'suche')"
				type="text"
				v-model="searchparam"
			/>
			<div class="d-flex gap-1 mt-2">
				<button
					type="button"
					class="btn btn-sm btn-outline-secondary"
					:disabled="!stg"
					@click="reload">
					<i class="fa fa-rotate-right"></i>
				</button>
				<button
					type="button"
					class="btn btn-sm"
					:class="isSortActive('lektor') ? 'btn-primary' : 'btn-outline-secondary'"
					@click="setSort('lektor')">
					Lkt <i class="fa" :class="sortIcon('lektor')"></i>
				</button>
				<button
					type="button"
					class="btn btn-sm"
					:class="isSortActive('kw') ? 'btn-primary' : 'btn-outline-secondary'"
					@click="setSort('kw')">
					KW <i class="fa" :class="sortIcon('kw')"></i>
				</button>
				<button
					type="button"
					class="btn btn-sm"
					:class="isSortActive('stunden') ? 'btn-primary' : 'btn-outline-secondary'"
					@click="setSort('stunden')">
					Std <i class="fa" :class="sortIcon('stunden')"></i>
				</button>
			</div>
		</div>
		<div v-if="!stg" class="d-flex flex-column align-items-center justify-content-center text-center text-muted py-5 px-3 h-100">
			<span class="small fw-semibold mb-1">Keine Lehreinheiten</span>
			<span class="small">Wähle einen Studiengang, um Lehreinheiten anzuzeigen.</span>
		</div>
		<div v-else class="overflow-auto px-2 pb-2 flex-grow-1">
			<div
				v-for="course in courses"
				:key="course.lehreinheit_id"
				style="cursor:grab"
				:style="courseStyle(course)"
				class="course-picker-row"
				v-draggable:move.noimage="dragLehreinheitCollection(course)"
				tabindex="0"
			>
				<div class="d-flex gap-1">
					<span class="fw-semibold small w-50" v-tooltip="course.lehrfach_bez">{{ course.lehrfach }} {{ course.lehrform }}</span>
					<span class="fw-semibold small w-50" v-tooltip="course.raumtypalternativ">{{ course.raumtyp }}</span>
					<i
						class="fa fa-calendar-week"
						:class="isMultiWeek(course) ? 'text-primary' : 'text-muted'"
						style="cursor:pointer"
						v-tooltip="isMultiWeek(course) ? 'MultiWeek aktiv' : 'MultiWeek Verplanung aktivieren'"
						@click.stop="toggleMultiWeek(course)"
					></i>
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
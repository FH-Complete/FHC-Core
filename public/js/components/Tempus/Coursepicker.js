import FormInput from '../Form/Input.js';
import draggable from "../../directives/draggable.js";
import ApiCoursePicker from '../../api/factory/tempus/coursepicker.js';

const VERTRAG_FILTER = {
	bestellt: ['bestellt', 'erteilt', 'akzeptiert'],
	erteilt: ['erteilt', 'akzeptiert'],
};

export default {
	components: {
		FormInput
	},
	directives: {
		draggable
	},
	props: {
		studiengaenge: {
			type: Array,
			default: [],
		},
		lecturers: {
			type: Array,
			default: [],
		},
		studiensemester: {
			type: String,
			default: null
		},
		previewLehreinheitId: {
			type: [String, Number],
			default: null
		},
		previewLoading: {
			type: Boolean,
			default: false
		},
	},
	inject: {
		showVertragFilter: {
			from: 'showVertragFilter',
			default: () => false
		},
	},
	emits: ['select-lecturer', 'select-kw', 'preview-raumvorschlag'],
	data() {
		return {
			searchparam: '',
			allCourses: [],
			sortBy: null,
			multiWeekIds: new Set(),
			favorites: new Set(),
			filter: {},
			isMultiWeekAll: false,
			vertragFilter: 'alle'
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
					course.start_kw.includes(query) ||
					course.lektoren?.some(lektor =>
						lektor.name.toLowerCase().includes(query) ||
						lektor.kurzbz.toLowerCase().includes(query)
					)
				);
			}
			else
				result = this.allCourses;

			if (VERTRAG_FILTER[this.vertragFilter])
			{
				const erlaubt = VERTRAG_FILTER[this.vertragFilter];
				result = result.filter(course =>
					course.vertraege?.some(vertrag => erlaubt.includes(vertrag.kurzbz))
				);
			}

			if (this.sortBy === 'lektor-asc' || this.sortBy === 'lektor-desc')
			{
				let dir = this.sortBy === 'lektor-asc' ? 1 : -1;

				result = result.sort((a, b) => {
					let an = a.lektoren?.[0]?.kurzbz ?? '';
					let bn = b.lektoren?.[0]?.kurzbz ?? '';
					return an.localeCompare(bn) * dir;
				});
			}
			else if (this.sortBy === 'kw-asc')
			{
				result = result.sort((a, b) => (a.start_kw ?? 0) - (b.start_kw ?? 0));
			}
			else if (this.sortBy === 'kw-desc')
			{
				result = result.sort((a, b) => (b.start_kw ?? 0) - (a.start_kw ?? 0));
			}
			else if (this.sortBy === 'stunden-asc')
			{
				result = result.sort((a, b) => (a.offenestunden ?? 0) - (b.offenestunden ?? 0));
			}
			else if (this.sortBy === 'stunden-desc')
			{
				result = result.sort((a, b) => (b.offenestunden ?? 0) - (a.offenestunden ?? 0));
			}

			return [...result].sort((a, b) => {
				let ap = this.isPinned(a) ? 1 : 0;
				let bp = this.isPinned(b) ? 1 : 0;
				return bp - ap;
			});
		}
	},
	watch: {
		studiengaenge: {
			deep: true,
			handler() {
				this.loadCourses();
			}
		},
		lecturers: {
			deep: true,
			handler() {
				this.loadCourses();
			}
		},
		studiensemester() {
			if (this.studiengaenge)
				this.loadCourses();
		},
	},
	methods: {
		async loadCourses()
		{
			const hasLektoren = this.lecturers.length > 0;
			const hasStg = this.studiengaenge.length > 0;

			this.filter = {};
			if (hasLektoren)
				this.filter.uid = this.lecturers;

			if (hasStg) {
				this.filter.stg = this.studiengaenge.map(
					({ stg_kz, semester, orgform_kurzbz }) => ({
						stg_kz,
						semester,
						orgform_kurzbz,
					}),
				);
			}

			if (Object.keys(this.filter).length === 0)
				return this.allCourses = [];
			this.$api.call(ApiCoursePicker.getCourses(this.filter, this.studiensemester))
				.then(result => {
					this.allCourses = result?.data?.map(e => ({
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
						lvverwaltunglink: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/LVVerwaltung/stdsem/' + e.studiensemester_kurzbz.toLowerCase() + '/le/' + e.lehreinheit_id,
						orig: {
							type: 'lehreinheit',
							lehreinheit_id: e.lehreinheit_id,
							blockung: e.stundenblockung,
							entry: e,
						},
						vertraege: e.vertraege ?? [],
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
			this.loadCourses();
		},
		toggleMultiWeek(course)
		{
			let id = course.lehreinheit_id;
			let weekIds = new Set(this.multiWeekIds);
			if (weekIds.has(id))
				weekIds.delete(id);
			else
				weekIds.add(id);

			this.multiWeekIds = weekIds;
		},
		isMultiWeek(course)
		{
			if (this.isMultiWeekAll)
				return true;
			return this.multiWeekIds.has(course.lehreinheit_id);
		},
		togglePin(course)
		{
			let id = course.lehreinheit_id;
			let favs = new Set(this.favorites);
			if (favs.has(id))
				favs.delete(id);
			else
				favs.add(id);

			this.favorites = favs;
			localStorage.setItem('tempus_coursepicker_favs', JSON.stringify([...favs]));
		},
		isPinned(course)
		{
			return this.favorites.has(course.lehreinheit_id);
		},
		selectRaumvorschlag(course)
		{
			if (this.isRaumvorschlagActive(course))
				this.$emit('preview-raumvorschlag', null);
			else
				this.$emit('preview-raumvorschlag', course.orig);
		},
		isRaumvorschlagActive(course)
		{
			return this.previewLehreinheitId != null && this.previewLehreinheitId === course.lehreinheit_id;
		},
	},
	mounted() {
		this.favorites = new Set(JSON.parse(localStorage.getItem('tempus_coursepicker_favs') || '[]'));
	},
	template: `
	<div class="course-picker d-flex flex-column h-100" data-cy="coursepicker">
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
					:disabled="studiengaenge.length <= 0"
					@click="reload">
					<i class="fa fa-rotate-right"></i>
				</button>
				<button
					type="button"
					class="btn btn-sm"
					:class="isMultiWeekAll ? 'btn-primary' : 'btn-outline-secondary'"
					v-tooltip.left="isMultiWeekAll ? 'MultiWeek aktiv' : 'MultiWeek Verplanung aktivieren'"
					@click="isMultiWeekAll = !isMultiWeekAll">
					<i class="fa fa-calendar-week"></i>
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
			<div class="btn-group btn-group-sm mt-2" role="group" aria-label="Vertragsfilter" v-if="showVertragFilter">
				<button
					v-for="option in [
						{ wert: 'alle', label: 'Alle' },
						{ wert: 'bestellt', label: 'Bestellt' },
						{ wert: 'erteilt', label: 'Erteilt' }
					]"
					:key="option.wert"
					type="button"
					class="btn"
					:class="vertragFilter === option.wert ? 'btn-primary' : 'btn-outline-secondary'"
					@click="vertragFilter = option.wert">
					{{ option.label }}
				</button>
			</div>
		</div>
		<div v-if="Object.keys(filter).length <= 0" class="d-flex flex-column align-items-center justify-content-center text-center text-muted py-5 px-3 h-100">
			<span class="small fw-semibold mb-1">{{$p.t('lehre', 'cptitleempty')}}</span>
			<span class="small">{{$p.t('lehre', 'cpempty')}}</span>
		</div>
		<div v-else class="course-picker-list overflow-auto px-2 pb-2 flex-grow-1">
			<div
				v-for="course in courses"
				:key="course.lehreinheit_id"
				style="cursor:grab"
				:style="courseStyle(course)"
				class="course-picker-row rounded-3 p-2"
				:class="{ 'course-picker-row-pinned': isPinned(course), 'course-picker-row-preview': isRaumvorschlagActive(course) }"
				v-draggable:move.noimage="dragLehreinheitCollection(course)"
				tabindex="0"
				data-cy="coursepicker-row"
			>
				<div class="d-flex gap-1 align-items-start">
					<div class="flex-grow-1" style="min-width: 0">
						<div class="d-flex align-items-center flex-wrap gap-1">
							<a
								:href="course.lvverwaltunglink"
								target="_blank"
								class="fw-semibold text-body text-decoration-none"
								@click.stop>
								{{ course.lehrfach }} {{ course.lehrform }}
							</a>
							<span
								v-for="vertrag in course.vertraege"
								:key="vertrag.kurzbz"
								class="fw-semibold small text-decoration-underline">
								{{ vertrag.bezeichnung }}
							</span>
						</div>
						<div class="text-muted text-truncate" v-tooltip.left="course.lehrfach_bez">{{ course.lehrfach_bez }}</div>
					</div>
					<span class="fw-semibold small w-50" v-tooltip.left="course?.raumtypalternativ">{{ course.raumtyp }}</span>
					<i
						class="fa fa-thumbtack"
						:class="isPinned(course) ? 'text-primary' : 'text-muted'"
						style="cursor:pointer"
						@click.stop="togglePin(course)"
					></i>
					<i
						class="fa fa-calendar-week"
						:class="isMultiWeek(course) ? 'text-primary' : 'text-muted'"
						style="cursor:pointer"
						v-tooltip.left="isMultiWeek(course) ? 'MultiWeek aktiv' : 'MultiWeek Verplanung aktivieren'"
						@click.stop="toggleMultiWeek(course)"
					></i>
					<i
						v-if="!(previewLoading && isRaumvorschlagActive(course))"
						class="fa fa-door-open"
						:class="isRaumvorschlagActive(course) ? 'text-primary' : 'text-muted'"
						style="cursor:pointer"
						v-tooltip.left="isRaumvorschlagActive(course) ? 'Raumvorschlag ausblenden' : 'Raumvorschlag einblenden'"
						@click.stop="selectRaumvorschlag(course)"
					></i>
					<i
						v-else
						class="fa fa-spinner fa-spin text-primary"
						v-tooltip.left="'Räume werden geprüft...'"
					></i>
				</div>
				
				<!--TODO(david) entfernen, dient nur für das mappen mit der lvverwaltung-->
				<div class="d-flex gap-1">
					<span class="small w-50" v-tooltip.left="course.lehreinheit_id">{{ course.lehreinheit_id }} </span>
				</div>
				
				<div class="d-flex gap-1 text-muted">
					<div class="w-50 d-flex flex-column text-truncate" v-tooltip.left="course.anmerkung">
						<span
							v-for="verband in course.lehrverband"
							:key="verband"
							class="text-truncate">
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
					<div class="w-50 d-flex flex-column" style="min-width: 0"
						v-tooltip.left="course.lektoren.map(l => l.kurzbz + ' (' + l.vertragsstatus + ')').join(', ')">
						<span
							v-for="lektor in course.lektoren.slice(0, 3)"
							:key="lektor.uid"
							class="d-flex align-items-center gap-1"
							style="min-width: 0">
							<span
								style="cursor:pointer"
								class="text-decoration-underline flex-shrink-0"
								@click.stop="selectLecturer(lektor)">
								{{ lektor.kurzbz }}
							</span>
					
						</span>
						<span class="w-50 align-self-start">WR: {{ course.wochenrythmus }} Bl: {{ course.stundenblockung }}</span>
					</div>
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
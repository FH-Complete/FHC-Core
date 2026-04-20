import MylvSemesterCards from "./Semester.js";
import MylvTable from "./Table.js";
import ApiAddons from "../../../api/factory/addons.js"

// TODO(chris): phrase: global/studiensemester_auswaehlen
// TODO(chris): phrase: next & prev +aria-label

export default {
	name: 'MyLv',
	components: {
		MylvSemesterCards,
		MylvTable
	},
	data: () => {
		return {
			firstLoad: true,
			studiensemester: null,
			lvs: {},
			currentSemester: null,
			lvMenues: null,
			mode: 'table' // TODO: load from local storage
		};
	},
	provide() {
		return {
			type: Vue.computed(() => this.type),
			lvMenues: Vue.computed(() => this.lvMenues)
		}
	},
	inject: ['isStudent', 'isMitarbeiter'],
	computed: {
		type() {
			if(this.isStudent) return 'student'
			if(this.isMitarbeiter) return 'employee'
			return null
		},
		ready() {
			return this.studiensemester !== null && (!this.firstLoad || this.current.lvs !== null);
		},
		current() {
			if (this.currentSemester === null)
				return { semester: null, lvs: [] };
			if (this.lvs[this.currentSemester] === undefined) {
				this.lvs[this.currentSemester] = {
					semester: this.currentSemester, 
					lvs: null
				};
				axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Mylv/Lvs/' + this.currentSemester).then(res => {
					this.lvs[this.currentSemester].lvs = res.data.retval || [];
					this.firstLoad = false;

					this.$api.call(ApiAddons.getMultipleLvMenu(this.lvs[this.currentSemester].lvs, this.currentSemester)).then(res => {
						this.lvMenues = res.data
					})

				})
			}
			return this.lvs[this.currentSemester];
		},
		nearestSem() {
			let now = Date.now();
			let nearestSem = null;
			let nearestSemDiff = 0;
			this.studiensemester.forEach(sem => {
				let start = new Date(sem.start);
				let end = new Date(sem.ende);
				if (now >= start && now <= end) {
					nearestSem = sem.studiensemester_kurzbz;
					nearestSemDiff = 0;
					return;
				}
				let diff = Math.min(Math.abs(now - start), Math.abs(now - end));
				if (nearestSem === null || diff < nearestSemDiff) {
					nearestSem = sem.studiensemester_kurzbz;
					nearestSemDiff = diff;
				}

			});
			return nearestSem;
		},
		currentIsFirst() {
			return this.studiensemester[0].studiensemester_kurzbz == this.currentSemester;
		},
		currentIsLast() {
			return this.studiensemester[this.studiensemester.length-1].studiensemester_kurzbz == this.currentSemester;
		}
	},
	methods: {
		clickMode(evt, mode) {
			this.mode = mode
		},
		prevSem() {
			this.$refs.studiensemester.selectedIndex--;
			this.$refs.studiensemester.dispatchEvent(new Event('change', { bubbles: true }));
		},
		nextSem() {
			this.$refs.studiensemester.selectedIndex++;
			this.$refs.studiensemester.dispatchEvent(new Event('change', { bubbles: true }));
		},
		updateRouter(val) {
			this.$router.push(`/Cis/MyLv/${val}`);
		}
	},
	created() {
		axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Mylv/Studiensemester').then(res => {
			this.studiensemester = res.data.retval || [];
			const routerStudiensemester = this.$route.params.studiensemester;
			if (routerStudiensemester && this.studiensemester.filter(s => s.studiensemester_kurzbz == routerStudiensemester).length)
				this.currentSemester = routerStudiensemester;
			else
				this.currentSemester = this.nearestSem;
		});
	},
	beforeRouteUpdate(to, from, next){
		if (to.params.studiensemester && this.studiensemester.filter(s => s.studiensemester_kurzbz == to.params.studiensemester).length && to.params.studiensemester != this.currentSemester)
			this.currentSemester = to.params.studiensemester;
		next();

	},
	template: `

	<h2>{{$p.t('lehre/myLV')}}</h2>
	<hr>
	<div class="mylv" v-if="ready">
		<div v-if="currentSemester" class="row justify-content-center mb-3">
			<div class="col-auto d-none">
				<label class="col-form-label">{{$p.t('lehre/studiensemester')}}</label>
			</div>
			<div class="col-auto">
				<div class="input-group">
					<button :aria-label="$p.t('lehre','previousStudSemester')" v-tooltip.top="{showDelay:1000, value:$p.t('lehre','previousStudSemester')}" class="btn btn-outline-secondary" type="button" :disabled="currentIsFirst" @click="prevSem">
						<i class="fa fa-caret-left" aria-hidden="true"></i>
					</button>
					<select ref="studiensemester" v-model="currentSemester" class="form-select" :aria-label="$p.t('global/studiensemester_auswaehlen')" @change="updateRouter($event.target.value)">
						<option v-for="semester in studiensemester" :key="semester.studiensemester_kurzbz">{{semester.studiensemester_kurzbz}}</option>
					</select>
					<button class="btn btn-outline-secondary" :aria-label="$p.t('lehre','nextStudSemester')" v-tooltip.top="{showDelay:1000, value:$p.t('lehre','nextStudSemester')}" type="button" :disabled="currentIsLast" @click="nextSem">
						<i class="fa fa-caret-right" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<div class=" col-auto my-lva-modes">
				<div class="d-flex gap-1 justify-content-end" role="group">
					<button
						type="button"
						class="btn btn-outline-secondary"
						:class="{active: mode === 'cards'}"
						@click="clickMode($event, 'cards')"
					>
						<i class="fa fa-grip"></i>
					</button>
					<button
						type="button"
						class="btn btn-outline-secondary"
						:class="{active: mode === 'table'}"
						@click="clickMode($event, 'table')"
					>
						<i class="fa fa-table"></i>
					</button>
				</div>
			</div>
		</div>
		<div class="alert alert-danger" role="alert" v-else>
			{{$p.t('lehre/noLvFound')}}
		</div>
		<mylv-semester-cards v-if="mode == 'cards'" v-bind="current"/>
		<mylv-table v-else-if="mode == 'table'" v-bind="current"/>
	</div>
	<div class="mylv text-center" v-else>
		<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
	</div>`
};
import Lvs from "./Lvs.js";
import Lehreinheiten from "./Lehreinheiten.js";
import Supervisor from "./Supervisor.js";

import ApiAddons from "../../../api/factory/addons.js"

export default {
	name: 'MyLv',
	components: {
		Lvs,
		Lehreinheiten,
		Supervisor,
	},
	data: () => {
		return {
			firstLoad: true,
			studiensemester: null,
			lvs: {},
			currentSemester: null,
			modes: ["lvs", "lehrenheiten", "supervision", "all"],
			selectedMode: "lvs"
		};
	},
	provide() {
		return {
			type: Vue.computed(() => this.type),
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
					
					this.lvs[this.currentSemester].lvs.forEach(lv=>{

						this.$api.call(ApiAddons.getLvMenu(lv.lehrveranstaltung_id, this.currentSemester)).then(res => {
							if(res.data) {
								
								const lvProp = this.lvs[this.currentSemester].lvs.find(lv2 => lv2.lehrveranstaltung_id == lv.lehrveranstaltung_id)
								lvProp.menu = res.data
								
							}
						})
						
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
		},
	},
	methods: {
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
	template: /*html*/ `
	<div>
		<h2>{{$p.t('lehre/myLV')}}</h2>
		<hr>
		<div class="mylv" v-if="ready">
			<div v-if="currentSemester" class="row justify-content-center mb-3">
				<div class="col-auto d-none">
					<label class="col-form-label">{{$p.t('lehre/studiensemester')}}</label>
				</div>
				<div class="d-flex flex-column flex-md-row justify-content-center gap-2">
					<div>
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
					<div>
						<select v-model="selectedMode" class="form-select" :aria-label="'mode selector placeholder'">
							<option v-for="mode in modes" :key="mode"> {{ mode }} </option>
						</select>
					</div>
				</div>
				<div class="pt-4">
					<lvs v-if="selectedMode === 'lvs' || selectedMode === 'all'" :current="current" />
					<hr v-if="selectedMode === 'all'">
					<lehreinheiten v-if="selectedMode === 'lehrenheiten' || selectedMode === 'all'" :semester="currentSemester" />
					<hr v-if="selectedMode === 'all'">
					<supervisor v-if="selectedMode === 'supervision' || selectedMode === 'all'" :semester="currentSemester" />
				</div>
			</div>
			<div v-else class="alert alert-danger" role="alert">
				{{$p.t('lehre/noLvFound')}}
			</div>
		</div>
		<div class="mylv text-center" v-else>
			<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
		</div>
	</div>
	`,
}
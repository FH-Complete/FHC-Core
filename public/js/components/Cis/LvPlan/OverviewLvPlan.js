import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';

import ApiLvPlan from '../../../api/factory/lvPlan.js';

export default {
	name: "OverviewLvPlan",
	components: {
		FormForm,
		FormInput,
	},
	props: {
		viewData: Object,
		propsViewData: Object
	},
	data() {
		return {
			formData:  {
				stgkz: null,
				semester: null,
				verband: null,
				gruppe: null,
			},
			listStg: [],
			listSem: [],
			listVerband: [],
			listGroup: [],
			dataLvStudiengang: {}
		};
	},
	methods: {
		loadLvPlan(){
			if(!this.formData.stgkz){
				this.$fhcAlert.alertError(this.$p.t('LvPlan', 'chooseStg'));
				return;
			}
			this.$router.push({
				name: "StgOrgLvPlan",
				params: {
					mode: "Week",
					focus_date: this.currentDay,
					stgkz: this.formData.stgkz,
					sem: this.formData.semester,
					verband: this.formData.verband,
					gruppe: this.formData.gruppe,
				}
			});
		},
		loadListSem(){
			this.listSem = [...Array(this.maxSemester).keys()].map(i => i + 1);
			this.loadListVerband();
		},
		loadListVerband(){
			this.$api
				.call(ApiLvPlan.getLehrverband(this.formData.stgkz, this.formData.semester, this.formData.verband))
				.then(result => {
					const data = result.data;
					const mappedData = data.map(item => item.verband);
					this.listVerband = [...new Set(mappedData.filter(v =>
						v !== null &&
						v !== undefined &&
						String(v).trim() !== ""
					))]
					.sort();
				})
				.catch(this.$fhcAlert.handleSystemError);
			this.loadListGroup();
		},
		loadListGroup(){
			this.$api
				.call(ApiLvPlan.getGruppe(this.formData.stgkz, this.formData.semester, this.formData.verband))
				.then(result => {
					const data = result.data;
					const mappedData = data.map(item => item.gruppe);
					this.listGroup =  [...new Set(mappedData.filter(v =>
						v !== null &&
						v !== undefined &&
						String(v).trim() !== ""))]
						.sort();
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	computed: {
		maxSemester(){
			const currentStg = this.listStg.find(
				item => item.studiengang_kz === this.formData.stgkz
			);
			return currentStg.max_semester;
		},
		currentDay() {
			if (!this.propsViewData?.focus_date || isNaN(new Date(this.propsViewData?.focus_date)))
				return luxon.DateTime.now().setZone(this.viewData.timezone).toISODate();
			return this.propsViewData?.focus_date;
		},
	},
	created(){
		this.$api
			.call(ApiLvPlan.getStudiengaenge())
			.then(result => {
				this. listStg = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div cis-lvplan-stg-org-ues d-flex flex-column h-100>
		<div class="mt-3">
	 		<form-form class="row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-5 g-3 mb-3">
	 			<form-input
	 				type="select"
	 				v-model="formData.stgkz"
	 				@change="loadListSem(formData.stgkz)"
	 				>
					<option value="null" selected>{{ $p.t('LvPlan/chooseStg') }}</option>
					<option
						v-for="stg in listStg"
						:key="stg.studiengang_kz"
						:value="stg.studiengang_kz"
						>
						{{ stg.kurzbzlang }} ({{ stg.bezeichnung }})
					</option>
	 			</form-input>
	 			<form-input
	 				type="select"
	 				v-model="formData.semester"
	 				@change="loadListVerband()"
	 				>
						<option value="null" selected>Semester</option>
						<option
							v-for="sem in listSem"
							:key="sem"
							:value="sem"
							>
							{{ sem }}
						</option>
	 			</form-input>
	 			<form-input
	 				type="select"
	 				v-model="formData.verband"
	 				@change="loadListGroup()"
	 				>
	 					<option value="null" selected>{{ $p.t('lehre/verband') }} </option>
					<option
						v-for="verband in listVerband"
						:key="verband"
						:value="verband"
						>
						{{ verband }}
					</option>
	 			</form-input>
	 			<form-input
	 				type="select"
	 				v-model="formData.gruppe"
	 				>
	 				<option value="null" selected>{{ $p.t('gruppenmanagement/gruppe') }}</option>
						<option
							v-for="group in listGroup"
							:key="group"
							:value="group"
							>
							{{ group }}
						</option>
	 			</form-input>
				<button type="button" class="btn btn-secondary" @click="loadLvPlan">{{ $p.t('LvPlan/loadLvPlan') }}</button>

	 		</form-form>
	 	</div>
	 </div>
	 `,
};

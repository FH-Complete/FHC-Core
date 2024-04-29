import BsAlert from '../../Bootstrap/Alert.js';
import {CoreFetchCmpt} from "../../Fetch.js";

export default {
	components: {
		CoreFetchCmpt
	},
	mixins: [
		BsAlert
	],
	props: {
		footer: Boolean,
		antragId: Number,
		countRemaining: Number
	},
	data(){
		return {
			lvs: null,
			repeat_last: false,
			refresh: true,
			result: false,
			check: false
		};
	},
	computed: {
		lvzugelassen() {
			let zwischen = {};
			for (let k in this.lvs){
				zwischen[k] = this.lvs[k] ? this.lvs[k].filter(lv=>lv.antrag_zugelassen) : null;
			}
			return zwischen;
		},
		lvzugelassenLength() {
			return Object.values(this.lvzugelassen).reduce((result, current) => result + (current ? current.length : 0), 0);
		}
	},
	methods: {
		setlvs(param) {
			if(param.error) {
				this.$refs.fetchCompt.error = true;
				this.$refs.fetchCompt.errorMessage = param.retval;
			} else {
				this.repeat_last = !!param.retval.repeat_last;
				if (this.repeat_last) {
					delete param.retval.repeat_last;
				}
				this.lvs = param.retval;
			}
		},
		loadlvs() {
			if (!this.antragId)
				return new Promise(() => {});
			return axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Antrag/Wiederholung/getLvs/' + this.antragId);
		},
		submit(result) {
			this.result = [result, this.check];
			this.hide();
		}
	},
	watch: {
		antragId() {
			Vue.nextTick(() => {
				this.refresh = !this.refresh;
			});
		}
	},
	popup(msg, options) {
		if (typeof options === 'string')
			options = { default: options };
		return BsAlert.popup.bind(this)(msg, options);
	},
	template: `<bs-modal ref="modalContainer" class="bootstrap-prompt" v-bind="$props" dialog-class="modal-lg">
		<template v-slot:title>
			<slot></slot>
		</template>
		<template v-slot:default>
			<core-fetch-cmpt
				ref="fetchCompt"
				:refresh="refresh"
				:api-function="loadlvs"
				@data-fetched="setlvs">
				<template #default>
					<div v-if="lvzugelassenLength == 0">
						{{$p.t('studierendenantrag','error_no_lvs')}}
					</div>
					<table v-else class="table caption-top" v-for="(lv_arr, sem) in lvzugelassen" :key="sem">
						<caption>
							<span class="d-flex justify-content-between">
								<span>{{ $p.t('studierendenantrag',['title_lv_nicht_zugelassen', 'title_lv_wiederholen'][repeat_last ? 1 : sem.substr(0,1)-1]) }}</span>
								<span>{{sem.substr(1)}}</span>
							</span>
						</caption>
						<thead v-if="lv_arr !== null">
							<tr>
								<th scope="col">{{$p.t('ui','bezeichnung')}}</th>
								<th scope="col">{{$p.t('lehre','lehrform')}}</th>
								<th scope="col">ECTS</th>
								<th scope="col">{{$p.t('lehre','note')}}</th>
								<th scope="col">
									{{$p.t('global','anmerkung')}}
								</th>
							</tr>
						</thead>
						<tbody>
							<tr v-if="lv_arr === null" class="table-warning">
								<td colspan="5">{{$p.t('studierendenantrag/error_stg_last_semester')}}</td>
							</tr>
							<template v-else>
								<tr v-for="lv in lv_arr">
									<td>
										<label class="w-100 m-1" :for="'flexSwitchCheckLv_' + lv.lehrveranstaltung_id">
											{{lv.bezeichnung}}
										</label>
									</td>
									<td>
										<div class="m-1">
											{{lv.lehrform_kurzbz}}
										</div>
									</td>
									<td>
										<div class="m-1">
											{{lv.ects}}
										</div>
									</td>
									<td>
										<div class="m-1">
											{{lv.note || '---'}}
										</div>
									</td>
									<td>
										<div class="m-1">
										{{lv.antrag_anmerkung}}
										</div>
									</td>
								</tr>
							</template>
						</tbody>
					</table>
				</template>
			</core-fetch-cmpt>
		</template>
		<template v-if="footer" v-slot:footer>
			<div v-if="countRemaining > 0" class="form-check flex-grow-1">
				<input ref="check" type="checkbox" class="form-check-input" id="cbid" v-model="check">
				<label class="form-check-label" for="cbid">{{$p.t('studierendenantrag','fuer_x_uebernehmen', {count: countRemaining})}}</label>
			</div>
			<button type="button" class="btn btn-primary" @click="submit(true)">{{$p.t('studierendenantrag','btn_approve')}}</button>
			<button v-if="countRemaining > 0" type="button" class="btn btn-secondary" @click="submit(false)">{{$p.t('ui','skip')}}</button>
		</template>
	</bs-modal>`
}

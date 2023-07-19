import {CoreFetchCmpt} from '../../Fetch.js';
import Phrasen from '../../../mixins/Phrasen.js';

export default {
	components: {
		CoreFetchCmpt
	},
	mixins: [
		Phrasen
	],
	emits: [
		'setInfos',
		'setStatus'
	],
	props: {
		studierendenantragId: Number
	},
	data() {
		return {
			data: null
		}
	},
	computed: {
		statusSeverity() {
			switch (this.data.status)
			{
				case 'Erstellt': return 'info';
				case 'Genehmigt': return 'success';
				default: return 'info';
			}
		}
	},
	methods: {
		load() {
			return axios.get(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Abmeldung/getDetailsForAntrag/' +
				this.studierendenantragId
			).then(
				result => {
					this.data = result.data.retval;
					if (this.data.status) {
						this.$emit("setStatus", {
							msg: this.p.t_ref('studierendenantrag', 'status_x', {status: this.data.statustyp}),
							severity: this.statusSeverity
						});
					}
					return result;
				}
			);
		}
	},
	template: `
	<div class="studierendenantrag-form-abmeldung">
		<core-fetch-cmpt :api-function="load">
			<div class="row">
				<div class="col-12">
					<table class="table">
						<tr>
							<th>{{p.t('lehre', 'studiengang')}}</th>
							<td align="right">{{data.bezeichnung}}</td>
						</tr>
						<tr>
							<th>{{p.t('lehre', 'organisationsform')}}</th>
							<td align="right">{{data.orgform_bezeichnung}}</td>
						</tr>
						<tr>
							<th>{{p.t('projektarbeitsbeurteilung', 'nameStudierende')}}</th>
							<td align="right">{{data.name}}</td>
						</tr>
						<tr>
							<th>{{p.t('person', 'personenkennzeichen')}}</th>
							<td align="right">{{data.matrikelnr}}</td>
						</tr>
						<tr>
							<th>{{p.t('lehre', 'studienjahr')}}</th>
							<td align="right">{{data.studienjahr_kurzbz}}</td>
						</tr>
						<tr>
							<th>{{p.t('lehre', 'semester')}}</th>
							<td align="right">{{data.semester}}</td>
						</tr>
					</table>
				</div>
				<div class="mb-3">
					<h5>{{p.t('studierendenantrag', 'antrag_grund')}}:</h5>
					<pre>{{data.grund}}</pre>
				</div>
			</div>
			<template v-slot:error="{errorMessage}">
				<div class="alert alert-danger m-0" role="alert">
					{{ errorMessage }}
				</div>
			</template>
		</core-fetch-cmpt>
	</div>
	`
}

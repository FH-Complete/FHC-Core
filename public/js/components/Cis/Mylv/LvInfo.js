import { formatDate, formatTime } from "../../../helpers/DateHelpers.js"

export default {
	props: {
		event: Object,
	},
	data() {
		return {

		}
	},
	computed: {
		lektorenLinks: function () {
			if (!this.event || !Array.isArray(this.event.lektor) || !this.event.lektor.length) return "a";

			let lektorenLinks = {};
			this.event.lektor.forEach((lektor) => {
				lektorenLinks[lektor.kurzbz] = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + `/Cis/Profil/View/${lektor.mitarbeiter_uid}`;
			})
			return lektorenLinks;
		},
		getOrtContentLink: function () {
			if (!this.event || !this.event.ort_content_id) return "a";

			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + `/CisVue/Cms/content/${this.event.ort_content_id}`
		},
		start_time: function () {
			return this.event.start ? formatTime(this.event.start) : 'N/A';
		},
		end_time: function () {
			return this.event.end ? formatTime(this.event.end) : 'N/A';
		}
	},
	methods: {
		methodFormatDate: formatDate,
	},
	template:/*html*/`
		<table class="table table-hover mb-4">
				<tbody>
					<tr v-if="event?.datum">
						<th>{{
							$p.t('global','datum')?
							$p.t('global','datum')+':'
							:''
						}}</th>
						<td>{{methodFormatDate(event.datum)}}</td>
					</tr>
					<tr>
						<th>{{
							$p.t('global','raum')?
							$p.t('global','raum')+':'
							:''
						}}</th>
						<td>
							<a v-if="event.ort_content_id" :href="getOrtContentLink"><i class="fa fa-arrow-up-right-from-square me-1 fhc-primary-color" ></i></a>
							{{event.ort_kurzbz}}
						</td>
					</tr>
					<tr>
						<th>{{
							$p.t('lehre','lehrveranstaltung')?
							$p.t('lehre','lehrveranstaltung')+':'
							:''
						}}</th>
						<td>{{'('+event.lehrform+') ' + event.lehrfach_bez}}</td>
					</tr>
					<tr>
						<th>{{
							$p.t('lehre','lektor')?
							$p.t('lehre','lektor')+':'
							:''
						}}</th>
						<td>
							<div v-for="lektor in event.lektor" class="d-block">
								<a v-if="lektorenLinks[lektor.kurzbz]" :href="lektorenLinks[lektor.kurzbz]"><i class="fa fa-arrow-up-right-from-square me-1 fhc-primary-color" ></i></a>
								{{lektor.kurzbz}}
							</div>
						</td>
					</tr>
					<tr>
						<th>{{
								$p.t('ui','zeitraum')?
								$p.t('ui','zeitraum')+':'
								:''
							}}</th>
						<td>{{start_time + ' - ' + end_time}}</td>
					</tr>
					<tr>
						<th>{{
							$p.t('lehre','organisationseinheit')?
							$p.t('lehre','organisationseinheit')+':'
							:''
						}}</th>
						<td>{{event.organisationseinheit}}</td>
					</tr>
				</tbody>
		</table>
	`
}


import { numberPadding, formatDate } from "../../../../helpers/DateHelpers.js"
import LvMenu from "../../Mylv/LvMenu.js";

export default {
	props:{
		event: {
			type: Object,
			required: true,
		},
		lvMenu:{
			type: Object,
			required: false,
			default: null,
		},
	},
	components:{
		LvMenu,
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
			if (!this.event.beginn)
				return 'N/A';
			if (!this.event.beginn instanceof Date) {
				return this.event.beginn;
			}
			return numberPadding(this.event.beginn.getHours()) + ":" + numberPadding(this.event.beginn.getMinutes());
		},
		end_time: function () {
			if (!this.event.ende)
				return 'N/A';
			if (!this.event.ende instanceof Date) {
				return this.event.ende;
			}
			return numberPadding(this.event.ende.getHours()) + ":" + numberPadding(this.event.ende.getMinutes());
		}
	},
	methods: {
		mehtodNumberPadding: function (number) {
			return numberPadding(number);
		},
		methodFormatDate: function (d) {
			return formatDate(d);
		},
	},
	template: `
	<div>
		<h3>
			{{$p.t('lvinfo','lehrveranstaltungsinformationen')}}
		</h3>
		<table class="table table-hover mb-4">
				<tbody>
					<tr>
						<th>{{
							$p.t('global','datum')?
							$p.t('global','datum')+':'
							:''
						}}</th>
						<td>{{methodFormatDate(event.datum)}}</td>
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
							$p.t('global','raum')?
							$p.t('global','raum')+':'
							:''
						}}</th>
						<td>
							<a v-if="event.ort_content_id" :aria-label="$p.t('global','raum')" :title="$p.t('global','raum')" :href="getOrtContentLink"><i class="fa fa-arrow-up-right-from-square me-1" style="color:#00649C" aria-hidden="true"></i></a>
							{{event.ort_kurzbz}}
						</td>
					</tr>
					<tr>
						<th>{{
							$p.t('lehre','lektor')?
							$p.t('lehre','lektor')+':'
							:''
						}}</th>
						<td>
							<div v-for="lektor in event.lektor" class="d-block">
								<a v-if="lektorenLinks[lektor.kurzbz]" :aria-label="$p.t('lehre','lektor')" :tooltip="$p.t('lehre','lektor')" :href="lektorenLinks[lektor.kurzbz]"><i class="fa fa-arrow-up-right-from-square me-1" aria-hidden="true" style="color:#00649C"></i></a>
								{{lektor.kurzbz}}
							</div>
						</td>
					</tr>
				</tbody>
		</table>
		
	</div>`,
}
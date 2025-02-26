import { numberPadding, formatDate } from "../../../helpers/DateHelpers.js"

export default {
	props: {
		event: Object,
	},
	data() {
		return {

		}
	},
	computed: {
		LV_TYPES: function () {
			return {
				lehreinheit: "lehreinheit",
				reservierung: "reservierung",
				moodle: "moodle",
			};
		},
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
			if (!this.event.start) return 'N/A';
			if (!this.event.start instanceof Date) {
				return this.event.start;
			}
			return numberPadding(this.event.start.getHours()) + ":" + numberPadding(this.event.start.getMinutes());
		},
		end_time: function () {
			if (!this.event.end) return 'N/A';
			if (!this.event.end instanceof Date) {
				return this.event.end;
			}
			return numberPadding(this.event.end.getHours()) + ":" + numberPadding(this.event.end.getMinutes());
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
	template:/*html*/`
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
					<template v-if="event.type == LV_TYPES.moodle">
						<th>{{$p.t('global','aktivitaet')}}:</th>
						<td>{{event?.assignment}}</td>
					</template>
					<template v-else>
						<th>{{
							$p.t('global','raum')?
							$p.t('global','raum')+':'
							:''
						}}</th>
						<td>
							<a v-if="event.ort_content_id" :href="getOrtContentLink"><i class="fa fa-arrow-up-right-from-square me-1" style="color:#00649C"></i></a>
							{{event.ort_kurzbz}}
						</td>
					</template>
				</tr>
				<template v-if="event.type != LV_TYPES.moodle">
					<tr>
						<th>{{
							$p.t('lehre','lehrveranstaltung')?
							$p.t('lehre','lehrveranstaltung')+':'
							:''
						}}</th>
						<td>{{'('+event.lehrform+') ' + event.lehrfach_bez}}</td>
					</tr>
				</template>
				<template v-if="event.type != LV_TYPES.moodle">
					<tr>
						<th>{{
							$p.t('lehre','lektor')?
							$p.t('lehre','lektor')+':'
							:''
						}}</th>
						<td>
							<div v-for="lektor in event.lektor" class="d-block">
								<a v-if="lektorenLinks[lektor.kurzbz]" :href="lektorenLinks[lektor.kurzbz]"><i class="fa fa-arrow-up-right-from-square me-1" style="color:#00649C"></i></a>
								{{lektor.kurzbz}}
							</div>
						</td>
					</tr>
				</template>
				<tr>
					<template v-if="event.type == LV_TYPES.moodle">
						<th>{{$p.t('global','typ')}}:</th>
						<td>{{event?.purpose}}</td>
					</template>
					<template v-else>
						<th>{{
							$p.t('ui','zeitraum')?
							$p.t('ui','zeitraum')+':'
							:''
						}}</th>
						<td>{{start_time + ' - ' + end_time}}</td>
					</template>
				</tr>
				<tr>
				    <template v-if="event.type == LV_TYPES.moodle">
						<th>{{$p.t('fristenmanagement','frist')}}:</th>
						<td>{{start_time}}</td>
					</template>
					<template v-else>
						<th>{{
							$p.t('lehre','organisationseinheit')?
							$p.t('lehre','organisationseinheit')+':'
							:''
						}}</th>
						<td>{{event.organisationseinheit}}</td>
					</template>
				</tr>
				<tr v-if="event.type == LV_TYPES.moodle">
				    <th>Link:</th>
					<td><a :href="event?.url" target="_blank"><i class="fa fa-arrow-up-right-from-square me-1"></i></a></td>
				</tr>
			</tbody>
		</table>
	`
}


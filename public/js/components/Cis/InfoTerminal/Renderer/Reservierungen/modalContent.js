import { numberPadding, formatDate } from "../../../../../helpers/DateHelpers.js"
import LvMenu from "../../../Mylv/LvMenu.js";

export default {
	name: 'Cis-Renderer-Reservierung-ModalContent',
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
		start_time: function () {
			if (!this.event.beginn)
				return 'N/A';
			if (!(this.event.beginn instanceof Date)) {
				return this.event.beginn;
			}
			return numberPadding(this.event.beginn.getHours()) + ":" + numberPadding(this.event.beginn.getMinutes());
		},
		end_time: function () {
			if (!this.event.ende)
				return 'N/A';
			if (!(this.event.ende instanceof Date)) {
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
							{{event.ort_kurzbz}}
						</td>
					</tr>
					<tr>
						<th>{{
							$p.t('lehre','gruppe')?
							$p.t('lehre','gruppe')+':'
							:''
						}}</th>
						<td>{{event.gruppe[0]?.kuerzel?.split("/")[0]}}</td>
					</tr>
					<tr>
						<th>{{
							$p.t('lehre','lektor')?
							$p.t('lehre','lektor')+':'
							:''
						}}</th>
						<td>
							<div v-for="lektor in event.lektor" class="d-block">
								{{lektor.kurzbz}}
							</div>
						</td>
					</tr>
				</tbody>
		</table>
		
	</div>`,
}
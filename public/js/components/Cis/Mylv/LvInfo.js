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
					<th>{{
						$p.t('global','raum')?
						$p.t('global','raum')+':'
						:''
					}}</th>
					<td>{{event.ort_kurzbz}}</td>
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
					<td>{{event.lektor.map(lektor=>lektor.kurzbz).join("/")}}</td>
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


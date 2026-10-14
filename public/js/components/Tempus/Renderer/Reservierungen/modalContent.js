import ZeitRow from '../../Modal/ZeitRow.js'
import RaumRow from '../../Modal/RaumRow.js'
import KollisionRow from '../../Modal/KollisionRow.js'
import TagRow from '../../Modal/TagRow.js'
import TeilnehmerRow from '../../Modal/TeilnehmerRow.js'

export default {
	props:{
		event: {
			type: Object,
			required: true,
		},
	},
	components: {
		ZeitRow,
		RaumRow,
		KollisionRow,
		TagRow,
		TeilnehmerRow
	},
	methods: {
		async saveAll() {
			await this.$refs.teilnehmerRow.save();
			this.$refs.zeit.save();
		}
	},
	template: `
	<div>
		
		<h6>
		{{ event.beschreibung }}
		</h6>
		
		<h5>
			{{$p.t('ui','reservierungsinformationen')}}
		</h5>
		
		<table class="table table-hover mb-4">
				<tbody>
					<kollision-row :event="event" />
					<zeit-row ref="zeit" :event="event" />
					<raum-row :event="event" />
					<tag-row :event="event" />
				</tbody>
		</table>
		<teilnehmer-row ref="teilnehmerRow" :event="event" class="mb-3" />
		<button type="button" class="btn btn-primary" @click.stop="saveAll">{{$p.t('ui', 'speichern')}}</button>
	</div>`,
}

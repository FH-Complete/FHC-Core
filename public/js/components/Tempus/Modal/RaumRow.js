import RaumauswahlModal from '../RaumauswahlModal.js';


export default {
	name: 'RaumRow',
	components: {
		RaumauswahlModal
	},
	props: {
		event: {
			type: Object,
			required: true,
		}
	},
	inject: {
		reload: {
			from: 'reload',
			default: () => () => {}
		},
		modalActions: {
			from: 'modalActions',
			default: () => ({})
		},
	},
	computed: {
		orte() {
			const ort = this.event.ort_kurzbz;
			if (Array.isArray(ort))
				return ort;
			return ort ? [ort] : [];
		},
		ortLinks()
		{
			let base_url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

			let ortLinks = {};
			this.event.ort_infos.forEach(ort => {
				if (ort.ort_content_id)
					ortLinks[ort.ort_kurzbz] = `${base_url}/CisVue/Cms/content/${ort.ort_content_id}`;
			})
			return ortLinks;
		},
	},
	methods: {
		openRaumvorschlag() {
			this.$refs.raumModal.show(this.event);
		},
		addToRoomFilter(ort) {
			this.modalActions?.addToFilter({ ort_kurzbz: ort }, 'ort');
		}
	},
	template: /*html*/`
	<tr>
		<th>
			{{ $p.t('global','raum') + ':' }}
			<i
				class="fa-solid fa-refresh"
				role="button"
				:title="$p.t('global','raum')"
				@click="openRaumvorschlag"
			></i>
		</th>
		<td>
			<div v-for="ort in orte" :key="ort" class="d-block">
				<a v-if="ortLinks[ort]" target='_blank' :aria-label="$p.t('lvplan','rauminformationenAnzeigen')" :title="$p.t('lvplan','rauminformationenAnzeigen')" :href="ortLinks[ort]"><i class="fa fa-arrow-up-right-from-square me-1" aria-hidden="true"></i></a>
				<i
					class="fa fa-filter"
					role="button"
					@click="addToRoomFilter(ort)"
				></i>
				{{ ort }}
			</div>
			<div>
				{{event.location}}
			</div>
			<raumauswahl-modal ref="raumModal" @saved="reload()" />
		</td>
	</tr>`
};
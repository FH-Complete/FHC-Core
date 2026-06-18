import AbgabeterminStatusLegende from "./StatusLegende.js";
import {formatDateTime} from "./dateUtils.js";

export const AbgabeStudentTimeline = {
	name: "AbgabeStudentTimeline",
	components: {
		AbgabeterminStatusLegende,
		Timeline: primevue.timeline,
	},
	props: {
		projekte: { type: Array, default: () => [] },
		notenOptions: { type: Array, default: () => [] },
		formatDateFn: { type: Function, required: true }
	},
	data() {
		return {
			legendExpanded: false,
			expandedProjects: {}
		}
	},
	computed: {
		student() {
			return this.projekte?.[0] ?? null
		}
	},
	watch: {
		projekte: {
			immediate: true,
			handler(val) {
				// open all projects by default whenever the student changes
				const state = {}
				val?.forEach(p => { state[p.projektarbeit_id] = true })
				this.expandedProjects = state
				this.legendExpanded = false
			}
		}
	},
	methods: {
		getNoteBezeichnung(projektarbeit) {
			if(projektarbeit.note && this.notenOptions) {
				const noteOpt = this.notenOptions.find(opt => opt.note == projektarbeit.note)
				return noteOpt?.bezeichnung
			} else {
				return ''
			}
		},
		getSavedTerminInfoString(termin) {
			const isUpdate = termin.updateamum != null;

			const fullname = isUpdate
				? termin.updatevon_fullname
				: termin.insertvon_fullname;

			const datetime = isUpdate
				? termin.updateamum
				: termin.insertamum;

			return this.$p.t('ui/savedAtByV3', [formatDateTime(datetime), fullname])
		},
		getItemBezeichnung(item) {
			if (!item?.bezeichnung) return ''
			return item.bezeichnung?.bezeichnung ?? item.bezeichnung
		},
		getItemNote(item) {
			if (!item?.note) return ''
			if (item.note?.bezeichnung) return item.note.bezeichnung
			return this.notenOptions?.find(n => n.note == item.note)?.bezeichnung ?? String(item.note)
		},
		getIconClass(dateStyle) {
			return ({
				verspaetet:              'fa-solid fa-triangle-exclamation',
				verpasst:                'fa-solid fa-calendar-xmark',
				abzugeben:               'fa-solid fa-hourglass-half',
				standard:                'fa-solid fa-clock',
				abgegeben:               'fa-solid fa-paperclip',
				beurteilungerforderlich: 'fa-solid fa-list-check',
				bestanden:               'fa-solid fa-check',
				nichtbestanden:          'fa-solid fa-circle-exclamation',
			})[dateStyle] ?? ''
		},
		getBetreuerLabel(projekt) {
			return projekt.erstbetreuer_full_name
				|| (projekt.betreuer_vorname ? `${projekt.betreuer_vorname} ${projekt.betreuer_nachname}`.trim() : null)
		},
		toggleProject(id) {
			this.expandedProjects[id] = !this.expandedProjects[id]
		}
	},
	template: `
	<div v-if="student">

		<div class="d-flex align-items-baseline gap-2 mb-3 pb-2 border-bottom">
			<span class="fw-bold fs-6">{{ student.student_vorname }} {{ student.student_nachname }}</span>
			<span class="text-muted small">{{ student.student_uid }}</span>
			<span v-if="student.matrikelnr" class="text-muted small ms-auto">{{ student.matrikelnr }}</span>
		</div>

		<div v-for="projekt in projekte" :key="projekt.projektarbeit_id" class="mb-2">

			<button
				class="btn btn-sm w-100 text-start d-flex align-items-center gap-2 rounded bg-light border-0 py-2 px-3"
				@click="toggleProject(projekt.projektarbeit_id)"
			>
				<i
					:class="expandedProjects[projekt.projektarbeit_id] ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'"
					style="width: 12px; flex-shrink: 0;"
				></i>
				<span class="fw-semibold text-truncate flex-grow-1" :title="projekt.titel">
					{{ projekt.titel || projekt.projekttyp_kurzbz || projekt.projektarbeit_id }}
				</span>
				
				<span
					v-if="projekt.note"
					class="small fw-semibold mx-2 flex-shrink-0"
				>
					{{ getNoteBezeichnung(projekt) }}
				</span>
				
				<span class="text-muted small me-1 flex-shrink-0">{{ projekt.studiensemester_kurzbz }}</span>
				<span class="badge bg-secondary flex-shrink-0">{{ projekt.abgabetermine?.length ?? 0 }}</span>
			</button>

			<div v-show="expandedProjects[projekt.projektarbeit_id]" class="px-2 pt-1">

				<div v-if="getBetreuerLabel(projekt)" class="text-muted small px-1 mb-2">
					{{ projekt.betreuerart || $capitalize($p.t('abgabetool/c4erstbetreuerv2')) }}:
					{{ getBetreuerLabel(projekt) }}
				</div>

				<Timeline :value="projekt.abgabetermine" align="right">

					<template #marker="slotProps">
						<!-- padding:0 overrides the 34px accordion left-padding from the -header CSS class -->
						<div
							:class="slotProps.item.dateStyle + '-header'"
							style="height: 26px; width: 26px; padding: 0; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"
						>
							<i :class="getIconClass(slotProps.item.dateStyle)" style="font-size: 0.75rem;"></i>
						</div>
					</template>

					<template #opposite="slotProps">
						<div class="text-end small text-nowrap text-muted">
							{{ formatDateFn(slotProps.item.datum) }}
						</div>
					</template>
					
					<template #content="slotProps">
						<div class="small pb-1">

							<div class="d-flex align-items-center gap-1 fw-semibold">
							
								<div
									:class="slotProps.item.dateStyle + '-header'"
									style="height:20px;width:20px;padding:0;border-radius:3px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"
								>
									<i :class="getIconClass(slotProps.item.dateStyle)" style="font-size:.65rem;"></i>
								</div>
							
								<span>
									{{ getItemBezeichnung(slotProps.item) }}
								</span>
							
								<i
									v-if="slotProps.item.fixtermin"
									class="fa-solid fa-lock text-muted"
									title="Fixtermin"
								></i>
							
								<i
									v-if="slotProps.item.abgabedatum"
									class="fa-solid fa-file text-muted"
									:title="$capitalize($p.t('abgabetool/c4abgabedatum'))"
								></i>
							
								<div class="flex-grow-1 text-end">
									<span
										v-if="slotProps.item.noteBackend?.bezeichnung"
										class="fw-bold"
									>
										{{ slotProps.item.noteBackend?.positiv
											? $capitalize($p.t('abgabetool/c4positivBenotet'))
											: $capitalize($p.t('abgabetool/c4negativBenotet'))
										}}
									</span>
							
									<span
										v-else-if="slotProps.item.bezeichnung?.benotbar"
										class="fw-bold text-muted"
									>
										{{ $capitalize($p.t('abgabetool/c4notYetGraded')) }}
									</span>
								</div>
							</div>
					
							<div
								v-if="slotProps.item.kurzbz"
								class="text-muted fst-italic ms-4"
							>
								{{ slotProps.item.kurzbz }}
							</div>
							
							<div
								v-if="slotProps.item.abgabedatum"
								class="small text-muted ms-4"
							>
								{{ $capitalize($p.t('abgabetool/c4abgabedatum')) }}:
								{{ formatDateFn(slotProps.item.abgabedatum) }}
							</div>
							
							<div
								v-if="slotProps.item.beurteilungsnotiz"
								class="small ms-4 mt-1 text-muted"
							>
								{{ slotProps.item.beurteilungsnotiz }}
							</div>
							
							<div
								v-if="slotProps.item.insertamum"
								class="small text-muted ms-4 mt-1"
								style="font-size: .72rem;"
							>
								<i class="fa-solid fa-clock-rotate-left me-1"></i>
								{{ getSavedTerminInfoString(slotProps.item) }}
							</div>
					
						</div>
					</template>

				</Timeline>
			</div>
		</div>

		<div class="mt-3 border-top pt-2">
			<button
				class="btn btn-link btn-sm p-0 text-muted text-decoration-none d-flex align-items-center gap-1"
				@click="legendExpanded = !legendExpanded"
			>
				<i :class="legendExpanded ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" style="font-size: 0.7rem;"></i>
				Legende
			</button>
			<div v-show="legendExpanded" class="mt-2">
				<AbgabeterminStatusLegende />
			</div>
		</div>

	</div>
	`
}

export default AbgabeStudentTimeline;
export default {
	name: "VerbandSelection",
	props: {
		studiengaenge: {
			type: Array,
			required: true
		},
		studiengaengeAll: {
			type: Array,
			default: () => []
		}
	},
	emits: ['update:studiengaenge'],
	methods: {
		removeStg(stg_kz, semester, orgform_kurzbz)
		{
			if (stg_kz == null)
			{
				this.$emit('update:studiengaenge', []);
			}
			else
			{
				this.$emit('update:studiengaenge', this.studiengaenge.filter(studiengang => !(studiengang.studiengang_kz === stg_kz && studiengang.semester === semester && studiengang.orgform_kurzbz === orgform_kurzbz)));
			}
		},
		stgLabel(stg)
		{
			let found = this.studiengaengeAll.find(s => s.studiengang_kz == stg.studiengang_kz);
			let name = found?.name ?? stg.studiengang_kz;

			if (stg.orgform_kurzbz)
				return `${name} - ${stg.orgform_kurzbz}`;

			return stg.semester ? `${name} - ${stg.semester}. Semester` : name;
		}
	},
	template: `
		<div class="room-selection px-2">
			<div class="d-flex align-items-center justify-content-between">
				<span class="fw-semibold"><i class="fa-solid fa-door-open me-2"></i>Studiengänge</span>
				<button
					type="button"
					class="btn btn-sm btn-link text-danger p-0"
					@click="removeStg(null)"
					title="Alle Studiengänge entfernen"
				>
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<ul class="list-unstyled mb-0">
				<li
					v-for="studiengang in studiengaenge"
					class="d-flex align-items-center justify-content-between"
				>
					<span>{{ stgLabel(studiengang) }}</span>
					<button
						type="button"
						class="btn btn-sm btn-link text-danger p-0"
						@click="removeStg(studiengang.studiengang_kz, studiengang.semester, studiengang.orgform_kurzbz)"
						title="Studiengang entfernen"
					>
						<i class="fa-solid fa-xmark"></i>
					</button>
				</li>
			</ul>
		</div>
	`
}
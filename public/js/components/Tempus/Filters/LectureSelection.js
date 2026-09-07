export default {
	name: "LectureSelection",
	props: {
		lecturers: {
			type: Array,
			required: true
		}
	},
	emits: ['remove'],
	template: `
		<div class="lecture-selection">
			<div class="room-selection px-2" >
				<div class="d-flex align-items-center justify-content-between">
					<span class="fw-semibold"><i class="fa-solid fa-user me-2"></i>Lektoren</span>
					<button
						type="button"
						class="btn btn-sm btn-link text-danger p-0"
						@click="$emit('remove')"
						title="Alle Lektoren entfernen"
					>
						<i class="fa-solid fa-xmark"></i>
					</button>
				</div>
				<div v-for="l in lecturers" :key="l.uid">
					<div class="fw-semibold pt-2 d-flex align-items-center justify-content-between">
						{{ l.label }}
						<button
							type="button"
							class="btn btn-sm btn-link text-danger p-0"
							@click="$emit('remove', l.uid)"
							title="Lektor entfernen"
						>
							<i class="fa-solid fa-xmark"></i>
						</button>
					</div>
			
					<div class="overflow-auto flex-grow-1 pb-2">
						<div class="d-flex align-items-center gap-2" @click="l.showEvents = !l.showEvents" style="cursor:pointer">
							<i :class="l.showEvents ? 'fa-solid fa-toggle-on text-primary' : 'fa-solid fa-toggle-off text-muted'"></i>
							<span class="form-check-label">Plan</span>
						</div>
						<div class="d-flex align-items-center gap-2" @click="l.overlays.blocks = !l.overlays.blocks" style="cursor:pointer">
							<i :class="l.overlays.blocks ? 'fa-solid fa-toggle-on text-primary' : 'fa-solid fa-toggle-off text-muted'"></i>
							<span class="form-check-label">Zeitsperren</span>
						</div>
						<div class="d-flex align-items-center gap-2" @click="l.overlays.wishes = !l.overlays.wishes" style="cursor:pointer">
							<i :class="l.overlays.wishes ? 'fa-solid fa-toggle-on text-primary' : 'fa-solid fa-toggle-off text-muted'"></i>
							<span class="form-check-label">Zeitwünsche</span>
						</div>
						<div class="d-flex align-items-center gap-2" @click="l.showCoursePicker = !l.showCoursePicker" style="cursor:pointer">
							<i :class="l.showCoursePicker ? 'fa-solid fa-toggle-on text-primary' : 'fa-solid fa-toggle-off text-muted'"></i>
							<span class="form-check-label">Course Picker</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	`
}
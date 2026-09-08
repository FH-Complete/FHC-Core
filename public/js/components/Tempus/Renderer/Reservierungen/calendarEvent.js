export default {
	props: {
		event: {
			type: Object,
			required: true
		}
	},
	computed: {
		classes() {
			const classes = ['cis-renderer-reservierungen-calendar-event', 'calendar-event-default', 'h-100', 'w-100', 'p-1'];

			if (this.event.collisions) {
				classes.push('calendar-event-collisions');
			}

			return classes;
		},
		tooltipString() {
			const tooltipArray = [];

			tooltipArray.push([
				this.$p.t('global/uhrzeit'),
				[this.start, this.end].join(' - ')
			].join(": "));

			tooltipArray.push([
				this.$p.t('profilUpdate/topic'),
				this.topicString
			].join(": "));

			tooltipArray.push([
				this.$p.t('person/ort'),
				this.ortString
			].join(": "));

			if (this.gruppeString) {
				tooltipArray.push([
					this.$p.t('lehre/gruppe'),
					this.gruppeString
				].join(": "));
			}

			if (Array.isArray(this.event.lektor) && this.event.lektor.length > 0) {
				if (this.event.lektor.length > 3) {
					tooltipArray.push([
						this.$p.t('lehre/lektor'),
						this.event.lektor.slice(0, 3).map(lektor => lektor.kurzbz).join("\n")
						+ "\n" + this.$p.t('lehre/weitereLektoren', [this.event.lektor.length - 3])
					].join(": "));
				} else {
					tooltipArray.push([
						this.$p.t('lehre/lektor'),
						this.event.lektor.map(lektor => lektor.kurzbz).join("\n")
					].join(": "));
				}
			}

			if (Array.isArray(this.event.teilnehmer_person) && this.event.teilnehmer_person.length > 0) {
				if (this.event.teilnehmer_person.length > 3) {
					tooltipArray.push([
						this.$p.t('lehre/teilnehmer'),
						this.event.teilnehmer_person.slice(0, 3).map(person => `${person.vorname} ${person.nachname}`).join("\n")
						+ "\n" + this.$p.t('lehre/weitereTeilnehmer', [this.event.teilnehmer_person.length - 3])
					].join(": "));
				} else {
					tooltipArray.push([
						this.$p.t('lehre/teilnehmer'),
						this.event.teilnehmer_person.map(person => `${person.vorname} ${person.nachname}`).join("\n")
					].join(": "));
				}
			}

			return tooltipArray.join("\n");
		},
		start() {
			return luxon.Duration
				.fromISOTime(this.event.beginn)
				.toISOTime({ suppressSeconds: true });
		},
		end() {
			return luxon.Duration
				.fromISOTime(this.event.ende)
				.toISOTime({ suppressSeconds: true });
		},
		ortString() {
			let orte = [...(this.event.ort_kurzbz || []), this.event.location?.trim()].filter(Boolean)
			return orte.join(', ')
		},
		topicString() {
			return Array.isArray(this.event.titel) ? this.event.titel.join(', ') : this.event.titel;
		},
		gruppeString() {
			return Array.isArray(this.event.teilnehmer_gruppe)
				? this.event.teilnehmer_gruppe.map(gruppe => gruppe.gruppe_kurzbz).join(', ')
				: this.event.teilnehmer_gruppe;
		},
	},
	template: /* html */`
	<div
		:class="classes"
	>
		<div
			v-if="!event.allDayEvent && event?.beginn && event?.ende"
			class="event-time d-grid h-100"
		>
			<span>{{ start }}</span>
			<span>{{ end }}</span>
		</div>
		<div class="event-text" v-tooltip="tooltipString">
			<span class="event-topic">{{ topicString }}</span>
			<span
				v-for="lektor in event.lektor.slice(0, 3)"
				class="event-lectors"
			>
				{{ lektor.kurzbz }}
			</span>
			<span
				v-if="event.lektor.length > 3"
				class="event-lectors-plus"
			>
				... +{{ event.lektor.length - 3 }}
			</span>
			<span
				v-for="person in (event.teilnehmer_person || []).slice(0, 3)"
				class="event-teilnehmer"
			>
				{{ person.vorname }} {{ person.nachname }}
			</span>
			<span
				v-if="event.teilnehmer_person && event.teilnehmer_person.length > 3"
				class="event-teilnehmer-plus"
			>
				... +{{ event.teilnehmer_person.length - 3 }}
			</span>
			<span
				class="event-place"
				data-cy="calendar-event-room"
			>{{ ortString }}</span>
			<span v-if="gruppeString" class="event-gruppe">{{ gruppeString }}</span>
		</div>
	</div>
	`,
}
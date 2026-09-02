export default {
	props: {
		event: {
			type: Object,
			required: true,
		},
	},
	computed: {
		classes() {
			const classes = [
				'cis-renderer-lehreinheit-calendar-event',
				'calendar-event-default',
				'h-100',
				'w-100',
				'p-1',
			];

			if (this.event.collisions) {
				classes.push('calendar-event-collisions');
			}

			return classes;
		},
		topicString() {
			return Array.isArray(this.event.topic)
				? this.event.topic.join(', ')
				: this.event.topic;
		},
		ortString() {
			let orte = [
				...(this.event.ort_kurzbz || []),
				this.event.location?.trim(),
			].filter(Boolean);
			return orte.join(', ');
		},
		gruppeString() {
			return Array.isArray(this.event.gruppe)
				? this.event.gruppe.map((gruppe) => gruppe.bezeichnung).join(', ')
				: this.event.gruppe;
		},
		tooltipString() {
			const tooltipArray = [];

			tooltipArray.push(
				[this.$p.t('global/uhrzeit'), [this.start, this.end].join(' - ')].join(
					': ',
				),
			);

			tooltipArray.push(
				[this.$p.t('profilUpdate/topic'), this.topicString].join(': '),
			);

			tooltipArray.push([this.$p.t('person/ort'), this.ortString].join(': '));

			if (this.gruppeString) {
				tooltipArray.push(
					[this.$p.t('lehre/gruppe'), this.gruppeString].join(': '),
				);
			}

			if (Array.isArray(this.event.lektor) && this.event.lektor.length > 0) {
				if (this.event.lektor.length > 3) {
					tooltipArray.push(
						[
							this.$p.t('lehre/lektor'),
							this.event.lektor
								.slice(0, 3)
								.map((lektor) => lektor.kurzbz)
								.join('\n') +
								'\n' +
								this.$p.t('lehre/weitereLektoren', [
									this.event.lektor.length - 3,
								]),
						].join(': '),
					);
				} else {
					tooltipArray.push(
						[
							this.$p.t('lehre/lektor'),
							this.event.lektor.map((lektor) => lektor.kurzbz).join('\n'),
						].join(': '),
					);
				}
			}

			return tooltipArray.join('\n');
		},
		start() {
			return luxon.Duration.fromISOTime(this.event.beginn).toISOTime({
				suppressSeconds: true,
			});
		},
		end() {
			return luxon.Duration.fromISOTime(this.event.ende).toISOTime({
				suppressSeconds: true,
			});
		},
		tags() {
			if (typeof this.event.tags === 'string') {
				try {
					return JSON.parse(this.event.tags);
				} catch (e) {
					console.error('Failed to parse tags:', e);
					return [];
				}
			}

			return this.event.tags || [];
		},
		tagsTooltip() {
			if (this.tags.length === 0) {
				return '';
			}

			return this.tags.map((tag) => tag.beschreibung).join('\n');
		},
		resourcesTooltip() {
			if (!this.event.resources || this.event.resources.length === 0) {
				return '';
			}

			let resources = this.event.resources;
			if (typeof this.event.resources === 'string') {
				try {
					resources = JSON.parse(this.event.resources);
				} catch (e) {
					console.error('Failed to parse resources:', e);
					return '';
				}
			}

			return resources.map((resource) => resource.beschreibung).join('\n');
		},
	},
	template: /*html*/ `
	<div
		:class="classes"
		class="position-relative"
		@wheel.stop
	>
		<div v-if="event.has_assigned_resources || tags?.length" class="position-absolute top-0 start-0 m-1 d-flex gap-1" >
			<i
				v-tooltip="resourcesTooltip"
				v-if="event.has_assigned_resources"
			  	class="fa-solid fa-table-list text-muted"
			></i>
			<i 
				v-tooltip="tagsTooltip"
				v-if="tags?.length"
				class="fa-solid fa-tags"
			></i>
		</div>
		<div class="position-absolute bottom-0 start-0 m-1">
			{{event.verplante_stunden}}
		</div>
		<div
			v-if="!event.allDayEvent && event?.beginn && event?.ende"
			class="event-time d-none d-xl-grid h-100"
		>
			<span>{{ start }}</span>
			<span>{{ end }}</span>
		</div>
		<div class="event-text" v-tooltip="tooltipString">
			<span class="event-topic">{{ topicString }}</span>
			<span
				class="event-place"
				data-cy="calendar-event-room"
			>{{ ortString }}</span>
			<span v-if="gruppeString" class="event-gruppe">{{ gruppeString }}</span>
			<span
				v-for="(lektor,index) in event.lektor.slice(0, 3)"
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
		</div>
	</div>
	`,
};

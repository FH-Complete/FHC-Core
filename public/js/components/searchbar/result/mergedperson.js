import TemplateFrame from "./template/frame.js";
import TemplateAction from "./template/action.js";

export default {
	components: {
		TemplateFrame,
		TemplateAction
	},
	emits: [ 'actionexecuted' ],
	props: {
		res: Object,
		actions: Object
	},
	computed: {
		telurl() {
			return 'tel:' + this.employee?.phone;
		},
		person() {
			const person = this.res.list.filter(item => item.type == 'person');
			if (person.length)
				return person.pop();

			// TODO(chris): first one might have not one of these but a later one
			const { person_id, name, photo_url, email } = this.res.list[0];
			return { person_id, name, photo_url, email };
		},
		employee() {
			const ma = this.res.list.filter(item => [
				'employee',
				'unassigned_employee'
			].includes(item.type));
			return ma.length ? ma.pop() : null;
		},
		students() {
			const students = this.res.list.filter(item => item.type == 'prestudent');
			return students.length ? students : null;
		},
		emails() {
			if (Array.isArray(this.person.email))
				return new Set(this.person.email);
			return [this.person.email];
		}
	},
	template: `
	<template-frame
		class="searchbar-result-mergedperson"
		:res="person"
		:actions="actions"
		:title="person.name"
		:image="this.person.photo_url"
		image-fallback="fas fa-user fa-4x"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Person ID</div>
				<div class="searchbar_tablecell">
					{{ person.person_id }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">EMails</div>
				<div class="searchbar_tablecell">
					<a v-for="email in emails" :key="email" :href="'mailto:' + email" class="d-block">
						{{ email }}
					</a>
				</div>
			</div>

			<template v-if="employee">
				<div class="searchbar_tablerow">
					<div class="searchbar_tablecell">
						<template-action
							:res="employee"
							:action="actions.defaultactionemployee || actions.defaultaction"
							@actionexecuted="$emit('actionexecuted')"
							>
							Mitarbeiter
						</template-action>
					</div>
				</div>
				<div class="searchbar_tablerow">
					<div class="searchbar_tablecell ps-3">Standard-Kostenstelle</div>
					<div class="searchbar_tablecell">
						<ul class="searchbar_inline_ul" v-if="employee.standardkostenstelle.length > 0">
							<li
								v-for="(stdkst, idx) in employee.standardkostenstelle"
								:key="idx"
								>
								{{ stdkst }}
							</li>
						</ul>
						<span v-else="">keine</span>
					</div>
				</div>
				<div class="searchbar_tablerow">
					<div class="searchbar_tablecell ps-3">Organisations-Einheit</div>
					<div class="searchbar_tablecell">
						<ul class="searchbar_inline_ul" v-if="employee.organisationunit_name.length > 0">
							<li
								v-for="(oe, idx) in employee.organisationunit_name"
								:key="idx"
								>
								{{ oe }}
							</li>
						</ul>
						<span v-else="">keine</span>
					</div>
				</div>
				<div class="searchbar_tablerow">
					<div class="searchbar_tablecell ps-3">Telefon</div>
					<div class="searchbar_tablecell">
						<a :href="telurl">
							{{ employee.phone }}
						</a>
					</div>
				</div>
			</template>

			<template v-if="students">
				<template v-for="student in students">
					<div class="searchbar_tablerow">
						<div class="searchbar_tablecell">
							<template-action
								v-if="actions.defaultaction"
								:res="student"
								:action="actions.defaultactionstudent || actions.defaultaction"
								@actionexecuted="$emit('actionexecuted')"
								>
								{{ student.status }} ({{ student.stg_kuerzel }})
							</template-action>
						</div>
					</div>
					<div class="searchbar_tablerow">
						<div class="searchbar_tablecell ps-3">Studiengang</div>
						<div class="searchbar_tablecell">
							{{ student.bezeichnung }}
						</div>
					</div>
					<div class="searchbar_tablerow">
						<div class="searchbar_tablecell ps-3">Prestudent ID</div>
						<div class="searchbar_tablecell">
							{{ student.prestudent_id }}
						</div>
					</div>
					<div v-if="student.uid" class="searchbar_tablerow">
						<div class="searchbar_tablecell ps-3">Student UID</div>
						<div class="searchbar_tablecell">
							{{ student.uid }}
						</div>
					</div>
					<div v-if="student.matrikelnr" class="searchbar_tablerow">
						<div class="searchbar_tablecell ps-3">Matrikelnummer</div>
						<div class="searchbar_tablecell">
							{{ student.matrikelnr }}
						</div>
					</div>
				</template>
			</template>
		</div>
	</template-frame>`
};
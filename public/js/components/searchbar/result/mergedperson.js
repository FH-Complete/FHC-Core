import TemplateFrame from "./template/frame.js";
import TemplateAction from "./template/action.js";

export default {
	name: 'SearchbarResultMergedperson',
	components: {
		TemplateFrame,
		TemplateAction
	},
	emits: [ 'actionexecuted' ],
	props: {
		mode: String,
		res: Object,
		actions: Object
	},
	computed: {
		person() {
			 // Cummulate all emails
			const email = this.res.list.reduce((a, c) => [...a, ...(Array.isArray(c.email) ? c.email : [c.email])], []);

			// Use person entry if available (with cummulated emails)
			const person = this.res.list.find(item => item.type == 'person');
			if (person)
				return {...person, email};

			 // Those properties should be the same in all entries
			const { person_id, name } = this.res.list[0];
			 // Get first photo (prefer student photo if available)
			let photo_url;
			if (this.mode == 'simple') {
				let foto = (this.students ? this.students.find(el => el.foto) : null)?.foto;
				if (foto)
					foto = 'data:image/jpeg;base64,' + foto;
				photo_url = foto || this.employee?.photo_url;
			} else
				photo_url = ((this.students ? this.students.find(el => el.photo_url) : null) || this.employee)?.photo_url;

			return { person_id, name, photo_url, email };
		},
		employee() {
			return this.res.list.find(item => [
				'employee',
				'unassigned_employee',
				'mitarbeiter',
				'mitarbeiter_ohne_zuordnung'
			].includes(item.type)) || null;
		},
		students() {
			const students = this.res.list.filter(item => [
					'student',
					'prestudent',
					'studentcis',
					'studentStv'
				].includes(item.type))
				.filter((item, idx, arr) => {
					if (item.type === 'prestudent') {
						return true;
					}

					let prestudentwithsameuidexists = arr.some(tmpitem => {
						return tmpitem.uid === item.uid && tmpitem.type === 'prestudent';
					});

					if (prestudentwithsameuidexists) {
						return false;
					}
					return true;
				}).sort((a, b) => (a.sort || 0) - (b.sort || 0));
			return students.length ? students : null;
		},
		emails() {
			// Remove duplicates
			return new Set(this.person.email);
		},
		telurl() {
			return 'tel:' + this.employee?.phone;
		},
		inaktiv() {
			return this.res.list.some(item => item?.aktiv === false);
		}
	},
	template: `
	<template-frame
		class="searchbar-result-mergedperson"
		:class="(inaktiv) ? 'searchbar_inaktiv' : ''"
		:res="person"
		:actions="actions"
		:title="person.name"
		:image="person.photo_url"
		image-fallback="fas fa-user fa-4x"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('person/person_id') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					{{ person.person_id }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('search/result_emails') }}</div>
				<div class="searchbar_tablecell searchbar_value">
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
							{{ $p.t('search/result_employee') }}
						</template-action>
					</div>
				</div>
				<div class="searchbar_tablerow">
					<div class="searchbar_tablecell searchbar_label ps-3">{{ $p.t('search/result_stdkst') }}</div>
					<div class="searchbar_tablecell searchbar_value">
						<ul class="searchbar_inline_ul" v-if="employee.standardkostenstelle.length > 0">
							<li
								v-for="(stdkst, idx) in employee.standardkostenstelle"
								:key="idx"
								>
								{{ stdkst }}
							</li>
						</ul>
						<span v-else="">{{ $p.t('search/result_stdkst_none') }}</span>
					</div>
				</div>
				<div class="searchbar_tablerow">
					<div class="searchbar_tablecell searchbar_label ps-3">{{ $p.t('lehre/organisationseinheit') }}</div>
					<div class="searchbar_tablecell searchbar_value">
						<ul class="searchbar_inline_ul" v-if="employee.organisationunit_name.length > 0">
							<li
								v-for="(oe, idx) in employee.organisationunit_name"
								:key="idx"
								>
								{{ oe }}
							</li>
						</ul>
						<span v-else="">{{ $p.t('search/result_oe_none') }}</span>
					</div>
				</div>
				<div class="searchbar_tablerow">
					<div class="searchbar_tablecell searchbar_label ps-3">{{ $p.t('person/telefon') }}</div>
					<div class="searchbar_tablecell searchbar_value">
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
								<template v-if="mode == 'simple'">
									{{ student.studiengang_kz }}
								</template>
								<template v-else-if="student.status && student.stg_kuerzel">
									{{ student.status }} ({{ student.stg_kuerzel }})
								</template>
								<template v-else>
									{{ $p.t('person/student') }}
								</template>
							</template-action>
						</div>
					</div>
					<div v-if="student.bezeichnung" class="searchbar_tablerow">
						<div class="searchbar_tablecell searchbar_label ps-3">{{ $p.t('lehre/studiengang') }}</div>
						<div class="searchbar_tablecell searchbar_value">
							{{ student.bezeichnung }} {{ student.orgform ? '(' + student.orgform + ')' : '' }}
						</div>
					</div>
					<div v-if="student.prestudent_id" class="searchbar_tablerow">
						<div class="searchbar_tablecell searchbar_label ps-3">{{ $p.t('search/result_prestudent_id') }}</div>
						<div class="searchbar_tablecell searchbar_value">
							{{ student.prestudent_id }}
						</div>
					</div>
					<div v-if="student.uid" class="searchbar_tablerow">
						<div class="searchbar_tablecell searchbar_label ps-3">{{ $p.t('search/result_student_uid') }}</div>
						<div class="searchbar_tablecell searchbar_value">
							{{ student.uid }}
						</div>
					</div>
					<div v-if="student.matrikelnr" class="searchbar_tablerow">
						<div class="searchbar_tablecell searchbar_label ps-3">{{ $p.t('person/matrikelnummer') }}</div>
						<div class="searchbar_tablecell searchbar_value">
							{{ student.matrikelnr }}
						</div>
					</div>
				</template>
			</template>
		</div>
	</template-frame>`
};
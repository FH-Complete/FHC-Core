import BaseTreemenu from '../../Base/Treemenu.js';

import ApiStvGroups from '../../../api/factory/stv/group.js';
import ApiStvDetails from '../../../api/factory/stv/details.js';

export default {
	components: {
		BaseTreemenu
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			default: () => {}
		},
		currentSemester: {
			from: 'currentSemester',
			required: true
		},
		appConfig: {
			from: 'appConfig',
			default: {
				number_displayed_past_studiensemester: 5
			}
		}
	},
	emits: [
		'selectVerband'
	],
	props: {
		preselectedKey: {
			type: String,
			default: null
		}
	},
	watch: {
		'appConfig.number_displayed_past_studiensemester'(newVal, oldVal) {
			if (oldVal !== undefined && this.$refs.menu) {
				this.$refs.menu.reloadNodesWithProp('no_sem_reload');
			}
		}
	},
	methods: {
		onSelectTreeNode(node) {
			if (node.link)
				this.$emit('selectVerband', {link: node.link, studiengang_kz: node.stg_kz, semester: node.semester, orgform_kurzbz: node.orgform_kurzbz});
		},
		getStudentAjaxId(student) {
			let res = student.id;
			if (student.vorname && student.nachname)
				res += ' (' + student.vorname + ' ' + student.nachname + ')';
			return res;
		},
		onDrop({ drag, drop }) {
			let endpoint;

			if (drop.gruppe_kurzbz) {
				endpoint = drag.map(student => [
					this.getStudentAjaxId(student),
					ApiStvGroups.add(
						student.id,
						drop.gruppe_kurzbz,
						this.currentSemester
					)
				]);
			} else {
				const { semester, verband, gruppe } = drop;
				const params = { semester, verband, gruppe };
				endpoint = drag.map(student => [
					this.getStudentAjaxId(student),
					ApiStvDetails.saveStudent(
						student.id,
						this.currentSemester,
						params
					)
				]);
			}

			return this.$api
				.call(endpoint)
				.then(this.$reloadList)
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	template: /* html */`
	<div class="overflow-auto" tabindex="-1">
		<base-treemenu
			ref="menu"
			config="stv"
			:preselected-key="preselectedKey"
			@select-entry="onSelectTreeNode"
			@drop="onDrop"
		/>
	</div>`
};

const LOCAL_STORAGE_ID = 'studierendenantrag_leitung_2023-11-14_header_filter';

export default {
	props: {
		stgs: Array
	},
	emits: [
		'input'
	],
	data() {
		return {
			todo_value: '',
			stg_value: ''
		}
	},
	computed: {
		value() {
			const a = [];
			if (this.todo_value)
				a.push(this.todo_value);
			if (this.stg_value)
				a.push(this.stg_value);

			return a.join('/');
		}
	},
	watch: {
		value(n) {
			window.localStorage.setItem(LOCAL_STORAGE_ID, n);
			this.$emit('input', n);
		}
	},
	created() {
		var values = 'todo';
		const savedPath = window.localStorage.getItem(LOCAL_STORAGE_ID);
		if (savedPath !== null) {
			values = savedPath;
		}

		values = values.split('/');

		if (values.length) {
			if (values.length == 1) {
				if (values[0] == 'todo')
					values.push('');
				else
					values.unshift('');
			}
			this.stg_value = values.pop();
			this.todo_value = values.pop();
		}
	},
	template: `
	<div class="studierendenantrag-leitung-header fhc-table-header d-flex align-items-center mb-2 gap-2">
		<h3 class="h5 col m-0">{{$p.t('studierendenantrag', 'studierendenantraege')}}</h3>
		<div class="col-auto row row-cols-lg-auto g-3 align-items-center">
			<div class="col-12">
				<select class="form-select" v-model="todo_value">
					<option value="todo">{{$p.t('studierendenantrag', 'filter_todo')}}</option>
					<option value="">{{$p.t('studierendenantrag', 'filter_all')}}</option>
				</select>
			</div>
			<div class="col-12">
				<select v-if="stgs.length > 1" class="form-select" v-model="stg_value">
					<option value="">{{$p.t('global', 'alle')}}</option>
					<option v-for="stg in stgs" :key="stg.studiengang_kz" :value="stg.studiengang_kz">
						{{stg.bezeichnung}}
					</option>
				</select>
			</div>
		</div>
	</div>
	`
}

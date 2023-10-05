import {CoreRESTClient} from '../../../RESTClient.js';

export default {
	emits: [
		'changed'
	],
	props: {
		default: {
			type: String,
			default: ''
		}
	},
	data() {
		return {
			current: 0,
			loading: true,
			list: []
		};
	},
	methods: {
		set(n) {
			this.loading = true;
			let fallback = this.current;
			this.current = n;
			this.save(fallback);
		},
		next() {
			this.loading = true;
			let fallback = this.current;
			if (this.current++ >= this.list.length)
				this.current = this.list.length - 1;
			this.save(fallback);
		},
		prev() {
			this.loading = true;
			let fallback = this.current;
			if (this.current-- < 0)
				this.current = 0;
			this.save(fallback);
		},
		save(fallback) {
			CoreRESTClient
				.post('components/stv/studiensemester/set', {
					studiensemester: this.list[this.current]
				})
				.then(() => {
					this.loading = false;
					this.$emit('changed');
				})
				.catch(() => {
					this.current = fallback;
					// TODO(chris): emit error
				});
		}
	},
	created() {
		CoreRESTClient
			.get('components/stv/studiensemester')
			.then(result => result.data)
			.then(result => {
				this.list = result.map(el => el.studiensemester_kurzbz);
				this.loading = false;
				this.current = this.list.indexOf(this.default);
			})
			.catch(error => {
				console.error(error);
			});
	},
	template: `
	<div class="stv-studiensemester">
		<div class="btn-group w-100 dropup" role="group" aria-label="Basic example">
			<button type="button" class="btn btn-outline-secondary flex-grow-0" @click="prev"><i class="fa-solid fa-caret-left"></i></button>
			<button type="button" class="btn btn-outline-secondary" data-bs-toggle="dropdown" data-bs-display="static" data-bs-offset="0,0" aria-expanded="false">
				{{list[current]}}
				<i v-if="loading" class="fa-solid fa-spinner fa-spin"></i>
			</button>
			<ul class="dropdown-menu dropdown-menu-dark overflow-auto w-100" style="max-height:60vh">
				<li v-for="(item, index) in list" :key="index">
					<a class="dropdown-item" :class="{active: index == current}" :aria-current="index == current ? 'true' : 'false'" href="#" @click="set(index)">{{item}}</a>
				</li>
			</ul>
			<button type="button" class="btn btn-outline-secondary flex-grow-0" @click="next"><i class="fa-solid fa-caret-right"></i></button>
		</div>
	</div>
	`
}
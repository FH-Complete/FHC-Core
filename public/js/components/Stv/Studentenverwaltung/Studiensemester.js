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
			list: [],
			today: -1
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
		setToToday() {
			if (this.today > 0) {
				if (this.today == this.current)
					return;
				return this.set(this.today);
			}

			this.loading = true;
			CoreRESTClient
				.get('components/stv/studiensemester/now')
				.then(result => CoreRESTClient.getData(result.data))
				.then(result => {
					this.today = this.list.indexOf(result);
					if (this.today >= 0) {
						if (this.today != this.current)
							this.set(this.today);
					} else {
						// TODO(chris): handle error (list might not be loaded yet)
					}
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		save(fallback) {
			CoreRESTClient
				.post('components/stv/studiensemester/set', {
					studiensemester: this.list[this.current]
				})
				.then(() => {
					this.loading = false;
					this.$emit('changed', this.list[this.current]);
				})
				.catch(error => {
					this.current = fallback;
					this.loading = false;
					this.$fhcAlert.handleFormValidation(error);
				});
		}
	},
	created() {
		CoreRESTClient
			.get('components/stv/studiensemester')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.list = result.map(el => el.studiensemester_kurzbz);
				this.loading = false;
				this.current = this.list.indexOf(this.default);
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-studiensemester">
		<div class="btn-toolbar w-100 dropup" role="toolbar" aria-label="Studiensemester">
			<div class="btn-group flex-grow-1 position-static" role="group" aria-label="Studiensemester einstellen">
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
			<div class="btn-group flex-grow-0" role="group" aria-label="aktuelles Studiensemester">
				<button type="button" class="btn btn-outline-secondary" @click="setToToday"><i class="fa-solid fa-bullseye"></i></button>
			</div>
		</div>
	</div>
	`
}
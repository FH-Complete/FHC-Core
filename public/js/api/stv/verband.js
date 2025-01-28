export default {
	get() {
		return this.$fhcApi.get('api/frontend/v1/stv/verband');
	},
	favorites: {
		get() {
			return this.$fhcApi.get('api/frontend/v1/stv/favorites');
		},
		set(favorites) {
			return this.$fhcApi.post('api/frontend/v1/stv/favorites/set', {
				favorites
			});
		}
	}
}
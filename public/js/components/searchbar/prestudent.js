import action from "./action.js";
import actions from "./actions.js";

export default {
	props: [ "res", "actions" ],
	components: {
		action: action,
		actions: actions
	},
	emits: [ 'actionexecuted' ],
	template: `
		<div class="searchbar_result searchbar_prestudent">
		
			<div class="searchbar_grid">
				<div class="searchbar_icon">
					<action :res="this.res" :action="this.actions.defaultaction" @actionexecuted="$emit('actionexecuted')">
						<img v-if="(typeof res.foto !== 'undefined') && (res.foto !== null)"
							 :src="'data:image/jpeg;base64,' + res.foto"
							 class="rounded-circle" height="100"/>
						<i v-else class="fas fa-user-circle fa-5x"></i>
					</action>
				</div>
		
				<div class="searchbar_data">
					<action :res="this.res" :action="this.actions.defaultaction" @actionexecuted="$emit('actionexecuted')">
						<span class="fw-bold">{{ res.name }}</span>
					</action>
		
					<div class="mb-3"></div>
		
					<div class="searchbar_table">
		
						<div class="searchbar_tablerow">
							<div class="searchbar_tablecell searchbar_label">Prestudent_id</div>
							<div class="searchbar_tablecell searchbar_value">
								{{ res.prestudent_id }}
							</div>
						</div>
		
						<div class="searchbar_tablerow">
							<div class="searchbar_tablecell searchbar_label">Student_uid</div>
							<div class="searchbar_tablecell searchbar_value">
								{{ res.uid }}
							</div>
						</div>
		
						<div class="searchbar_tablerow">
							<div class="searchbar_tablecell searchbar_label">Person_id</div>
							<div class="searchbar_tablecell searchbar_value">
								{{ res.person_id }}
							</div>
						</div>
		
						<div class="searchbar_tablerow">
							<div class="searchbar_tablecell searchbar_label">Studiengang</div>
							<div class="searchbar_tablecell searchbar_value">
								{{ res.bezeichnung }}
							</div>
						</div>
		
						<div class="searchbar_tablerow">
							<div class="searchbar_tablecell searchbar_label">EMail</div>
							<div class="searchbar_tablecell searchbar_value">
								<a :href="this.mailtourl">
									{{ res.email }}
								</a>
							</div>
						</div>
		
		
					</div>
		
					<actions :res="this.res" :actions="this.actions.childactions"
							 @actionexecuted="$emit('actionexecuted')"></actions>
		
				</div>
			</div>
		
		</div>
    `,
	methods: {
	},
	computed: {
		mailtourl: function() {
			return 'mailto:' + this.res.email;
		}
	}
};
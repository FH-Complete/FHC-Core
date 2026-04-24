import Phrasen from "../../../../../mixins/Phrasen.js";
import ApiLehre from "../../../../../api/factory/lehre.js";

export default {
	mixins: [
		Phrasen
	],
	props: {
		semesterInfo: String,
	},
	data: ( ) =>{
		return {
			gradeAverage: null,
			gradeWeightedAverage: null,
		}
	},
	methods: {
		async fetchAverageGrade() {
			this.gradeAverage = null;
			this.gradeWeightedAverage = null;
			if (!this.$props.semesterInfo) return;

			let gradeAverageResponse = await this.$api.call(
				ApiLehre.getSemesterAverageGrade(this.$props.semesterInfo),
			);
			const gradeAverageResponseData = gradeAverageResponse.data;
			this.gradeAverage =
				gradeAverageResponseData.average_grade?.toFixed(2);
			this.gradeWeightedAverage =
				gradeAverageResponseData.weighted_average_grade?.toFixed(2);
		},
	},
	watch: {
		semesterInfo() {
			this.fetchAverageGrade();
		},
	},
	async created() {
		await this.fetchAverageGrade();
	},
	template: /*html*/`
	<div class="card mylv-semester-studiengang-grades">

		<div class="card-header text-center">
			<h6>{{$p.t('lehre/notenstatistik')}}</h6>
		</div>

		<div v-if="gradeAverage && gradeWeightedAverage">
			<table class="card-body table w-auto mx-auto">
			  <tbody>
				<tr>
				  <td class="text-end">
					{{$p.t('lehre/headerAverage')}}
				  </td>
				  <td class="text-start">
					{{ gradeAverage }}
				  </td>
				</tr>
				<tr>
				  <td class="text-end">
					{{$p.t('lehre/headerWeightedAverage')}}
				  </td>
				  <td class="text-start">
					{{ gradeWeightedAverage }}
				  </td>
				</tr>
			  </tbody>
			</table>
		</div>
		<div v-else class="card-body text-center">
			<p>{{$p.t('lehre/info_noGradesYet')}}</p>
		</div>

		<div v-if="gradeAverage && gradeWeightedAverage" class="card-footer d-flex align-items-start text-muted small">
		  <i class="fa fa-circle-info me-2 mt-1"></i>
		  <div>
			<strong>{{$p.t('ui', 'hinweis')}}</strong><br>
			* {{$p.t('lehre/noticeAverage')}}
			<br>
			** {{$p.t('lehre/noticeWeightedAverage')}}
		  </div>
		</div>

	</div>
	`
}

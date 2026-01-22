import Phrasen from "../../../../../mixins/Phrasen.js";

export default {
	mixins: [
		Phrasen
	],
	props: {
		lvs: Array,
	},
	data: ( ) =>{
		return {
			gradeAverage: null,
			gradeWeightedAverage: null,
			existingGrades: false
		}
	},
	methods: {
		calculateAverages(){
			let sum = 0;
			let count = 0;
			let sumWeighted = 0;
			let sumEcts = 0;

			this.lvs.forEach((lv) => {
				if ((lv.znote >= 1 && lv.znote <= 5) && lv.znote!= null) {
					this.existingGrades = true;
					sum+= lv.znote;
					count++;
					sumWeighted += lv.znote * Number(lv.ects);
					sumEcts += Number(lv.ects);
				}
			});
			this.gradeAverage = (sum/count).toFixed(2);
			this.gradeWeightedAverage = (sumWeighted/sumEcts).toFixed(2);
		}
	},
	watch: {
		lvs: {
			handler() {
				this.calculateAverages();
			},
			deep: true,
			immediate: true
		}
	},
	mounted(){
		this.calculateAverages();
	},
	template: /*html*/`
	<div class="card mylv-semester-studiengang-grades">

		<div class="card-header text-center">
			<h6>{{$p.t('lehre/notenstatistik')}}</h6>
		</div>

		<div v-if="existingGrades">
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

		<div v-if="existingGrades" class="card-footer d-flex align-items-start text-muted small">
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

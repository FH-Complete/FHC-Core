import FhcCalendar from "../../components/Calendar/Calendar.js";
import CalendarDate from "../../composables/CalendarDate.js";
import FhcApi from "../../plugin/FhcApi.js";

const app = Vue.createApp({
	components: {
		FhcCalendar
	},
	data() {
		return {
			stunden: [],
			events: null,
            calendarWeek: new CalendarDate(new Date()),
        }
	},
    computed:{
        currentDate: function(){
            return new Date(this.calendarWeek.y, this.calendarWeek.m, this.calendarWeek.d);
        },
    },
	created() {

        this.$fhcApi.factory.stundenplan.getStunden().then(res =>{
            res.data.forEach(std => {
				this.stunden[std.stunde] = std; // TODO(chris): geht besser
			});

            this.$fhcApi.factory.stundenplan.getRoomInfo('EDV_A6.09', this.calendarWeek.firstDayOfWeek, this.calendarWeek.lastDayOfWeek).then(res =>{
                let events;
                if (res.data && res.data.forEach) {
                    res.data.forEach((el, i) => {

                        el.id = i;
                        el.color = '#' + (el.farbe || 'CCCCCC');
                        el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
                        el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
                        el.title = el.lehrfach;
                        if (el.lehrform)
                            el.title += '-' + el.lehrform;
                    });
                    
                    this.events = res.data;
                }
            })
        });

	},
    template: /*html*/`
    <div>
        <fhc-calendar v-slot="{event}" :initialDate="currentDate" :events="events" initial-mode="week" show-weeks>
            <div class="d-flex flex-column align-items-center justify-content-evenly h-100">
                <span>{{event.orig.lv_info}}</span>	
                <span>{{event.orig.stg}}</span>
                <span>{{event.orig.lektor}}</span>
			</div>
        </fhc-calendar>
    </div>
    `,
});
app.config.unwrapInjectedRef = true;
app.use(FhcApi);
app.mount('#content');
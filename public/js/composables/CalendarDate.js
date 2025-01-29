import {user_locale} from "../plugin/Phrasen.js";
import CalendarDates from "./CalendarDates.js";

class CalendarDate {
	constructor(y, m, d) {
		this.weekStart = CalendarDate.getWeekStart();
		this.watchLocale = Vue.watch(
			user_locale,
			(newLocale, oldLocale, onCleanup) =>{
				this.weekStart = CalendarDate.getWeekStart();
				this._clean();
				onCleanup((cleanup)=>{
					// do clean up
				});
			},
			
		);
		this.set(y, m, d);
		this._clean();
		CalendarDates.subscribe(this);
	}
	get y() { return this._y }
	set y(v) { this._y = v; this._clean() }
	get m() { return this._m }
	set m(v) { this._m = v; this._clean() }
	get d() { return this._d }
	set d(v) { this._d = v; this._clean() }
	/**
	 * @see https://www.smart-rechner.de/kalenderwochen/rechner.php
	 */
	get w() {
		if (this._w === null) {
			if (this.weekStart == 1 && this._m == 11 && this._d > 28 && this.wd <= this._d-29) {
				this._w = 1;
			} else if (this.weekStart == 1 && this._m == 0 && this._d < 4 && 3-this.wd <= -this._d) {
				let weekStartOfTheYear = new Date(this.y-1, 0, this.weekStart == 1 ? 4 : 1);
				weekStartOfTheYear.setDate(weekStartOfTheYear.getDate() - (weekStartOfTheYear.getDay() + 7 - this.weekStart)%7);
				this._w = Math.ceil((Math.floor((new Date(this.y, this.m, this.d) - weekStartOfTheYear) / 86400000) + 1) / 7);
			} else {
				let weekStartOfTheYear = new Date(this.y, 0, this.weekStart == 1 ? 4 : 1);
				weekStartOfTheYear.setDate(weekStartOfTheYear.getDate() - (weekStartOfTheYear.getDay() + 7 - this.weekStart)%7);
				this._w = Math.ceil((Math.floor((new Date(this.y, this.m, this.d) - weekStartOfTheYear) / 86400000) + 1) / 7);
			}
		}
		return this._w;
	}
	set w(v) {
		if (this.w != v) {
			let lw = this.numWeeks;
			
			this.d += (v - this.w) * 7;

			if (v > 0 && v <= lw && this.w != v) {
				if (this.weekStart != 1) {
					if (this.w == 1) {
						this.set(this.firstDayOfWeek);
					} else {
						this.set(this.lastDayOfWeek);
					}
				}
				if (this.w != v) {
					console.error('couldn\'t set the week', this, v);
				}
			}
		}
	}
	get wYear() {
		if( this.w === 1 ) {
			return this.cdLastDayOfWeek.format({ year: 'numeric' });
		}
		return this.cdFirstDayOfWeek.format({ year: 'numeric' });
	}
	get wd() {
		if (this._wd === null) {
			// the .getDay() method from js Date object ALWAYS returns values from 0 to 6, where 0 is Sunday and 6 is Saturday
			// aligns the getDay() result of the Date to the weekStart of the CalendarDate
			this._wd = ((new Date(this.y, this.m, this.d)).getDay()+7-this.weekStart)%7;
		}
		return this._wd;
	}
	get firstDayOfWeek() {
		let firstDayOfWeek = new Date(this.y, this.m, this.d);
		// to ensure that firstDayOfWeek.getDay() is always greater than this.weekStart we add 7 and wrap the result around with %7 to avoid negative numbers
		firstDayOfWeek.setDate(this.d -(firstDayOfWeek.getDay()+7-this.weekStart)%7);
		return firstDayOfWeek;
	}
	get cdFirstDayOfWeek() {
		let FirstDayOfWeek = new CalendarDate(this.firstDayOfWeek);
		return FirstDayOfWeek;
	}
	get lastDayOfWeek() {
		let lastDayOfWeek = new Date(this.y, this.m, this.d);
		// uses the calculation from firstDayOfWeek and adds 6 days to the result to get the last day of the week
		lastDayOfWeek.setDate(this.d -(lastDayOfWeek.getDay()+7-this.weekStart)%7 +6);
		return lastDayOfWeek;
	}
	get wholeWorkWeek() {
		const days = []
		const date = new Date(this.y, this.m, this.d);
		for(let i = 0; i < 5; i++) {
			days[i] = new Date(this.y, this.m, this.d)
			days[i].setDate(this.d -(date.getDay()+7-this.weekStart)%7 + i)
		}
		return days
	}
	get cdLastDayOfWeek() {
		let LastDayOfWeek = new CalendarDate(this.lastDayOfWeek);
		return LastDayOfWeek;
	}
	get firstDayOfCalendarMonth() {
		let firstDayOfMonth = new Date(this.y, this.m, 1);
		let offset = (firstDayOfMonth.getDay() + 7 - this.weekStart) % 7;
		// offset will be greater than 1 most of the time, using a negative number for a date returns a date in the past
		return new Date(this.y, this.m, 1-offset);
	}
	get cdFirstDayOfCalendarMonth() {
		let firstDayOfMonth = new Date(this.y, this.m, 1);
		let offset = (firstDayOfMonth.getDay() + 7 - this.weekStart) % 7;
		let FirstDayOfCalendarMonth = new CalendarDate(this.y, this.m, 1 - offset);
		return FirstDayOfCalendarMonth;
	}
	get lastDayOfCalendarMonth() {
		// In JavaScript, the Date constructor interprets: A day of 0 as the last day of the previous month 
		let lastDayOfMonth = new Date(this.y, this.m+1, 0);
		let offset = (lastDayOfMonth.getDay() + 7 - this.weekStart) % 7;
		return new Date(lastDayOfMonth.getFullYear(), lastDayOfMonth.getMonth(), lastDayOfMonth.getDate()+6-offset);
	}
	get cdLastDayOfCalendarMonth() {
		let lastDayOfMonth = new Date(this.y, this.m+1, 0);
		let offset = (lastDayOfMonth.getDay() + 7 - this.weekStart) % 7;
		let LasyDayOfCalendarMonth = new CalendarDate(lastDayOfMonth.getFullYear(), lastDayOfMonth.getMonth(), lastDayOfMonth.getDate() + 6 - offset);
		return LasyDayOfCalendarMonth;
	}
	get cdLastDayOfNextCalendarMonth() {
		let lastDayOfMonth = new Date(this.y, this.m+1, 0);
		let offset = (lastDayOfMonth.getDay() + 7 - this.weekStart) % 7;
		let LastDayOfNextCalendarMonth = new CalendarDate(lastDayOfMonth.getFullYear(), lastDayOfMonth.getMonth() + 1, lastDayOfMonth.getDate() + 6 - offset);
		return LastDayOfNextCalendarMonth;
	}
	get nextSevenDays() {
		const days = []
		
		for(let i = 0; i < 7; i++) {
			days[i] = new Date(this.y, this.m, this.d + i)
		}
		return days
	}
	get numWeeks() {
		// if the week starts with Monday we have to go 3 days in the past from the start of the next year to get the correct numWeek of the current year
		// this is because for example 30.12.2024 - 05.01.2025 is the first calendarWeek of 2025 
		let lastCalendarWeek = new CalendarDate(this.y + 1, 0, this.weekStart == 1 ? -3 : 0);
		return lastCalendarWeek.w;
	}
	set(y,m,d,noClean) {

		if (y !== undefined && (m === undefined || m === true) && d === undefined) {
			if (this.isDate(y))
			{
				// set year/month/day from date object
				return this.set(y.getFullYear(), y.getMonth(), y.getDate(), m);
			}
			if (y.y !== undefined && y.m !== undefined && y.d !== undefined)
			{
				// set year/month/day from CalendarDate object
				return this.set(y.y, y.m, y.d, m);
			}
		}
		// initialize year/month/day
		this._y = y ?? 0;
		this._m = m ?? 0;
		this._d = d ?? 0;
		
		if (!noClean)
			this._clean();
	}
	_clean() {
		this.set(new Date(this._y, this._m, this._d), true);
		this._w = null;
		this._wd = null;
	}
	format(options, lang=undefined) {
		return (new Date(this._y, this._m, this._d)).toLocaleString(lang, options);
	}
	compare(d) {
		if (this.isDate(d))
			return (this.y === d.getFullYear() && this.m === d.getMonth() && this.d === d.getDate());
		return (this.y === d.y && this.m === d.m && this.d === d.d);
	}
	isInWeek(w, y) {
		if (this.y == y && this.w == w)
			return true;
		if (this.weekStart == 1)
			return false;
		let edgeDay = this.cdFirstDayOfWeek;console.log(edgeDay);
		if (edgeDay.y == y && edgeDay.w == w)
			return true;
		edgeDay = this.cdLastDayOfWeek;
		if (edgeDay.y == y && edgeDay.w == w)
			return true;
		return false;
	}
	setLocale(locale) {
		this.weekStart = CalendarDate.getWeekStart(locale);
	}
	// method that checks if the parameter is of type Date
	isDate(obj){
		return Object.prototype.toString.call(obj) === '[object Date]';
	}
	cleanup(){
		this.watchLocale();
	}
}
/**
 * Returns the weekday number (Date.getDay()) on which the week starts depending on the locale.
 * This can be Saturday(6), Sunday(0) or Monday(1)
 * 
 * @see https://stackoverflow.com/questions/53382465/how-can-i-determine-if-week-starts-on-monday-or-sunday-based-on-locale-in-pure-j
 * 
 * @param string locale
 * 
 * @return integer
 */
CalendarDate.getWeekStart = function(locale) {

	locale = user_locale.value || locale || navigator.language;
	const parts = locale.match(/^([a-z]{2,3})(?:-([a-z]{3})(?=$|-))?(?:-([a-z]{4})(?=$|-))?(?:-([a-z]{2}|\d{3})(?=$|-))?/i);

	const language_code = parts[1];
	const language_starting_Sat = ['ar','arq','arz','fa'];
	const language_starting_Sun = 'amasbndzengnguhehiidjajvkmknkolomhmlmrmtmyneomorpapssdsmsnsutatethtnurzhzu'.match(/../g);
	
	const region_code = parts[4];
	const region_starting_Sat = 'AEAFBHDJDZEGIQIRJOKWLYOMQASDSY'.match(/../g);
	const region_starting_Sun = 'AGARASAUBDBRBSBTBWBZCACNCODMDOETGTGUHKHNIDILINJMJPKEKHKRLAMHMMMOMTMXMZNINPPAPEPHPKPRPTPYSASGSVTHTTTWUMUSVEVIWSYEZAZW'.match(/../g);

	if (region_code){
		if (region_starting_Sun.includes(region_code))
			return 0;
		else if (region_starting_Sat.includes(region_code))
			return 6;
		else
			return 1;
	}
	else if(language_code)
	{
		if (language_starting_Sun.includes(language_code))
			return 0;
		else if (language_starting_Sat.includes(language_code)) 
			return 6;
		else
			return 1;
	}
	else
	{
		return 1;
	}
}


export default CalendarDate
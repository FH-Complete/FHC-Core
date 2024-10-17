class CalendarDate {
	constructor(y, m, d) {
		this.weekStart = CalendarDate.getWeekStart();
		this.set(y, m, d);
		this._clean();
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
	get wd() {
		if (this._wd === null) {
			this._wd = ((new Date(this.y, this.m, this.d)).getDay()+7-this.weekStart)%7;
		}
		return this._wd;
	}
	get firstDayOfWeek() {
		let firstDayOfWeek = new Date(this.y, this.m, this.d);
		firstDayOfWeek.setDate(this.d -(firstDayOfWeek.getDay()+7-this.weekStart)%7);
		return firstDayOfWeek;
	}
	get cdFirstDayOfWeek() {
		return new CalendarDate(this.firstDayOfWeek);
	}
	get lastDayOfWeek() {
		let lastDayOfWeek = new Date(this.y, this.m, this.d);
		lastDayOfWeek.setDate(this.d -(lastDayOfWeek.getDay()+7-this.weekStart)%7 +6);
		return lastDayOfWeek;
	}
	get cdLastDayOfWeek() {
		return new CalendarDate(this.lastDayOfWeek);
	}
	get firstDayOfCalendarMonth() {
		let firstDayOfMonth = new Date(this.y, this.m, 1);
		return new Date(this.y, this.m, 1-(firstDayOfMonth.getDay() + 7 - this.weekStart)%7);
	}
	get cdFirstDayOfCalendarMonth() {
		let firstDayOfMonth = new Date(this.y, this.m, 1);
		return new CalendarDate(this.y, this.m, 1-(firstDayOfMonth.getDay() + 7 - this.weekStart)%7);
	}
	get lastDayOfCalendarMonth() {
		let lastDayOfMonth = new Date(this.y, this.m+1, 0);
		return new Date(lastDayOfMonth.getFullYear(), lastDayOfMonth.getMonth(), lastDayOfMonth.getDate()+6-(lastDayOfMonth.getDay() + 7 - this.weekStart)%7);
	}
	get cdLastDayOfCalendarMonth() {
		let lastDayOfMonth = new Date(this.y, this.m+1, 0);
		return new CalendarDate(lastDayOfMonth.getFullYear(), lastDayOfMonth.getMonth(), lastDayOfMonth.getDate()+6-(lastDayOfMonth.getDay() + 7 - this.weekStart)%7);
	}
	get numWeeks() {
		return (new CalendarDate(this.y+1,0,this.weekStart == 1 ? -3 : 0)).w;
	}
	set(y,m,d,noClean) {
		if (y !== undefined && (m === undefined || m === true) && d === undefined) {
			if (Object.prototype.toString.call(y) === '[object Date]')
				return this.set(y.getFullYear(), y.getMonth(), y.getDate(), m);
			if (y.y !== undefined && y.m !== undefined && y.d !== undefined)
				return this.set(y.y, y.m, y.d, m);
		}
		[this._y,this._m,this._d] = [y || 0, m || 0, d || 0];
		if (!noClean)
			this._clean();
	}
	_clean() {
		this.set(new Date(this._y, this._m, this._d), true);
		this._w = null;
		this._wd = null;
	}
	format(options) {
		return (new Date(this._y, this._m, this._d)).toLocaleString(undefined, options);
	}
	compare(d) {
		if (Object.prototype.toString.call(d) === '[object Date]')
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
	locale = locale || navigator.language;
	const parts = locale.match(/^([a-z]{2,3})(?:-([a-z]{3})(?=$|-))?(?:-([a-z]{4})(?=$|-))?(?:-([a-z]{2}|\d{3})(?=$|-))?/i);
	const regionSat = 'AEAFBHDJDZEGIQIRJOKWLYOMQASDSY'.match(/../g);
	const regionSun = 'AGARASAUBDBRBSBTBWBZCACNCODMDOETGTGUHKHNIDILINJMJPKEKHKRLAMHMMMOMTMXMZNINPPAPEPHPKPRPTPYSASGSVTHTTTWUMUSVEVIWSYEZAZW'.match(/../g);
	const languageSat = ['ar','arq','arz','fa'];
	const languageSun = 'amasbndzengnguhehiidjajvkmknkolomhmlmrmtmyneomorpapssdsmsnsutatethtnurzhzu'.match(/../g);

	return (
		parts[4] ? (
			regionSun.includes(parts[4]) ? 0 :
			regionSat.includes(parts[4]) ? 6 : 1) : (
			languageSun.includes(parts[1]) ? 0 :
			languageSat.includes(parts[1]) ? 6 : 1));
}


export default CalendarDate
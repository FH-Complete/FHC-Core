import moment from "moment";

export function getDateForDay(dayName) {
  const days = {
    monday: 0,
    tuesday: 1,
    wednesday: 2,
    thursday: 3,
    friday: 4,
    saturday: 5,
    sunday: 6,
  };

  const dayIndex = days[dayName.toLowerCase()];

  if (dayIndex === undefined) {
    throw new Error(`Invalid day name: ${dayName}`);
  }

  return moment().startOf("isoWeek").add(dayIndex, "days").format("YYYY-MM-DD");
}
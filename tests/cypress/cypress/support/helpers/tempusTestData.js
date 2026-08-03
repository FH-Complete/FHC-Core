import { tempusApi } from "../api/tempusApi";

export const TEMPUS_TEST_STUDY_PLAN_SHORT_CODE = "STG5";

const getLocalDateString = () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
};

const getSemesterStartDate = (semester) => semester.start?.slice(0, 10);

export const getTempusTestSemester = () =>
  tempusApi.getStudySemesters().then((semesters) => {
    const today = getLocalDateString();
    const activeSemester = semesters
      .filter((semester) => {
        const startDate = getSemesterStartDate(semester);

        return startDate && startDate <= today;
      })
      .sort((a, b) =>
        getSemesterStartDate(b).localeCompare(getSemesterStartDate(a)),
      )[0];

    expect(
      activeSemester,
      `Tempus test semester starting on or before ${today}`,
    ).to.exist;

    return activeSemester.studiensemester_kurzbz;
  });

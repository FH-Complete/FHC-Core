NOTEN TEST SUITE
================
Tests for the Benotungstool page and its backend, the Noten API.

WARNING: the suite deletes and writes grades. Run it only against a test instance with a test database.


1. Start
--------
One time:

  npm install
  copy tests/cypress/.env.example        -> tests/cypress/.env          BASE_URL and one login
  copy tests/cypress/suites/.env.example -> tests/cypress/suites/.env   passwords and database
  npm run noten:check -- seed     Seeds the test data: demolektor1, LV 5221, demoassistenz.

NOTEN_LEKTOR_USER must be a Lektor who teaches an LV. An admin teaches nothing, so the suite finds no LV.

Run:

  npm run noten:check     Checks the test data in the database. Run it first when something fails.
  npm run noten:api       API tests.
  npm run noten:ui        UI tests in Chrome.
  npx cypress open        Cypress with a window, to write a test.

Put more Cypress options after "--". A second --spec replaces the default:

  npm run noten:api -- --spec tests/cypress/e2e/specs/api/noten/frist.cy.js
  npm run noten:ui -- --headed --no-exit

Pending tests are normal. A test skips when the instance has a different configuration than the
test needs. The skip message names the key.

A profile in profiles/noten.js lists the keys for the other branch. To run such a test, set the keys
of the profile in application/config/noten.php (`config`) or config/global.config.inc.php (`flags`)
on the test instance, run the suite, and put the old values back.


2. Domain model
---------------
Note          A grade from lehre.tbl_note. Tests never use a fixed id: they use ctx.noten.
LV-Note       The grade of one student in one LV (campus.tbl_lvgesamtnote).
Pruefung      One exam with a date (lehre.tbl_pruefung).
Antritt       A Pruefung that uses one of the allowed attempts. "entschuldigt", "Noch nicht
              eingetragen" and "Nicht beurteilt" use no Antritt.
Verlauf       The rule state that the server derives for each student: pruefungen, antrittCount,
              maxAntritte, canAdd, bestanden, terminal. The client and the tests only read it.
Freigabe      The Lektor releases the LV-Notes. This makes them binding and sends a mail.
Frist         The last day to enter a Note (Noteneintragungsfrist).
Zeugnisnote   The final grade. Only the StV writes it. An Anrechnung blocks every Pruefung.
Punkte        With CIS_GESAMTNOTE_PUNKTE the Lektor enters Punkte; the Notenschluessel gives the Note.
Lektor        Teaches the LV and enters the Notes.
Assistenz     Enters Notes for a Studiengang. CIS_GESAMTNOTE_ROLLENMATRIX decides what it may do.

The Antritt chain of one student:

  Antritt 1                  the first Note
  Antritt 2, 3 ...           a repeat, only after a NEGATIVE Note
  kommissionelle Pruefung    the last Antritt; it always closes the chain

A Note from NOTEN_ABSCHLIESSEND always closes the chain ("bestanden"). Any other positive Note
closes it only while CIS_GESAMTNOTE_NOTENVERBESSERUNG is off.

The server holds all rules: PruefungsverlaufLib the Antritt chain, the controller Noten.php access,
Frist and Freigabe. The instance configuration decides every rule value (CIS_GESAMTNOTE_*).
Tests read it from getCisConfig as ctx.cisConfig.


3. Architecture
---------------
cypress.config.js                     Global: BASE_URL and one login for all suites. Loads suites/noten.js.
tests/cypress/
  suites/noten.js                     The suite: its cy.task list and its Cypress.env values.
  suites/.env                         NOTEN_* keys: accounts and database (not in Git).
  e2e/specs/api/noten/<topic>.cy.js   Calls the Noten API and checks the response.
  e2e/specs/ui/noten/<topic>.cy.js    Clicks through the Benotungstool page.
  support/api/notenApi.js             One function per endpoint, and the login.
  support/pages/benotungstool.po.js   All selectors and clicks of the page.
  support/helpers/                    ctx, test data, skips, assertions.
  tasks/                              Database access. Runs in Node, not in the browser.
  tools/dbCheck.js                    Checks the test data without a browser.
  profiles/noten.js                   The configurations that the suite must pass.
  local/                              Personal tools (SSH tunnel, lint). The suite never needs them.

The Noten API (application/controllers/api/frontend/v1/Noten.php):

  read    getCisConfig, getNoten, getBenotungstoolContext, getLvForStudiengang,
          getLehreinheitenForLv, getLektorenForLehreinheit, getStudentenNoten, getNoteByPunkte
  write   saveLvNote, importLvNoten, savePruefung, createPruefungen, importPruefungen, saveFreigabe

  getStudentenNoten  { students: [row], domain }. A row has zeugnisnote, lv_note, lv_punkte,
                     freigabedatum, benotungsdatum, teilnoten, proposed_note and verlauf.
  each write         { <uid>: { lvgesamtnote, verlauf, pruefung } }
  a rejected row     { <uid>: { error: { code, message } } }   (HTTP 200: createPruefungen and the imports)
  a stopped request  HTTP 500, errors: [{ code, message }]
  the code           the phrase key of the message, for example "maxAntritteReached"

Every test has four steps:

  1. before       loadNotenContext() gives ctx: semester, LV, students, Noten, configuration.
  2. beforeEach   loginAsLektor() and requireDbReset(). The test starts with givenBaseline(ctx, student).
  3. act          notenApi.* in an API test, benotungstoolPage.* in a UI test.
  4. check        readStateViaApi(ctx): what the server reports.
                  readLvGesamtnoteViaDb(ctx, student): what the database stores.

The helpers:

  notenApi.js        notenApi.* as NOTEN_LEKTOR_USER; apiGet / apiPost(path, params, auth) as another
                     account; loginAsLektor, lektorAuth, freigabePassword
  notenTestData.js   loadNotenContext, requireDbReset, resetNotenState, seedBaseline, seedPruefung,
                     seedZeugnisnote, readLvGesamtnoteViaDb, readAuthUid, antrittDate
  notenScenario.js   givenBaseline, addPruefung, editPruefung, readStateViaApi,
                     studentOf, verlaufOf, pruefungenOf, antritteOf
  notenConfig.js     skipIf and the require* skips
  notenErrors.js     expectNotenSuccess, expectNotenError, expectBulkRowAccepted, expectBulkRowError
  notenAssistenz.js  the Assistenz account


4. Rules for a new test
-----------------------
- Put the test into the spec of its topic. Create a new spec only for a new topic.
- Write the test title in German and describe the behavior: "lehnt eine zweite Prüfung ohne Note ab".
- Write the message of an expect in German too. Only the comments are English.
- Use no fixed ids and no fixed dates. Use ctx.noten, ctx.notenScale, ctx.students, antrittDate(ctx, n).
- Pass the student object (ctx.students[i]) to a helper, not the uid. Use at most ctx.students[6].
- Call the API through notenApi, or apiGet / apiPost for another account. No own cy.request.
- Start each test with givenBaseline, or with resetNotenState alone if the test needs a student
  without a Note. To seed several students after one reset, use resetNotenState and then seedBaseline
  for each student.
- Never depend on an earlier test.
- Check an error by its code: expectNotenError(response, "maxAntritteReached"). Never compare a text.
- Skip only with skipIf(this, condition, reason) or a require* helper.
- Write a test that skips as function () {}, not as () => {}. An arrow function has no "this".
- Test the branch that the instance has, and skip the other branch.
- Do not stub the Noten API. Use cy.intercept only to wait for a request.
- UI: select elements only by data-cy. After a save, wait for the request (waitForOk), not for the DOM.
- UI specs run in Chrome. Electron crashes on the Benotungstool page.
- A JavaScript error of the page fails the test. Only a rejected API request does not: the page
  already shows it as a toast (support/commands.js).
- Write comments in English: one line, and only when the code does not show the reason.


5. Naming
---------
- Domain words stay German, in the code too: Note, LV-Note, Pruefung, Antritt, Verlauf, Freigabe,
  Frist, Zeugnisnote, Punkte, Lektor, Assistenz, Lehreinheit, Studiengang, Notenschluessel,
  Teilnote, Anrechnung, entschuldigt, kommissionell. All other words are English.
- Two German words of the page are no domain words. The code uses the English word:

    Notenvorschlag  ->  proposal   the Note that the table proposes; applyProposal writes it as the LV-Note
    Wiederholung    ->  repeat     a Pruefung after Antritt 1 (verlauf.hasRepeat, requireRepeat)

- In code and comments, do not translate a domain word, and do not use a second word for it: "course grade" is LV-Note,
  "attempt" is Antritt, "exam" and "Termin" are Pruefung.

- One thing has one name in every layer:

    suites/.env          JavaScript        API field
    NOTEN_LV_ID          ctx.lvId          lv_id
    NOTEN_SEM_KURZBZ     ctx.semKurzbz     sem_kurzbz
    NOTEN_LEKTOR_USER    lektorAuth()

  An endpoint has the same name in Noten.php, public/js/api/factory/noten.js, notenApi.js and the
  intercept alias of the page object (@savePruefung).
- "noten" is the name of the suite: NOTEN_* keys, noten*.js files, cy.task("noten:..."), npm run noten:*.
  "benotungstool" names only the page: benotungstool.po.js.
- A function name starts with a verb: read, seed, add, edit, expect, require.
- JavaScript names use camelCase. API fields keep the server spelling (student_uid, lv_id).


6. Debugging
------------
401 in all tests
  The web instance and suites/.env use different databases. Compare DB_NAME in
  config/system.config.inc.php with NOTEN_DB_NAME.

"Fixture-Reset nicht verfügbar"
  The database connection is missing. Check NOTEN_DB_* in suites/.env. The pg_hba.conf of the
  database must allow the machine that runs Cypress.

A message contains "<< PHRASE"
  The phrase is missing in the database. Run system/phrasesupdate.php and phrasesync.php on the server.
  phrasesupdate.php only INSERTS a phrase. A changed text therefore needs a new key, which is why
  some keys end with a number: notenimportHinweistextv6.

"LV ... hat N Studierende, die Suite braucht 7"
  The test LV has too few students. Run: npm run noten:check -- seed

The next run does not start
  A Cypress process still runs. Stop it: Stop-Process -Name Cypress -Force

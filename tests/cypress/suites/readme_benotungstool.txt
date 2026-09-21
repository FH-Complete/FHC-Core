BENOTUNGSTOOL - TESTSUITE
=========================
The suite deletes and writes notes to the database. Run only on a test instance.

1. Files
-----------------

e2e/specs/unit/      Rules as pure functions.
e2e/specs/api/noten/ The API, called by tests to query an endpoint and verify the response.
e2e/specs/ui/        The user interface in the browser.

support/api/         API calls, which mirror public/js/api/factory/noten.js.
support/pages/       The user interface with selectors and click paths.
support/helpers/     Test data, error messages, fixture reset.
tasks/               Database connection in Node, not in the browser.
suites/noten.js      Links the suite to cypress.config.js.
suites/.env          The suite’s environment variables.
tools/dbCheck.js     Checks the fixture without HTTP. A good first command to run if problems arise.

The configuration is located in cypress.config.js. The credentials are stored in two files:
tests/cypress/.env applies to all suites, while tests/cypress/suites/.env applies only to this suite.


2. Preparation
---------------

Create login credentials in actual .env files:

tests/cypress/.env.example -> tests/cypress/.env
tests/cypress/suites/.env.example -> tests/cypress/suites/.env

Fill out both files. The comments within them explain each value.

- NOTEN_USER is the INSTRUCTOR, not an administrator. An administrator usually does not teach any courses,
  so the suite will not find any courses.
- The suite requires a direct connection to the database (NOTEN_DB_*).

Both sides must point to the same database: the web instance (config/system.config.inc.php,
DB_NAME) and the suite (NOTEN_DB_NAME in suites/.env). Otherwise, the suite will seed one database and
check against the other.


3. Start
----------

  npm run noten:check          Check fixture.
  npm run noten:api            Unit and API tests.
  npm run noten:ui             User interface in Chrome.
  npx cypress open             Cypress interactive.

Put more Cypress options after "--". A second --spec replaces the default:

  npm run noten:api -- --spec tests/cypress/e2e/specs/api/noten/noten.frist.cy.js
  npm run noten:ui -- --headed --no-exit
  npm run noten:api -- --env NOTEN_PUNKTE_MODE=on

tests/cypress/profiles/noten.js describes the configurations in which the suite should run. A
profile specifies the values for application/config/noten.php and the define() switches in
config/global.config.inc.php. The suite tests the configuration it finds. Switching
the instance is not part of the suite.


4. Debugging
------------------------

401 in all tests
  The web instance and suites/.env likely point to different databases.

“Fixture reset unavailable”
  The database connection is missing. Check NOTEN_DB_* in tests/cypress/suites/.env. The pg_hba.conf file for the
  database must allow access from the machine running Cypress.

A test expects one error message but receives a different one
  The phrase is missing from the database -> run system/phrasesupdate.php on the server.

The next run won’t start
  A Cypress process is still hanging. Terminate it:  Stop-Process -Name Cypress -Force

5. Conventions
---------------

- No fixed IDs in the test. The grades come from `getNoten`, the rule values from `getCisConfig`. A
  different installation will have different primary keys.
- After every write operation, wait for the request, not for the UI.
- Access the UI via `data-cy`, not via a CSS class.
- Set the state before the test using `givenBaseline` or `resetNotenState`. Never rely on the
  state left behind by a previous test.

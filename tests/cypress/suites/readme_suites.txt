CYPRESS SUITES
==============
The Cypress project of FH-Complete is tests/cypress. A run executes one suite. A suite is a set of
specs with its own cy.task functions and Cypress.env() values. A suite is part of core or of an extension.


1. Run a suite
--------------
Run every command in tests/cypress. CY_SUITE selects the suite:

  npx cypress run                  the default suite noten
  CY_SUITE=noten npx cypress run   a suite of core: suites/<name>.js
  CY_SUITE=../../application/extensions/FHC-Core-Anwesenheiten/tests/cypress npx cypress run
                                   the tests/cypress folder of an extension

A value with a "/" is a folder: relative to tests/cypress, or absolute. So a separate clone of the
extension works too. In PowerShell set the variable first: $env:CY_SUITE = "..."

The fixture check of a suite takes the same name or folder:

  node tools/dbCheck.js noten [check|seed]
  node tools/dbCheck.js ../../application/extensions/FHC-Core-Anwesenheiten/tests/cypress [check|seed]


2. The contract
---------------
A suite file exports two values:

  module.exports = {
    specPattern: "e2e/**/*.cy.js",        // relative to the suite folder
    setupNodeEvents(on, config, core) {   // like setupNodeEvents of Cypress, plus core
      on("task", { ... });
      config.env = { ...suiteKeys, ...config.env };   // a value from --env wins
      return config;
    },
  };

                       suite file          suite folder
  suite of core        suites/<name>.js    tests/cypress
  suite of extension   <folder>/suite.js   <folder>

core        { db }: tasks/db.js with withClient, inTransaction, assertWritable, checkConnection.
.env        Core loads the .env next to the suite file: suites/.env or <folder>/.env. A value from the
            environment wins. The pipeline sets all keys in the environment.
fixtures    <suite folder>/fixtures
after:run   Core closes the DB pool after the run. Cypress keeps only one after:run handler, so a suite
            registers none.

A suite can have a fixture check for tools/dbCheck.js: tools/checks/<name>.js in core, or checks.js in
the folder of an extension. It exports { title, checks, sqlFiles }.


3. Keys
-------
tests/cypress/.env (template .env.example) holds the keys that all suites share:

  BASE_URL                  the instance under test
  USER_NAME, USER_PASSWORD  the default account of cy.login, cy.fhcGet and cy.fhcPost
  TEST_DB_*                 the database of that instance; tasks/db.js reads it

A suite gives its own keys its prefix, for example NOTEN_* or ANW_*.


4. Commands for all suites (support/commands.js)
------------------------------------------------
  cy.login(user, password)                    session login, default USER_NAME
  cy.fhcGet(path, qs, account)                GET as an account; null sends no credentials
  cy.fhcPost(path, body, account)             POST with a JSON body
  cy.fhcUpload(path, fields, file, account)   multipart POST of a text file
  .fhcData()                                  checks a success response, yields its data
  .fhcErrors(status)                          checks a failed response, yields its errors

path is the part after the entry point, for example "api/frontend/v1/Phrasen/loadModule/global" or
"extensions/FHC-Core-Anwesenheiten/api/InfoApi/getViewPermissions". The commands send it through
cis.php, like the CIS4 pages do (support/helpers/paths.js).


5. Rules for an extension suite
-------------------------------
- A spec imports no core file. It uses the commands of section 4.
- A Node file requires no npm package and no core file. It gets the DB part through core.
- The suite runs without a file change on every test instance with the extension.

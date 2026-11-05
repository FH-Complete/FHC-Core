LOCAL WORKSTATION - LINT AND FORMAT
===================================
These tools are for local workstations only. No file of the suite refers to this folder. The team does
not need it to run the tests.

1. Files
-----------------

eslint.config.js    Lint rules of the noten suite. Two rules check conventions from suites/readme_noten.txt.
package.json        ESLint and Prettier. Cypress, dotenv and pg come from tests/cypress/package.json.


2. Preparation
---------------

Install the packages in this folder:

  cd tests/cypress/local
  npm install


3. Lint and format
-------------------

Run the commands in this folder. The paths in eslint.config.js start in tests/cypress, so both
scripts change to that folder first. Both scripts touch only the files of the noten suite and the
shared files, not the specs of other suites.

  npm run lint              ESLint for the noten suite and cypress.config.js. Add "-- --fix" to fix.
  npm run format            Prettier for the same files: tabs, 120 columns. It writes the files.

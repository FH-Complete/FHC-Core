LOCAL WORKSTATION - LINT AND FORMAT
===================================
These tools are for local workstations only. No file of the suite refers to this folder. The team does
not need it to run the tests.

1. Files
-----------------

eslint.config.js    Lint rules of the suite. Two rules check conventions from suites/readme_noten.txt.
package.json        ESLint and Prettier. Cypress, dotenv and pg come from the repository root.


2. Preparation
---------------

Install the packages in this folder:

  cd tests/cypress/local
  npm install


3. Lint and format
-------------------

Run the commands in this folder. The paths in eslint.config.js start at the repository root, so
both scripts change to the root first.

  npm run lint              ESLint for tests/cypress and cypress.config.js. Add "-- --fix" to fix.
  npm run format            Prettier for tests/cypress: tabs, 120 columns. It writes the files.
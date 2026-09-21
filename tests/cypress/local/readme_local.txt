LOCAL WORKSTATION - SSH TUNNEL AND CONFIGURATION PROFILES
=========================================================
These tools are for local workstations only. No file of the suite refers to this folder. The team does
not need it to run the tests.

The tools do two things:

- They open an SSH tunnel to the test database. pg_hba.conf accepts only the application server, so
  a workstation cannot connect directly.
- They switch the test instance to a configuration profile, run the suite, and put the original
  configuration back.

1. Files
-----------------

withTunnel.js       Opens the tunnel, starts a command, and closes the tunnel when the command ends.
sshTunnel.js        The tunnel itself (package ssh2). withTunnel.js and profileSwitch.js use it.
notenProfiles.js    Command line for the profiles: status, apply, restore, run.
profileSwitch.js    Writes the profile over SFTP, keeps the backups, checks the instance.
eslint.config.js    Lint rules of the suite. They enforce the conventions in .claude/rules/.
package.json        ssh2, ESLint and Prettier. Cypress, dotenv and pg come from the repository root.
.env                SSH access and the path of the instance (gitignored).

The profiles are in tests/cypress/profiles/noten.js. That file is part of the suite.


2. Preparation
---------------

Install the packages in this folder:

  cd tests/cypress/local
  npm install

Create the credentials file:

tests/cypress/local/.env.example -> tests/cypress/local/.env

The tools also read tests/cypress/.env (BASE_URL) and tests/cypress/suites/.env (NOTEN_USER,
NOTEN_DB_*). Fill out these two files first. readme_benotungstool.txt explains them.

- NOTEN_DB_HOST in suites/.env stays the real database host, as the SSH host sees it. Do not write
  127.0.0.1. The tunnel changes the value only for the command that it starts.
- NOTEN_SSH_HOST is the SSH host. It is usually not the web hostname.
- Without NOTEN_SSH_KEY, the tools use only the SSH agent. They do not read ~/.ssh themselves.
- Use the agent if possible: run ssh-add and keep NOTEN_SSH_KEY empty. On Windows, the service
  "OpenSSH Authentication Agent" must run.
- NOTEN_SSH_PASSPHRASE keeps the passphrase in plain text. Use it only if the agent is not possible.
- NOTEN_REMOTE_ROOT is the absolute path of the instance on its host. Only the profiles need it.
- Set NOTEN_PROFILE_SSH_* only when the instance runs on a different host than the database tunnel.
- The tools need Node 18 or later.


3. SSH tunnel
--------------

Run the commands from the repository root. Put withTunnel.js in front of each command that needs
the database:

  node tests/cypress/local/withTunnel.js npm run noten:check
  node tests/cypress/local/withTunnel.js npm run noten:api
  node tests/cypress/local/withTunnel.js npm run noten:ui
  node tests/cypress/local/withTunnel.js npx cypress open

withTunnel.js opens the tunnel on a free local port. Then it sets NOTEN_DB_HOST=127.0.0.1 and
NOTEN_DB_PORT=<port> for the command. The .env files do not change. When the command ends, the tunnel
closes. Ctrl+C stops the command first and then the tunnel.

The same result without this code: open the tunnel in a second terminal, and write fixed values in
suites/.env.

  ssh -N -L 15432:<database host>:5432 <user>@<ssh host>

  NOTEN_DB_HOST=127.0.0.1
  NOTEN_DB_PORT=15432


4. Configuration profiles
--------------------------

The suite tests the configuration it finds. Some rules have a second branch, for example two
Antritte instead of three. A profile switches the instance to such a branch.

  node tests/cypress/local/notenProfiles.js status                  Active profile and all profiles.
  node tests/cypress/local/notenProfiles.js apply <profile>         Switch the instance to a profile.
  node tests/cypress/local/notenProfiles.js restore                 Put the original files back.
  node tests/cypress/local/notenProfiles.js run [api|ui|all] [a,b]  One run for each profile.


5. Lint and format
-------------------

Run the commands in this folder. The paths in eslint.config.js start at the repository root, so
both scripts change to the root first.

  npm run lint              ESLint for tests/cypress and cypress.config.js. Add "-- --fix" to fix.
  npm run format            Prettier for tests/cypress: tabs, 120 columns. It writes the files.


6. Debugging
------------------------

"SSH to <user>@<host> ... failed" or "SSH zu <user>@<host> gescheitert"
  The SSH host is often not the web hostname. Without NOTEN_SSH_KEY.
  Run ssh-add, or write the key path in NOTEN_SSH_KEY.

"SSH key does not exist"
  The path in NOTEN_SSH_KEY is wrong. A path can start with ~.

The tunnel is up, but the database connection closes immediately
  The SSH host cannot reach NOTEN_DB_HOST. If Postgres runs on the SSH host itself, write localhost.

"Profilwechsel nach 60 Sekunden ungeprüft"
  The instance at BASE_URL does not show the profile. The message lists each key with the actual and
  the expected value. Make sure that NOTEN_REMOTE_ROOT is the path of the instance at BASE_URL.

"getCisConfig antwortet nach dem Profilwechsel mit HTTP ..."
  HTTP 401: NOTEN_USER or NOTEN_PASSWORD is wrong. Other codes: the changed file contains a PHP
  error. Run restore, then examine the values of the profile.

"Auf <path> ist noch "<profile>" aktiv"
  A profile is still active on a different NOTEN_REMOTE_ROOT. Run restore. It uses the path in
  state.json.

"Die Sicherung ... fehlt"
  The backup in ~/.cypress-noten-profile/ is missing, so the original file is unknown. Deploy the
  file again. The tool then takes it as the original.

"... definiert <FLAG> nicht"
  The profile sets a flag that config/global.config.inc.php does not define. Add the define() line
  on the instance, or remove the flag from the profile.

Ctrl+C during notenProfiles.js run
  The instance stays on the current profile. Run restore.

Self-signed certificate on the instance
  Set NODE_TLS_REJECT_UNAUTHORIZED=0 in local/.env.
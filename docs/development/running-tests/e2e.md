# End-to-end (E2E) testing

[Cypress](https://www.cypress.io/) is used to provide browser-level full-stack testing.

In this project, Cypress gets run within the `cypress-workspace` holobranch defined at `.holo/branch/cypress-workspace/**` within the project repository. This allows local test suite additions and overrides to be stacked on top of those provided by parent projects. The base implemenation is published from [`emergence-skeleton`](https://github.com/JarvusInnovations/emergence-skeleton) and your local project may have any number of parent projects stacked in between, so there can be many layers contributing to the below content structure.

The `cypress-workspace` holobranch typically copies the following overrides from the local project repository:

- `cypress.json`: top-level project configuration for cypress
- `cypress/integrations/**/*.js`: additional or overridden test suites
- `cypress/integrations/**/*.json`: additional or overridden test suite configurations (provides some flexibility for test suites to support different downstream reskinnings without all their code needing to be duplicated and overridden)
- `cypress/fixtures/**`: static content test suites can make use of

Less commonly, the following files might also be copied from the local project repository to override the Cypress setup in more depth:

- `cypress/support/index.js`: Cypress plugins and additional commands get loaded here for all test suites
- `package.json`/`package-lock.json`: Tracks the Cypress version and those of installed plugin packages

Try to avoid having copies of these in local project repositories:

- `cypress/support/commands.js`: Base set of additional commands that test suites can rely on. Instead of overridding this file, add additional project-specific commands to some new files under `cypress/support` and override `cypress/support/index.js` to load them
- `cypress/plugins/index.js`: Base set of automatic environment setup logic

## Run tests quickly

To quickly run the full test suite headlessly, run on the local terminal **outside the studio** in the root of your local project repository:

```bash
script/test
```

## Run tests interactively

To run tests with Cypress' interactive GUI open, run on the local terminal **outside the studio** in the root of your local project repository:

```bash
script/test-interactive
```

This script uses [`unionfs-fuse`](https://github.com/rpodgorny/unionfs-fuse) to set up a virtual directory mount on your workstation's filesystem to run Cypress out of. This union mount provides a live workspace where your local project workspace is merged on top of the base set of `cypress-workspace` content pulled from your parent project.

This virtual directory mount gets set up at `${path_to_your_repo}.cypress-workspace/merged` and Cypress gets run from there.

- Changes you save to Cypress content in **your local project work tree** will immediately be reflected in the `merged` mount
    - The filesystem events needed to drive auto-reload **may not work**
    - Exit the Cypress GUI and reload it to thoroughly force your latest content to be used
- Changes you save to Cypress content in **your local `merged` mount** will immediately be reflected back to your local project work tree
- If parent project content changes / you've edited a source config, exit the Cypress GUI and re-run `script/test-interactive` to restart in a fresh environment

!!! tip "Making Cypress auto-reload as you save changes"

    Because filesystem change events from your local project work tree to the merged unionfs that Cypress runs out of don't always work, work on Cypress tests out of the `merged` mount instead.

    Any changes you make will immediately be written to to your local project work tree ready to stage into a git commit, and filesystem change events will fire live for Cypress to auto-reload test suites as you work.

    The **Open in IDE** button that Cypress' main window will how you as you hover over tests in the list can be used to open the copy of the file in the `merged` mount where changes will trigger auto-reload.

## Testing against a remote server

By setting environment variables before launching the Cypress GUI, the E2E test suite can be configured to execute against a backend studio hosted on a remote machine or server.

On the local terminal **outside the studio** in the root of your local project repository:

1. Set base URL to studo HTTP root reachable from local workstation:

    ```bash
    export CYPRESS_BASE_URL='http://workstation.mydomain:{{ studio.web_port }}'
    ```

2. Configure the SSH host that the backend studio is running on:

    ```bash
    export CYPRESS_STUDIO_SSH='workstation.mydomain'
    ```

    Your local terminal must be set up to connect to it without password.

3. Configure the name of the Docker container running the backend studio:

    ```bash
    export CYPRESS_STUDIO_CONTAINER='{{ studio.name }}'
    ```

4. Launch the Cypress GUI:

    ```bash
    script/test-interactive
    ```

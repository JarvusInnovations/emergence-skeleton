# End-to-end (E2E) testing

## Running tests

[Cypress](https://www.cypress.io/) is used to provide browser-level full-stack testing. The `package.json` file at the root of the repository specifies the dependencies for running the test suite and all the configuration/tests for Cypress are container in the `cypress/` tree at the root of the repository.

To get started, from a terminal **outside the studio** in the root of the repository:

1. Install development tooling locally:

    ```bash
    npm install
    ```

2. Launch the Cypress GUI:

    ```bash
    npm run cypress:open
    ```

## Testing against a remote server

By setting environment variables before launching the Cypress GUI, the E2E test suite can be configured to execute against a backend studio hosted on a remote machine or server.

On the local terminal **outside the studio** in the root of the repository:

1. Set base URL to studo HTTP root reachable from local workstation:

    ```bash
    export CYPRESS_BASE_URL='http://workstation.mydomain:7080'
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
    npm run cypress:open
    ```

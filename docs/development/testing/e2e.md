# End-to-end (E2E) testing

## Running tests

[Cypress](https://www.cypress.io/) is used to provide browser-level full-stack testing. The `package.json` file at the root of the repository specifies the dependencies for running the test suite and all the configuration/tests for Cypress are container in the `cypress/` tree at the root of the repository.

To get started, from a terminal **outside the studio** in the root of the repository:

```bash
# install development tooling locally
npm install

# launch cypress app
npm run cypress:open
```

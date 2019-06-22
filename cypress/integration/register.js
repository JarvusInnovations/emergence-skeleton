describe('Admin login test', () => {

    // reset database before tests
    before(() => {
        const studioContainer = Cypress.env('STUDIO_CONTAINER');

        if (studioContainer) {
            cy.exec(`echo 'DROP DATABASE IF EXISTS \`default\`;' | docker exec -i ${studioContainer} hab pkg exec core/mysql mysql -u root -h 127.0.0.1`);
        }
    });

    it('Register and set up profile', () => {
        cy.visit('/');

        cy.contains('Register').click();
        cy.location('pathname').should('eq', '/register');

        cy.focused()
            .should('have.attr', 'name', 'FirstName')
            .type('Fname')
            .tab();

        cy.focused()
            .should('have.attr', 'name', 'LastName')
            .type('Lname')
            .tab();

        cy.focused()
            .should('have.attr', 'name', 'Email')
            .type('email@example.org')
            .tab();

        cy.focused()
            .should('have.attr', 'name', 'Username')
            .type('zerocool')
            .tab();

        cy.focused()
            .should('have.attr', 'name', 'Password')
            .type('password123')
            .tab();

        cy.focused()
            .should('have.attr', 'name', 'PasswordConfirm')
            .type('password1234{enter}')
        ;

        cy.location('pathname').should('eq', '/register');

        cy.get('.error');

        cy.focused()
            .should('have.attr', 'name', 'FirstName')
            .tab()
            .tab()
            .tab()
            .tab()
            .tab()
            .type('password123{enter}')
        ;

        cy.contains('Fill out your profile').click();

        // upload same photo twice
        cy.upload_file('photo.jpg', 'image/jpeg', 'input[type=file]');
        cy.contains('Upload New Photo').click();
        cy.upload_file('photo.jpg', 'image/jpeg', 'input[type=file]');
        cy.contains('Upload New Photo').click();
    });
});
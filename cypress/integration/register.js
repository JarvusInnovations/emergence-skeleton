describe('Registration', () => {

    // reset database before tests
    before(() => {
        cy.dropDatabase();
    });

    it('Register user', () => {
        cy.readFile('cypress/integration/profile.json').then(({ values }) => {
            cy.visit('/');

            cy.contains('Register').click();
            cy.location('pathname').should('eq', '/register');

            cy.get('input[name=FirstName]')
                .focus()
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
                .type('password1234{enter}');

            cy.location('pathname').should('eq', '/register');

            cy.get('.error');

            cy.get('input[name=Password]')
                .should('have.value', 'password123');

            cy.get('input[name=PasswordConfirm]')
                .should('have.value', 'password1234')
                .parent('.field')
                .should('have.class', 'has-error')
                .find('.error-text')
                .contains('Please enter your password a second time for confirmation');

            cy.get('input[name=FirstName]')
                .focus()
                .should('have.attr', 'name', 'FirstName')
                .tab()
                .tab()
                .tab()
                .tab()
                .tab()
                .type('password123{enter}');

            cy.contains('Fill out your profile').click();
            cy.location('pathname').should('eq', '/profile');
            cy.location('search').should('be.empty');

            // verify profile API data
            cy.request('/profile?format=json').its('body.data').then(data => {
                expect(data).to.have.property('ID', 1);
                expect(data).to.have.property('Class', values['profile-data'].Class);
                expect(data).to.have.property('FirstName', 'Fname');
                expect(data).to.have.property('LastName', 'Lname');
                expect(data).to.have.property('Email', 'email@example.org');
                expect(data).to.have.property('Username', 'zerocool');
                expect(data).to.have.property('AccountLevel', 'User');
            });
        });
    });
});

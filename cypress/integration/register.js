describe('Admin login test', () => {
    it('Visit the homepage', () => {
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
            .type('password1234{enter}');

        cy.location('pathname').should('eq', '/register');

        cy.get('.error');

        cy.focused()
            .should('have.attr', 'name', 'FirstName')
            .tab()
            .tab()
            .tab()
            .tab()
            .tab()
            .type('password123{enter}');
    });
});
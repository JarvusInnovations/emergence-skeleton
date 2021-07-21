describe('Registration and profile', () => {

    // reset database before tests
    before(() => {
        cy.dropDatabase();
    });

    it('Register user', () => {
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
    });

    it('Upload profile photos', () => {
        cy.readFile('cypress/integration/register.json').then(({ selectors, values }) => {
            cy.loginAs('zerocool', 'password123');
            cy.visit('/profile');

            // upload same photo twice
            cy.upload_file('photo.jpg', 'image/jpeg', 'input[type=file]');
            cy.contains('Upload New Photo').click();
            cy.upload_file('photo.jpg', 'image/jpeg', 'input[type=file]');
            cy.contains('Upload New Photo').click();

            cy.get(selectors['gallery-photo-first'])
                .should('have.class', values['gallery-photo-selected-class'])
                .within(() => {
                    cy.get(selectors['gallery-default-link'])
                        .should('not.exist');
                })
                .next(selectors['gallery-photo'])
                    .should('not.have.class', values['gallery-photo-selected-class'])
                    .find(selectors['gallery-default-link'])
                        .should('exist')
                        .click();

            cy.get(selectors['gallery-photo-first'])
                .should('not.have.class', values['gallery-photo-selected-class'])
                .within(() => {
                    cy.get(selectors['gallery-default-link'])
                        .should('exist');
                })
                .next(selectors['gallery-photo'])
                    .should('have.class', values['gallery-photo-selected-class'])
                    .within(() => {
                        cy.get(selectors['gallery-default-link'])
                            .should('not.exist');
                    })
                .find('img')
                    .should(($img) => {
                        expect($img).to.have.attr('src').to.match(/^\/thumbnail\/2\/\d+x\d+(\/cropped)?$/);
                        expect($img).to.have.prop('width').to.be.a('number')
                        expect($img).to.have.prop('height').to.be.a('number')
                    });
        });
    });

    it('Fill out profile', () => {
        cy.readFile('cypress/integration/register.json').then(({ selectors, values }) => {
            cy.loginAs('zerocool', 'password123');
            cy.visit('/profile');

            cy.get('input[name=Location]')
                .type('Philadelphia, PA')
                .tab();

            cy.focused()
                .should('have.attr', 'name', 'About')
                .type('Meow')
                .tab();

            cy.focused()
                .should('contain', values['profile-markdown-link-text'])
                .should('have.attr', 'href', values['profile-markdown-link-href'])
                .tab();

            cy.focused()
                .should('contain', values['profile-save-button-text'])
                .tab();

            cy.focused()
                .should('have.attr', 'name', 'Email')
                .type('email@example.com')
                .tab();

            cy.focused()
                .should('have.attr', 'name', 'Phone')
                .type('(123) 456-7890{enter}');

            cy.location('pathname').should('eq', '/profile');
            cy.location('search').should('eq', '?status=saved');

            // verify profile API data
            cy.request('/profile?format=json').its('body.data').then(data => {
                expect(data).to.have.property('ID', 1);
                expect(data).to.have.property('Class', values['profile-data-class']);
                expect(data).to.have.property('FirstName', 'Fname');
                expect(data).to.have.property('LastName', 'Lname');
                expect(data).to.have.property('Email', 'email@example.com');
                expect(data).to.have.property('Phone', 1234567890);
                expect(data).to.have.property('Location', 'Philadelphia, PA');
                expect(data).to.have.property('About', 'Meow');
                expect(data).to.have.property('PrimaryPhotoID', 2);
                expect(data).to.have.property('Username', 'zerocool');
                expect(data).to.have.property('AccountLevel', 'User');
            });
        });
    });

    it('View profile', () => {
        cy.readFile('cypress/integration/register.json').then(({ selectors, values }) => {
            cy.loginAs('zerocool', 'password123');
            cy.visit('/profile');

            // verify profile display page
            cy.visit('/profile/view');
            cy.location('pathname').should('eq', '/people/zerocool');
            cy.get('.header-title').should('contain', 'Fname Lname');
            cy.get('a[href^="http://maps.google.com/"]').should('contain', 'Philadelphia, PA');
            cy.get(selectors['profile-view-photo-last']).should('have.attr', 'href', values['profile-photo-last-href']);
            cy.get('.photo-thumb').should('have.length', 2);
            cy.get('#info .about').should('contain', 'Meow');
        });
    });
});
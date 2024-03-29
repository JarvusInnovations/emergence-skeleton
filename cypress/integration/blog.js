describe('Blogging', () => {

    // reset database before tests
    before(() => {
        cy.resetDatabase();
    });

    it('Create post', () => {
        cy.loginAs();

        cy.visit('/blog');

        cy.contains('Create a Post').click();

        cy.get('.x-panel.emergence-cms-editor .x-btn[id^=emergence-cms-toolbar-][id$=-menu-trigger]')
            .click();

        cy.contains('.x-menu-item-text[id^=menuitem-]', 'Publish on save')
            .click();

        cy.contains('.x-menu-item-text[id^=menucheckitem-]', 'Publish on save')
            .click();

        cy.get('input[id^=datefield-]')
            .click()
            .clear()
            .type('01/02/2030');

        cy.get('input[id^=timefield-]')
            .click()
            .clear()
            .type('4:05 pm{enter}');

        cy.get('.x-component.emergence-cms-preview time[data-ref="timeEl"]')
            .should('contain', 'Wednesday, January 2, 2030 at 4:05 pm')
            .click();

        cy.get('.x-panel.emergence-cms-editor .x-btn[id^=emergence-cms-toolbar-][id$=-menu-trigger]')
            .click();

        cy.contains('.x-menu-item-text[id^=menuitem-]', '1/2/2030 4:05 pm');

        cy.get('.x-field.field-title input')
            .click()
            .type('Hello world!')
            .tab();

        cy.focused()
            .should('contain', 'Save')
            .tab();

        cy.focused()
            .wait(500)
            .type('tag1{enter}tag2');

        cy.get('.x-panel-body .x-field textarea')
            .click()
            .type('## Header 2{enter}{enter}### Header 3{enter}{enter}- point 1{enter}- point2{enter}');

        cy.intercept(
            {
                method: 'POST',
                pathname: '/blog/save',
                middleware: true
            },
            // delay response so loadmask can be verified
            req => req.on('response', res => res.setDelay(250))
        ).as('saveBlog');

        cy.contains('.x-btn.save-btn', 'Save')
            .click();

        cy.contains('.emergence-cms-editor .x-mask-msg-text', 'Saving').should('be.visible');

        cy.wait('@saveBlog').its('response.statusCode').should('eq', 200);

        cy.location('pathname', { timeout: 10000 })
            .should('eq', '/blog/hello_world/edit');

        cy.location('search').should('be.empty');

        cy.get('.x-component.emergence-cms-preview').within(() => {

            cy.get('time[data-ref="timeEl"]')
                .should('contain', 'Wednesday, January 2, 2030 at 4:05 pm');

            cy.get('[data-ref=tagsCt] a')
                .should('have.length', 2)
                .first()
                .should('have.attr', 'href', '/tags/tag1')
                .should('have.text', 'tag1')
                .next()
                .should('have.attr', 'href', '/tags/tag2')
                .should('have.text', 'tag2');
        });
    });

    it('Edit post', () => {
        cy.loginAs();

        cy.visit('/drafts');

        cy.contains('Hello world!')
            .should('have.attr', 'href', '/blog/hello_world')
            .click();

        cy.contains('.blog-post a', 'Edit')
            .should('have.attr', 'href', '/blog/hello_world/edit')
            .click();

        cy.get('.x-panel.emergence-cms-editor .x-btn[id^=emergence-cms-toolbar-][id$=-menu-trigger]')
            .click();

        cy.contains('.x-menu-item-text[id^=menuitem-]', '1/2/2030 4:05 pm')
            .click();

        cy.get('input[id^=timefield-]')
            .click()
            .clear()
            .type('6:07 pm{enter}');

        cy.get('.x-component.emergence-cms-preview time[data-ref="timeEl"]')
            .should('contain', 'Wednesday, January 2, 2030 at 6:07 pm')
            .click();

        cy.intercept(
            {
                method: 'POST',
                pathname: '/blog/save',
                middleware: true
            },
            // delay response so loadmask can be verified
            req => req.on('response', res => res.setDelay(250))
        ).as('saveBlog');

        cy.contains('.x-btn.save-btn', 'Save')
            .click();

        cy.contains('.emergence-cms-editor .x-mask-msg-text', 'Saving')
            .should('be.visible');

        cy.wait('@saveBlog').its('response.statusCode').should('eq', 200);

        cy.contains('.emergence-cms-editor .x-mask-msg-text', 'Saving', { timeout: 10000 })
            .should('not.be.visible');

        cy.reload();

        cy.get('.x-component.emergence-cms-preview time[data-ref="timeEl"]')
            .should('contain', 'Wednesday, January 2, 2030 at 6:07 pm');
    });
});
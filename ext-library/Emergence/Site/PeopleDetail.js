 Ext.define('Emergence.Site.PeopleDetail', {
        extend: 'Ext.Panel',
        alias: 'widget.peopleDetail',
        // add tplMarkup as a new property
        layout: {
        	align: 'left'
        },
        tplMarkup: [
            'FirstName:{FirstName}<br/>',
            'LastName: {LastName}<br/>',
            'AccountLevel: {AccountLevel}<br/>',
        ],
        // startingMarup as a new property
        startingMarkup: 'Please select a book to see additional details',

        bodyPadding: 7,
        // override initComponent to create and compile the template
        // apply styles to the body of the panel and initialize
        // html to startingMarkup
        initComponent: function() {
            this.tpl = Ext.create('Ext.Template', this.tplMarkup);
            this.html = this.startingMarkup;

            this.bodyStyle = {
                background: '#ffffff'
            };
            // call the superclass's initComponent implementation
            App.BookDetail.superclass.initComponent.call(this);
        },
        // add a method which updates the details
        updateDetail: function(data) {
            this.tpl.overwrite(this.body, data);
        }
    });
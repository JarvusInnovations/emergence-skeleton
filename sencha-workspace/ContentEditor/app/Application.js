/**
 * The main application class. An instance of this class is created by app.js when it calls
 * Ext.application(). This is the ideal place to handle application launch and initialization
 * details.
 */
Ext.define('ContentEditor.Application', {
    extend: 'Ext.app.Application',
    requires: [
        'Emergence.cms.view.DualView',
        'Ext.plugin.Viewport'
    ],

    name: 'ContentEditor',

    init: function() {
        Emergence.util.API.setHostname('v1-demo.node0.slate.is');
    },

    launch: function () {
        var me = this,
            dualView = Ext.create('Emergence.cms.view.DualView', {
                plugins: 'viewport'
            });

        // TODO - Launch the application
        Emergence.util.API.request({
            url: '/blog/about',
            success: function(response) {
                dualView.lookupReference('editor').setContentRecord(response.data.data);
            }
        });
    }
});

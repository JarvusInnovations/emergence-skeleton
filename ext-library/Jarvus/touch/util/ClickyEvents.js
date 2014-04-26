/**
 * Log state changes to clicky
 */
Ext.define('Jarvus.touch.util.ClickyEvents', {
    singleton: true,

    constructor: function() {
        if (Ext.feature.has.History && window.clicky) {
            window.addEventListener('hashchange', function(ev) {
                clicky.log(ev.newURL, null, 'pageview');
            });
        }
    }
});
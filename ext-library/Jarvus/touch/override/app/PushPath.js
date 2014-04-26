/**
 * Provides {@link #method-pushPath} for controllers
 */
Ext.define('Jarvus.touch.override.app.PushPath', {
    override: 'Ext.app.Controller',
    requires: [
        'Ext.app.Action',
        'Jarvus.touch.override.app.EncodedPaths'
    ],

    /**
     * Silently push a given path to the address bar without triggering a routing event.
     * This is useful to call after a user has _already_ entered a UI state and the current address
     * _may_ need to be synchronized. If the given path was already in the address bar this method
     * has no effect.
     *
     * @param {String/String[]/Ext.data.Model} url The url path to push
     */
    pushPath: function(url) {
        var app = this.getApplication(),
            encodedUrl = app.encodePath(url);

        app.getHistory().add(Ext.create('Ext.app.Action', {url: encodedUrl}), true);

		app.fireEvent('pathpush', encodedUrl);
    }
});
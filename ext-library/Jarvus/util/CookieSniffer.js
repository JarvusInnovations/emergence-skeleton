/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext,Slate*/
Ext.define('Jarvus.util.CookieSniffer', {
	singleton: true
	/**
     * Download specified URL and execute a supplied callback when the download begins.
     * The URL is loaded in a hidden iframe to avoid UI disturbance and network cancellations.
     * A random downloadToken parameter is added to the request which the server process must return via
     * a cookie of the same name with a path of '/'. This allows you to prevent duplicate requests and keep
     * the user updated about dynamic downloads that can take awhile to start.
     
     * @param {url} The url path to download
     * @param {callback} callback function to execute after download begins
     * @param {scope} scope of callback}
     * @param {options} defaults to {absoluteUrl: false, openWindow: false, pollingInterval: 500}
     */
    ,downloadFile: function(url, callback, scope, options) {
        options = options || {};
        
        // create and append downloadToken
        var downloadToken = Math.random();

        url = Ext.urlAppend(url, 'downloadToken='+downloadToken);
        
        // get or create iframe el
        this.downloadFrame = this.downloadFrame || Ext.getBody().createChild({
            tag: 'iframe',
            style: {
                display: 'none'
            }
        });
        
        // setup token monitor
        var downloadInterval = setInterval(function() {
            if(Ext.util.Cookies.get('downloadToken') == downloadToken)
            {
                clearInterval(downloadInterval);
                Ext.util.Cookies.clear('downloadToken');
                Ext.callback(callback, scope, [url, options]);
            }
        }, options.pollingInterval || 500);
        
        // launch download
        if(options.openWindow)
        {
            window.open(url);
        }
        else
        {
            // use iframe for loading, setting window.location cancels current network ops
            this.downloadFrame.dom.src = url;
        }

    }
});
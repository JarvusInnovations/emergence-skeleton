/**
 * Creates an link for adding an event to the user's calendar
 */
Ext.define('Jarvus.touch.ux.CalendarLink', {
    extend: 'Ext.Component',
    xtype: 'calendarlink',
    
    config: {
        /**
         * @cfg {Object}
         * Object containing event information:
         * 
         * - **title**
         * - **startTime**
         * - **endTime**
         * - **location** (optional)
         * - **details** (optional)
         * - **name** (optional)
         * - **website** (optional)
         * - **icsUrl** (optional) - Will be linked to directly to download to native calendar on supporting devices
         */
        eventData: null,
        
        /**
         * Label for link, may include HTML
         */
        label: 'Add to calendar',
        
        cls: 'calendar-link',
        tpl: [
            '<a href="{url}" target="{target}">{label}</a>'
        ]
    },
    
    updateEventData: function(eventData) {
        var me = this,
            target = '_blank',
            gcalParams = {
                action: 'TEMPLATE',
                trp: 'true',
                sprop: []
            },
            url;
        
        if (!eventData) {
            me.hide();
            return;
        }
        
        if (eventData.icsUrl && Ext.os.is.iOS) {
            url = eventData.icsUrl;
            
            // iOS web
            if ('standalone' in navigator && !navigator.standalone && location.protocol.match(/^https?/i)) {
                target = '_self';
            }
        } else {
            gcalParams.text = eventData.title;
            gcalParams.dates = me.getUTCTimestamp(eventData.startTime) + '/' + me.getUTCTimestamp(eventData.endTime);
            
            if (eventData.location) {
                gcalParams.location = eventData.location;
            }
            
            if (eventData.details) {
                gcalParams.details = eventData.details;
            }
            
            if (eventData.name) {
                gcalParams.sprop.push('name:'+eventData.name);
            }
            
            if (eventData.website) {
                gcalParams.sprop.push('website:'+eventData.name);
            }
            
            url = 'https://www.google.com/calendar/event?' + Ext.Object.toQueryString(gcalParams);
        }
        
        me.setData({
            url: url,
            target: target,
            label: me.getLabel()
        });
        
        me.show();
    },
    
    getUTCTimestamp: function(date) {
        return [
            date.getUTCFullYear(),
            Ext.String.leftPad(date.getUTCMonth()+1, 2, '0'),
            Ext.String.leftPad(date.getUTCDate(), 2, '0'),
            'T',
            Ext.String.leftPad(date.getUTCHours(), 2, '0'),
            Ext.String.leftPad(date.getUTCMinutes(), 2, '0'),
            Ext.String.leftPad(date.getUTCSeconds(), 2, '0'),
            'Z'
        ].join('');
    }
});

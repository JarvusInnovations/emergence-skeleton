/**
 * A subclass of Ext.Mask that uses component hide/show instead of element hide/show so that
 * showAnimation/hideAnimation configuration works as expected.
 */
Ext.define('Jarvus.touch.ux.AnimatedMask', {
    extend: 'Ext.Mask',
    xtype: 'animatedmask',
    
    config: {
        showAnimation: 'fadeIn',
        hideAnimation: {
            type: 'fadeOut',
            duration: 100
        }
    },
    
    /**
     * Override {@link Ext.Component#method-doSetHidden} to use component hide/show methods
     */
    setHidden: function(hidden) {
        var me = this;

        if (me.animatingMask) {
            me.animatingMask = false;
            me.callParent(arguments);
            return;
        }
        
        me.animatingMask = true;
        
        if (hidden) {
            me.hide();
        } else {
            me.show();
        }
    }
});

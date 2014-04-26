/**
 * Prevents 'show' event from being fired on views as they are removed from a card layout
 * Bug report: http://www.sencha.com/forum/showthread.php?267848-2.2.1-show-event-fired-on-NavigationView-children-as-they-are-being-removed
 */
Ext.define('Jarvus.touch.patch.PreserveDestroyOnRemove', {
    override: 'Ext.Container',

    onItemRemove: function(item, index, destroying) {
        this.doItemLayoutRemove(item, index, destroying);

        this.fireEvent('remove', this, item, index);
    },

    doItemLayoutRemove: function(item, index, destroying) {
        var layout = this.getLayout();

        if (this.isRendered() && item.setRendered(false)) {
            item.fireAction('renderedchange', [this, item, false], 'onItemRemove', layout, { args: [item, index, destroying] });
        }
        else {
            layout.onItemRemove(item, index);
        }
    }
});

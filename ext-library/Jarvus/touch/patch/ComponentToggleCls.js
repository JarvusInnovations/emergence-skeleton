/**
 * Fixes incompatibility between Component.toggleCls and addCls/removeCls
 * Bug report: http://www.sencha.com/forum/showthread.php?267741-2.2.1-Component.toggleCls-does-not-update-internal-cls-config
 */
Ext.define('Jarvus.touch.patch.ComponentToggleCls', {
    override: 'Ext.Component',

    toggleCls: function(className, force) {
        var oldCls = this.getCls();
        this[!force && oldCls && Ext.Array.contains(oldCls, className) ? 'removeCls' : 'addCls'](className);
    }
});

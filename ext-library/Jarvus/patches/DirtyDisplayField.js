Ext.define('Jarvus.patches.DirtyDisplayField', {
    override: 'Ext.form.field.Display'

    ,isDirty: function() {
        return false;
    }

});
Ext.define('Jarvus.patch.SelectBlanks', {
    override: 'Ext.field.Select',

	// patch bug TOUCH-2830 http://www.sencha.com/forum/showthread.php?198216
	applyValue: function(value) {
        var record = value,
            index, store;

        //we call this so that the options configruation gets intiailized, so that a store exists, and we can
        //find the correct value
        this.getOptions();

        store = this.getStore();

        if ((value != undefined && value != null && !value.isModel) && store) {
            index = store.find(this.getValueField(), value, null, null, null, true);

            if (index == -1) {
                index = store.find(this.getDisplayField(), value, null, null, null, true);
            }

            record = store.getAt(index);
        }

        return record;
    },
	
	
    showPicker: function() {
        var store = this.getStore();
        //check if the store is empty, if it is, return
        if (!store || store.getCount() === 0) {
            return;
        }

        if (this.getReadOnly()) {
            return;
        }

        this.isFocused = true;

        //hide the keyboard
        //the causes https://sencha.jira.com/browse/TOUCH-1679
        // Ext.Viewport.hideKeyboard();

        if (this.getUsePicker()) {
            var picker = this.getPhonePicker(),
                name   = this.getName(),
                value  = {};

            value[name] = this.getValue();
            picker.setValue(value);
            if (!picker.getParent()) {
                Ext.Viewport.add(picker);
            }
            picker.show();
        } else {
            var listPanel = this.getTabletPicker(),
                list = listPanel.down('list'),
                store = list.getStore(),
                index = store.find(this.getValueField(), this.getValue(), null, null, null, true),
                record = store.getAt((index == -1) ? 0 : index);

            if (!listPanel.getParent()) {
                Ext.Viewport.add(listPanel);
            }

            listPanel.showBy(this.getComponent());
            list.select(record, null, true);
        }
    }
});
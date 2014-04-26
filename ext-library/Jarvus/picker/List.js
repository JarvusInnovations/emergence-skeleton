Ext.define('Jarvus.picker.List', {
	extend: 'Jarvus.picker.Abstract'
	,xtype: 'listpicker'
	,requires: [
		'Ext.dataview.List'
	]
	
	,config: {
		cls: 'listpicker'
		,stretchX: true
		,stretchY: true
		,destroyPickerOnHide: true
		
		,list: true
		,doneButton: {
			text: 'Clear'
		}
	}

	,applyList: function(config) {
		if (config === true)
		{
			config = {};
		}
		
		Ext.applyIf(config, {
			grouped: true
			,indexBar: true
			,allowDeselect: false
			,mode: 'SINGLE'
			,deselectOnContainerClick: false
			,cls: Ext.baseCSSPrefix + 'select-overlay'
		});
		
		return Ext.factory(config, 'Ext.dataview.List', this.getList());
	}

	,updateList: function(newList, oldList) {
		if(newList)
			this.add(newList);
		
		if(oldList)
			this.remove(oldList);
	}
	
	,getValue: function() {
		return this.getList().getSelection()[0];
	}
	
	,onShow: function() {
		this.callParent(arguments);
		
		var list = this.getList()
			,selectedEl = list.innerElement.down('.x-item-selected');
		
		if(selectedEl)
			list.getScrollable().getScroller().scrollTo(0, Math.max(0, selectedEl.getOffsetsTo(list.innerElement)[1]-50));
	}
	
	,onDoneButtonTap: function() {
		var list = this.getList();
		list.deselectAll();
		list.getScrollable().getScroller().scrollTo(0, 0);
			
		var oldValue = this._value;
		
		if (oldValue != '')
		{
			this.setValue('');
			this.fireEvent('change', this, '');
		}
		
		this.hide();
	}
});
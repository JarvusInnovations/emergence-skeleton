Ext.define('Jarvus.picker.Slotted', {
	extend: 'Jarvus.picker.Abstract'
	,xtype: 'picker'
	,requires: [
		'Ext.picker.Slot'
	]
	
	,config: {
		cls: Ext.baseCSSPrefix + 'picker'
		,useTitles: false
		,slots: null
		,height: 220
		,layout: 'hbox'
		,defaultType: 'pickerslot'
	}
	
	,initElement: function() {
		this.callParent(arguments);
		
		var clsPrefix = Ext.baseCSSPrefix
			,innerElement = this.innerElement;
		
		//insert the mask, and the picker bar
		this.mask = innerElement.createChild({
			cls: clsPrefix + 'picker-mask'
		});
		
		this.bar = this.mask.createChild({
			cls: clsPrefix + 'picker-bar'
		});
		
		this.on({
			scope: this
			,delegate: 'pickerslot'
			,slotpick: 'onSlotPick'
		});
		
		console.log('slotted picker initialized!');
	}
	
	
	,updateUseTitles: function(useTitles) {
		var innerItems = this.getInnerItems()
			,ln = innerItems.length
			,cls = Ext.baseCSSPrefix + 'use-titles'
			,i, innerItem;
		
		//add a cls onto the picker
		if (useTitles)
		{
			this.addCls(cls);
		}
		else
		{
			this.removeCls(cls);
		}
		
		//show the titme on each of the slots
		for (i = 0; i < ln; i++)
		{
			innerItem = innerItems[i];
		
			if (innerItem.isSlot)
			{
				innerItem.setShowTitle(useTitles);
			}
		}
	}

	,applySlots: function(slots) {
		//loop through each of the slots and add a referece to this picker
		if (slots)
		{
			var ln = slots.length
				,i;
		
			for (i = 0; i < ln; i++)
			{
				slots[i].picker = this;
			}
		}
		
		return slots;
	}
		
	,updateSlots: function(newSlots) {
		var bcss = Ext.baseCSSPrefix
			,innerItems;
		
		this.removeAll();
		
		if (newSlots)
		{
			this.add(newSlots);
		}
		
		innerItems = this.getInnerItems();
		if (innerItems.length > 0)
		{
			innerItems[0].addCls(bcss + 'first');
			innerItems[innerItems.length - 1].addCls(bcss + 'last');
		}
		
		this.updateUseTitles(this.getUseTitles());
	}
	
	,onSlotPick: function(slot) {
		this.fireEvent('pick', this, this.getValue(true), slot);
	}
	
	,setValue: function(values, animated) {
		var me = this
			,slots = me.getInnerItems()
			,ln = slots.length
			,key, slot, loopSlot, i, value;
		
		if (!values)
		{
			values = {};
			for (i = 0; i < ln; i++)
			{
				//set the value to false so the slot will return null when getValue is called
				values[slots[i].config.name] = null;
			}
		}
	
		for (key in values)
		{
			value = values[key];
			for (i = 0; i < slots.length; i++)
			{
				loopSlot = slots[i];
				if (loopSlot.config.name == key)
				{
					slot = loopSlot;
					break;
				}
			}
	
			if (slot)
			{
				if (animated)
				{
					slot.setValueAnimated(value);
				}
				else
				{
					slot.setValue(value);
				}
			}
		}
	
		me._values = values;
		
		return this.callParent([values, animated]);
	}

	,getValue: function(useDom) {
		var values = {}
			,items = this.getItems().items
			,ln = items.length
			,item, i;
	
		if (useDom)
		{
			for (i = 0; i < ln; i++)
			{
				item = items[i];
				if (item && item.isSlot)
				{
					values[item.getName()] = item.getValue(useDom);
				}
			}
			
			this._values = values;
		}
	
		return this._values;
	}
	
	,destroy: function() {
		this.callParent();
		Ext.destroy(this.mask, this.bar);
	}
});
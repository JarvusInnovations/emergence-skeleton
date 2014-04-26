Ext.define('Jarvus.patch.PopSelectors', {
	override: 'Ext.navigation.View'
	
	,beforePop: function(target) {
		
		// resolve count via component query or instance via object
		if(Ext.isString(target) || Ext.isObject(target))
		{
			var innerItems = this.getInnerItems()
				,last = innerItems.length-1;
				
			for(var i = last; i >= 0; i--)
			{
				if(
					(Ext.isString(target) && Ext.ComponentQuery.is(innerItems[i], target))
					|| (Ext.isObject(target) && target == innerItems[i])
				)
				{
					target = last - i;
					break;
				}
			}
			
			if(!Ext.isNumber(target))
				return false;
		}
		
		return this.callParent([target]);
	}
});
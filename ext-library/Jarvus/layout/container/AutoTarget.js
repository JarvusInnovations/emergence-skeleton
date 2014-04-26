/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * The AutoTarget layout manager extends AutoLayout, but overrides calculateContentSize to rely on the natural CSS-induced
 * sizing of the container's targetEl to measure layout sizing instead of interrogating children, allowing you to clip
 * child content without the extra space being read by the layout engine
 * 
 * See discussion: http://www.sencha.com/forum/showthread.php?230606-container.Auto-layout-looks-at-children-for-height
 */
Ext.define('Jarvus.layout.container.AutoTarget', {
    extend: 'Ext.layout.container.Auto'
    ,alias: 'layout.autotarget'
    
    ,type: 'autotarget'
    
    // ignore child sizes and just rely on CSS to set targetEl correctly
	,calculateContentSize: function(ownerContext, dimensions) {
		if(ownerContext.widthModel.shrinkWrap)
		{
			if(!ownerContext.setContentWidth(ownerContext.targetContext.el.getWidth()))
			{
				this.done = false;
			}
		}
		
		if(ownerContext.heightModel.shrinkWrap)
		{
			if(!ownerContext.setContentHeight(ownerContext.targetContext.el.getHeight()))
			{
				this.done = false;
			}
		}
	}
});
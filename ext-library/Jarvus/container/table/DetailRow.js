/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A container for a table row made of a single cell that spans the entire row.
 * Child items are rendered into a wrapping div tag within the cell that provides
 * for transitioning the contents of the cell on- and off- screen.
 */
Ext.define('Jarvus.container.table.DetailRow', {
	extend: 'Ext.container.Container'
	,xtype: 'detailrow'
	,requires: [
		'Ext.layout.component.Body'
		,'Jarvus.layout.container.AutoTarget'
	]
	
	/**
	 * @cfg {Number} colSpan (required)
	 * The number of columns in the parent table. This is automatically set
	 * when used with {@link Jarvus.container.table.Expando}.
	 */
	,colSpan: 1
	
	/**
	 * @cfg {String}
	 * Default xtype for child items
	 */
	,defaultType: 'component'
	
	,componentLayout: 'body'
	,layout: 'autotarget'
	
	,autoEl: 'tr'
	,baseCls: 'details-row'
	,childEls: ['body']
	,renderTpl: [
		'<td id="{id}-innerCell"<tpl if="colSpan &gt; 1"> colspan="{colSpan}"</tpl>>'
			,'<div id="{id}-body" class="td-ct">'
				,'{%this.renderContainer(out,values)%}'
			,'</div>'
		,'</td>'
	]
	
	,initRenderData: function() {
		var me = this;
		return Ext.apply(me.callParent(), {
			colSpan: me.colSpan
		});
	}
	
	,getTargetEl: function() {
		return this.body;
	}
});
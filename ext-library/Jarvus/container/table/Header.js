/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A header section for tables that contains a set of column headers and
 * optionally a headline row and/or validation error row.
 * 
 * Creates a thead tag.
 */
Ext.define('Jarvus.container.table.Header', {
	extend: 'Jarvus.container.table.Segment'
	,xtype: 'tableheader'
	
	/**
	 * @cfg {Object[]} columns (required)
	 * A list of column headers
	 * 
	 * @cfg {String} columns.title (required) Title for the column header
	 * @cfg {String} columns.colCls A CSS class for the group that will be prefixed with 'col-'
	 * @cfg {String} columns.colGroup A group for the column that will be added as a CSS class with the 'group-' prefix
	 * @cfg {Number} columns.colSpan Number of columns the header's th should span
	 * @cfg {Boolean} columns.hidden True to initially hide the column header
	 */
	,columns: []
	
	/**
	 * @cfg {String}
	 * Some markup to insert at the top of this thead. The string will be wrapper within a
	 * tr tag and so must only contain td and/or th tag(s) at the top level
	 */
	,superHeader: false
	
	,autoEl: 'thead'
	,renderTpl: [
		'<tpl if="superHeader"><tr class="merge-with-next">{superHeader}</tr></tpl>'
		,'<tr>'
			,'<tpl for="columns">'
				,'<th class="col-{colCls}<tpl if="colGroup"> group-{colGroup}</tpl>"<tpl if="colSpan"> colspan={colSpan}</tpl><tpl if="hidden"> style="display:none"</tpl>>{title}</th>'
			,'</tpl>'
		,'</tr>'
	]
	
	,beforeRender: function() {
		var me = this
			,superHeader = me.superHeader;
		
		Ext.apply(me.renderData, {
			columns: me.columns
			,superHeader: Ext.isArray(superHeader) ? superHeader.join('') : superHeader
		});
		
		me.callParent();
	}
	
	/**
	 * Set an error message to be displayed under the column headers row.
	 * The error will be shown immediately and the row to contain it will be
	 * rendered on first use.
	 */
	,setError: function(message) {
		var me = this
			,errorEl = me._errorEl
			,colSpan = 0;
			
		if(errorEl)
		{
			errorEl.down('td').update(message);
			errorEl.show();
		}
		else
		{
			Ext.Array.sum(Ext.Array.map(me.columns, function(column) {
				colSpan += (column.colSpan || 1);
			}));
			
			Ext.DomHelper.useDom = true;
			me._errorEl = me.el.createChild({
				tag: 'tr'
				,cls: 'table-error'
				,cn: {
					tag: 'td'
					,html: message
					,colSpan: colSpan
				}
			}).setVisibilityMode(Ext.Element.DISPLAY);
			Ext.DomHelper.useDom = false;
		}
		
		me.doLayout();
	}
	
	/**
	 * Clear an error set with {@link #setError} and hides the error row
	 */
	,clearError: function() {
		var me = this
			,errorEl = me._errorEl;
		
		if(errorEl)
		{
			errorEl.hide();
			me.doLayout();
		}
	}
});
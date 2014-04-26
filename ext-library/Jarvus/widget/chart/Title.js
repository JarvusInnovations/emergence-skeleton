/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A simple h1 component for titling a {@link Jarvus.widget.chart.Container chart container}.
 */
Ext.define('Jarvus.widget.chart.Title', {
	extend: 'Ext.Component'
	,xtype: 'charttitle'
	
	,autoEl: 'h1'
	,componentCls: 'chart-title'
	
	/**
	 * @cfg {Function}
	 * A function to be called within the scope of the chart title when the associated chart is updated
	 */
	,onChartRefresh: Ext.emptyFn
});
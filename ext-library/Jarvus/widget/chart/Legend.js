/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * An template-based legend for charts within a {@link Jarvus.widget.chart.Container chart container}.
 */
Ext.define('Jarvus.widget.chart.Legend', {
	extend: 'Ext.Component'
	,xtype: 'chartlegend'
	
	,autoEl: 'ul'
	,componentCls: 'chart-legend'
	,tpl: [
		'{% var renderValue = values.renderValue %}'
		,'<tpl for="series">'
			,'<tpl for=".">'
				,'<li>'
					,'<span class="swatch" style="background-color: {color}"></span>'
					,'<span class="swatch-name">{label}</span>'
					,'<tpl if="value"><span class="value">{[renderValue ? renderValue(values.value) : values.value]}</span></tpl>'
				,'</li>'
			,'</tpl>'
		,'</tpl>'
	]
	
	,onChartRefresh: function(chart) {
		var me = this
			,data = {
				series: []
				,renderValue: me.renderValue
			};
		
		chart.series.each(function(series) {
			if(series.visibleInLegend)
			{
				data.series.push(me.getSeriesData(series));
			}
		});
		
		me.update(data);
	}
	
	,renderValue: function(value) {
		return Ext.util.Format.currency(value, "$", 0);
	}
	
	,getSeriesData: function(series) {
		var legendData = []
			,i = series.yField.length-1;
		
		for(; i >= 0; i--)
		{
			legendData.push({
				label: series.yField[i]
				,color: series.getLegendColor(i)
				,value: this.getSeriesItemValue(series, i)
			});
		}
		
		return legendData;
	}
	
	,getSeriesItemValue: function(series, index) {
		return series.items[index].storeItem.get(series.field);
	}
});
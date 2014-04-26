/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext,POC*/
/**
 * @deprecated
 */
Ext.define('Jarvus.container.CheckboxGroups', {
	extend: 'Jarvus.container.CheckboxNav'
	,xtype: 'checkboxgroups'
	,requires: [
		'Jarvus.container.CheckboxNav'	
	]
	,initialize: function(){
		var me = this;
		me.callParent(arguments);
	
		me.store = Ext.getStore(me.config.store);
		me.store.on('refresh', me.renderItems, me, {delay: 10});
		me.element.on('tap', me.toggleGroup, me, {delegate: '.accordion-header'});
		me.element.on('tap', me.filterChange, me, {delegate: 'li.category'});
		me.renderItems();
		
	}
	,bufferedFilter: null
	,filterChange: function(evt, t){
		var me = this;
		
		if(me.bufferedFilter)
			clearTimeout(me.bufferedFilter);
			
		me.bufferedFilter = setTimeout(Ext.bind(me.doFilterChange,me),750, evt, t);
	}
	,doFilterChange: function(evt, t){
		var checkboxes = Ext.select('.category input')
			,filters = []
			,list = evt.getTarget('ul', null, true)
			,input = t.type == "checkbox" ? t : Ext.fly(t).down('input').dom;
		
		if(t.tagName == 'DIV') //in case user clicks on load mask it won't try to load anything. Though when the load mask is up it should have been disabled. Seems like it doesn't disable plain html inputs in a container
			return false;
		
		if(input.value == 'All' && input.checked)
		{
			var groupElements = list.select('input[type=checkbox]', list.dom);

			Ext.each(groupElements.elements, function(item){
				if(item.value != 'All')
					item.checked = false;
			},this);
			
			this.fireEvent('filterchange', this, filters);
			
			return this.bufferedFilter = null;
		}
		
		evt.getTarget('section', null, true).query('input[value=All]')[0].checked = false;
		
		Ext.each(checkboxes.elements, function(item){
			if(item.checked && item.value != 'All')
				filters.push(item.value);
		}, this);
//		
//		if(!toggledOn)
//			Ext.Array.remove(filters, t.value);
			
		filters = Ext.Array.clean(Ext.Array.unique(filters));

		this.fireEvent('filterchange', t, filters);
		return this.bufferedFilter = null;
	}
	,toggleGroup: function(evt, t){
	//	console.log(evt, t);
		var el = Ext.fly(t).up('.accordion-section')
			,groups = Ext.select('.accordion-section');
			
		if(!el.hasCls('selected'))
		{
			Ext.each(groups.elements, function(item){
				Ext.fly(item).removeCls('selected');
			}, this);
			
			el.addCls('selected');	
		}
	}
	,renderItems: function(){
		var me = this
			,groupField = me.config.groupField
			,valueField = me.config.valueField
			,displayField = me.config.displayField ? me.config.displayField : me.config.valueField;

		
		me.setMasked({
			xtype: 'loadmask'
			,message: 'Updating&hellip;'
		});
		if(!me.store.getGroupField())
			me.store.setGroupField(groupField);
			
		var groups = me.store.getGroups()
			,html = '<div class="categories accordion">';
			
		Ext.each(groups, function(item, index){
			html += '<section class="'+(index===0 && this.config.expandFirst ? ('selected ') : '')+' accordion-section">'
					+ '<h1 class="accordion-header">'+item.name+'</h1><ul class="accordion-body">';

			Ext.each(item.children, function(record){
				var value = record.get(valueField)
					,label = record.get(displayField)
//					,checkboxItemId = value.replace(/[^\w\d]/g, '')
					,isAll = value == 'All';
				
				html += '<li class="category">'
						+ '<label>'
							+ '<input type="checkbox"'+(isAll ? 'checked' : '')+' value=\''+value+'\' name="facet" />'
							+ label
						+'</label>'
					+ '</li>';
			}, me);

			html += '</ul></section>';
		}, me);
		
		
		html += '</div>';
		
		me.setHtml(html);
			
//		me.store.each(function(record){
//			var group = record.get(groupField)
//				,itemId = group.replace(/\s+/, '')
//				,panel = this.down('#' + itemId);
//				
//			if(!panel)
//			{
//				panel = this.add({
//					itemId: itemId
//					,renderTpl: '<div class="categoryGroup">{group}</div>'
//					,renderData: {
//						group: group
//					}
//				});
//			}
//			if(!panel.down(('#' + checkboxItemId)))
//			{
//				panel.add({
//					xtype: 'checkboxfield'
//					,labelWrap: true
//					,labelAlign: 'right'
//					,value: value
//					,labelWidth: '75%'
//					,label: label
//					,itemId: checkboxItemId
//					,width: '100%'
//				});
//			}
//		}, me);
		
		me.setMasked(false);
	}
	,getDetailedChecked: function(){
		var checkBoxes = Ext.select('li.category input')
			,checked = {};
			
		Ext.each(checkBoxes.elements, function(item){
			if(item.checked && item.value != 'All')
			{
				var header = Ext.fly(item).up('ul').prev('h1').dom.innerHTML
					,label = Ext.fly(item).up('label').dom.innerText;
				
				if(!checked[header])
					checked[header] = {};
					
				checked[header][label] = item.value;
			}
		});

		return checked;
	}
	,getChecked: function(){
		var checkBoxes = Ext.select('li.category input')
			,checked = [];
			
		Ext.each(checkBoxes.elements, function(item){
			if(item.checked && item.value != 'All')
				checked.push(item.value);
		});
		return checked;
	}
});
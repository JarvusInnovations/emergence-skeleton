Ext.define('Emergence.Editor.view.SearchResults', {
	extend: 'Ext.panel.Panel'
	,alias: 'widget.search-results'
	,layout: 'fit'
	,initComponent: function() {
		
		/*this.dockedItems = [{
			xtype: 'toolbar'
			,dock: 'top'
			,items: [
			'->'
				,{
			         xtype: 'textfield'
			         ,name: 'globalSearch'
			         ,hideLabel: true
			         ,width: 200
				}
				,{
					xtype: 'button'
					,text: 'Search'
				}
			]
		}];*/
		
		this.items = [{
			xtype: 'dataview'
			,tpl: new Ext.XTemplate(
				'<section class="activity-feed">'
				,'<tpl for=".">'
					,'<article class="feed-item">'
						,'<span class="file">'
        					,'<a class="filename" href="#/{File.FullPath}:{line}" title="/{File.FullPath}:{line}">{File.Handle}</a>'
        				,'</span>'
						,'Line {line}<pre>{[this.parseResult(values.result)]}</pre>'
					,'</article>'
				,'</tpl>'
				,'</section>'
				,{
					parseResult: function(result) {
						return result.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					}
				}
			)
			,itemSelector: 'article'
			,emptyText: 'Nothing found.'
			//,store: 'SiteSearch'
			,autoScroll: true
		}];
		
		this.callParent(arguments);
	}
});
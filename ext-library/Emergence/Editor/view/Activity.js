Ext.define('Emergence.Editor.view.Activity', {
    extend: 'Ext.panel.Panel'
	,alias: 'widget.emergence-activity'
	,title: 'Activity'
	,layout: 'fit'
	,initComponent: function() {
		
		this.dockedItems = [{
			xtype: 'toolbar'
			,dock: 'bottom'
			,items: [{
    			xtype: 'button'
				,text: 'Refresh Activity'
				,action: 'refresh'
				,iconCls: 'refresh'
			},'->',{
    			xtype: 'button'
				,text: 'Load Full History'
				,action: 'load-all'
				,iconCls: 'refresh'
			}]
		}];
		
		this.items = [{
			xtype: 'dataview'
			,tpl: [
				'<section class="activity-feed">'
				,'<tpl for=".">'
					,'<article class="feed-item">'
						
						// user
						,'<figure class="user">'
							//,'<img src="/thumbnail/person/{Author.ID}/29x29xFFFFFF" width=29 height=29>'
							,'<img src="' + Ext.BLANK_IMAGE_URL + '" style="background-image:url(/thumbnail/person/{Author.ID}/58x58)" width=29 height=29>'
							,'<figcaption>{Author.Username}</figcaption>'
						,'</figure>'

						// save actions (create, edit, create & edit)
						,'<tpl if="EventType == \'save\'">'
							,'<tpl if="revisionsCount == 1 && !revisions[0].AncestorID"><span class="action created">created</span></tpl>'
							,'<tpl if="revisions[0].AncestorID"><span class="action edited">edited</span></tpl>'
							,'<tpl if="revisionsCount &gt; 1 && !revisions[0].AncestorID"><span class="action created edited">created &amp; edited</span></tpl>' //create & edit
						,'</tpl>'
						
						// saved file
						,'<tpl if="EventType != \'delete\'">'
							,'<span class="file">'
	        					,'<span class="path">'
	        						,'<tpl if="Collection.ParentID">&hellip;</tpl>'
	        						,'/{Collection.Handle}/'
	        					,'</span>'
	        					,'<a class="filename" href="#/{CollectionPath}/{Handle}" title="/{CollectionPath}/{Handle}">{Handle}</a>'
	    						,'<tpl if="revisionsCount &gt; 1"><span class="revisions"><span class="count">{revisionsCount}</span> times</span></tpl>'
	                            ,'<tpl if="FirstAncestorID && RevisionID"><a class="compare" href="#diff:[{FirstAncestorID},{RevisionID}]/{CollectionPath}/{Handle}">compare</a></tpl>'
                            ,'</span>'
						,'</tpl>'

    					,'<tpl if="EventType == \'delete\'">'
    						// delete action
							,'<span class="action deleted">deleted</span>'
                            
                            // deleted file
                            ,'<tpl if="values.files.length == 1">'
                            ,'<tpl for="files">'
            				    ,'<span class="file">'
            				    	,'<span class="path">'
	            				    	,'<tpl if="Collection.ParentID">&hellip;</tpl>'
	            				    	,'/{Collection.Handle}/'
            				    	,'</span>'
            				    	,'<a class="filename" href="#/{CollectionPath}/{Handle}" title="/{CollectionPath}/{Handle}">{Handle}</a>'
            				    ,'</span>'
            				,'</tpl>'
                            ,'</tpl>'
                            
                            // deleted files
                            ,'<tpl if="values.files.length != 1">'
                            	,'<span class="file"><span class="count">{[values.files.length]}</span> files</span>'
                            ,'</tpl>'
						,'</tpl>'
						
						// timestamp
						,'<time class="timestamp">'
							,'<tpl if="FirstTimestamp && FirstTimestamp.getTime() != Timestamp.getTime()">'
								,'{FirstTimestamp:date("M j, g:i a")}&thinsp;&ndash;&thinsp;{Timestamp:date("g:i a")}'
							,'</tpl>'
							,'<tpl if="!FirstTimestamp || FirstTimestamp.getTime() == Timestamp.getTime()">'
								,'{Timestamp:date("M j, g:i a")}'
							,'</tpl>'
						,'</time>'

					,'</article>'
				,'</tpl>'
				,'</section>'
			]
			,itemSelector: 'article'
			,emptyText: 'No activity'
			,store: 'ActivityStream'
			,autoScroll: true
		}];
		
		
		this.callParent(arguments);
	}
	
});
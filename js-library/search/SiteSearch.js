Ext.ns('Jarvus.search');

Jarvus.search.SiteSearch = Ext.extend(Ext.util.Observable, {

    formEl: 'search'
    ,resultsCtId: 'search-results'
    ,queryEl: 'search-box'
    ,minChars: 2
    ,searchDelay: 300
    ,handlerURL: '/search/json'
    ,searchingCls: 'results-loading'
    ,searchingText: 'Searching&hellip;'
    ,noResultsCls: 'no-results'
    ,noResultsText: 'No results'
    ,moreResultsCls: 'more-results'
    ,groupResultsLimit: 5
    ,thumbFormat: '48x48'
    ,renderers: false
    ,groupTitles: false
    
    ,constructor: function(config) {
    
		Ext.apply(this, config);
		
		Ext.onReady(this.initPage, this);
	}
	
	
	,initPage: function() {
	
		// get Elements
		this.formEl = Ext.get(this.formEl);
		this.queryEl = Ext.get(this.queryEl);
		
		this.queryPlaceholder = this.queryEl.getAttribute('placeholder');
		
		// configre elements
		this.formEl.setStyle('position', 'relative');
		this.queryEl.dom.setAttribute('autocomplete', 'off');
		
		// wire events
		this.queryEl.on('keyup', this.onQueryKey, this);
		this.queryEl.on('focus', this.onQueryFocus, this);
		
		Ext.getBody().on('click', this.onBodyClick, this);
	}
	
	,onBodyClick: function(event, target) {
	
		if(this.resultsCt && this.resultsCt.isVisible() && !event.within(this.formEl))
		{
			this.resultsCt.fadeOut();
		}
	
	}

	,onQueryFocus: function(event, target) {
	
		if(this.resultsCt && !this.resultsCt.isVisible())
		{
			this.resultsCt.fadeIn();
		}
	
	}
	
	,onQueryKey: function(event, target) {

		if(target.value.length == 0 && this.resultsCt)
			this.resultsCt.fadeOut();
							
		// execute delayed search
		if(!this.searchTask)
		{
			this.searchTask = new Ext.util.DelayedTask(this.executeSearch, this);
		}
		this.searchTask.delay(this.searchDelay);

	}
	
	,executeSearch: function(query) {
	
		query = query || this.queryEl.getValue();
		
		if(query.length < this.minChars)
			return;
		
		if(query == this.queryPlaceholder)
			return;
			
		if(query == this.lastQuery)
			return;
			
		// show results container
		this.initResultsCt();

		if(this.searchConnection)
		{
			// abort any existing search
			this.searchConnection.abort();
		}
		else
		{
			// initialize search connection
			this.searchConnection = new Ext.data.Connection({
				url: this.handlerURL
				,method: 'GET'
				,listeners: {
					scope: this
					,requestcomplete: this.onResults
				}
			});
		}
		
		//console.info('Executing search: %o', query);
		this.searchConnection.request({
			params: {
				q: query
			}
		});
		
		this.lastQuery = query;
		
		this.showSearchingText();
	}

	,initResultsCt: function() {
	
		if(!this.resultsCt)
		{
			this.resultsCt = Ext.DomHelper.append(this.formEl, {
				tag: 'section'
				,id: this.resultsCtId
			}, true);
			
			this.showSearchingText();
		}
		
		if(!this.resultsCt.isVisible())
			this.resultsCt.fadeIn();
	}
	
	,showSearchingText: function() {
	
		Ext.DomHelper.overwrite(this.resultsCt, {
			cls: this.searchingCls
			,html: this.searchingText
		});
	
	}
	
	,showNoResults: function() {
	
		Ext.DomHelper.overwrite(this.resultsCt, {
			cls: this.noResultsCls
			,html: this.noResultsText
		});
	
	}
	
	,onResults: function(connection, response, options) {
	
		var r = Ext.decode(response.responseText)
			,groupsCount = 0
			,resultsCount = 0;
		
		if(!r.data)
		{
			this.showNoResults();
			return;
		}
		
		this.loadTypes();
		this.resultsCt.update('');
		
		for(var group in r.data)
		{
			if(r.data[group].length == 0)
				continue;
		
			groupsCount++;
			
			var groupResults = 0
				,groupTotal = r.data[group].length
				,spec = {
					tag: 'ul'
					,cn: [{
						tag: 'h1'
						,html: (this.groupTitles[group] || group)
					}]
				};
			
			Ext.each(r.data[group], function(result) {
			
				resultsCount++;
				groupResults++;
				
				var resultSpec = {
					tag: 'li'
					,cn: []
				};
							
				if(groupResults <= this.groupResultsLimit)
				{
					if(this.renderers[result.Class])
					{
						var r = this.renderers[result.Class];
						
						while(typeof r == 'string')
							r = this.renderers[r];
						
						resultSpec.cn = r.call(this, result);
					}
					else
					{
						if(result.Title)
						{
							resultSpec.cn.push(result.Title);
						}
						else
						{
							resultSpec.cn.push(result.Class+' #'+result.ID);
						}
					}
					spec.cn.push(resultSpec);
				}
				else
				{
					resultSpec.cn = {
						tag: 'a'
						,cls: this.moreResultsCls
						,href: '/search?q='+escape(options.params.q)+'#results-'+group
						,html: (groupTotal - groupResults) + ' more&hellip;'
					};
					
					spec.cn.push(resultSpec);
					return false;
				}
			
			}, this);
			
			
			// append group results to container
			Ext.DomHelper.append(this.resultsCt, spec);
		}
		
		// no results =[
		if(resultsCount == 0)
		{
			this.showNoResults();
		}
	}
	
	
	,loadTypes: function() {
	
		this.renderers = this.renderers || {};
		
		Ext.apply(this.renderers, {
			Product: function(result) {
				var cn = [];
			
				if(result.PrimaryPhotoID)
					cn.push({
						tag: 'img'
						,src: '/thumbnail/'+result.PrimaryPhotoID+'/'+this.thumbFormat
					});
					
				cn.push(result.Title);
				cn.push({
					tag: 'span'
					,cls: 'price'
					,html: '$'+result.Price
				});
				
				return {
					tag: 'a'
					,href: '/products/'+result.Handle	
					,cn: cn
				};
			}
			
			,User: function(result) {
				var cn = [];
			
				if(result.PrimaryPhotoID)
					cn.push({
						tag: 'img'
						,src: '/thumbnail/'+result.PrimaryPhotoID+'/'+this.thumbFormat
					});
					
				cn.push(result.FirstName+' '+result.LastName);
				
				return {
					tag: 'a'
					,href: '/users/'+result.Username	
					,cn: cn
				};
			}
			
			,Person: function(result) {
				var cn = [];
			
				if(result.PrimaryPhotoID)
					cn.push({
						tag: 'img'
						,src: '/thumbnail/'+result.PrimaryPhotoID+'/'+this.thumbFormat
					});
					
				cn.push(result.FirstName+' '+result.LastName);
				
				return {
					tag: 'a'
					,href: '/people/'+result.ID	
					,cn: cn
				};
			}
			
			,Event: function(result) {
				return {
					tag: 'a'
					,href: '/events/'+result.Handle	
					,html: result.Title
				};
			}
		});
	
	
	
		this.groupTitles = this.groupTitles || {};
		
		Ext.apply(this.groupTitles, {
			User: 'Users'
			,Person: 'People'
			,Product: 'Products'
			,Event: 'Events'
		});
	}

});

/*
<section id="search-results">
	<ul>
		<h1>Category 1</h1>
		<li><img src="/thumbnail/263/48x48x">foo result 1</li>
		<li><img src="/thumbnail/263/48x48x">Dummy result 2</li>
		<li><img src="/thumbnail/263/48x48x">Dummy result 3</li>
	</ul>
	<ul>
		<h1>Category 2</h1>
		<li>Dummy result 1</li>
	</ul>
	<ul>
		<h1>Category 3</h1>
		<li>Dummy result 1</li>
		<li>Dummy result 2</li>
		<li><img src="/thumbnail/263/48x48xFFFFFF">Dummy result 3 with a really long-ass name</li>
		<li><img src="/thumbnail/263/48x48xFFFFFF">Dummy result 4</li>
		<li>Dummy result 5</li>
	</ul>
</section>
					
*/
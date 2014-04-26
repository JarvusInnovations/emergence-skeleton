declare('Media::MediaRenderer', function(use) {
	use([], function() {
    
		Media.MediaRenderer = function(){
		    return {
		    
		    	classRenderers: {
		    		PhotoMedia: 'renderPhoto'
		    		,VideoMedia: 'renderPhoto'
		    		,PDFMedia: 'renderPhoto'
		    		,AudioMedia: 'renderPhoto'
		    	}
		    	
		    	,renderMedia: function(mediaRecord, options) {
		    		options = options || {};
		    		//console.log('rendering media: %o with options %o', mediaRecord, options);
		    	
		    		var r = Media.MediaRenderer.classRenderers[mediaRecord.get('Class')];
		    		
		    		if(r)
		    			return Media.MediaRenderer[r](mediaRecord, options);
		    		else
		    			return 'Unable to render media of class '+mediaRecord.get('Class');
		    	
		    	}
		    	
		    	,renderPhoto: function(mediaRecord, options) {		    	
		    		return Ext.DomHelper.markup({
		    			tag: 'a'
		    			,href: this.getThumbnailURL(mediaRecord, {width:1000,height:1000})
		    			,target: '_blank'
		    			,cls: 'attached-media-link'
		    			,title: mediaRecord.get('Caption')
		    			,children: {
			    			tag: 'img'
			    			,src: this.getThumbnailURL(mediaRecord, options)
			    			,alt: mediaRecord.get('Caption')
			    		}
		    		});
		    	}
		    	
		    	,renderVideo: function(mediaRecord, options) {
		    		return Ext.DomHelper.markup({
		    			tag: 'a'
		    			,href: this.getThumbnailURL(mediaRecord, {width:1000,height:1000})
		    			,target: '_blank'
		    			,cls: 'attached-media-link video-link'
		    			,title: mediaRecord.get('Caption')
		    			,children: {
			    			tag: 'img'
			    			,src: this.getThumbnailURL(mediaRecord, options)
			    			,alt: mediaRecord.get('Caption')
			    		}
		    		});
		    	}
		    	
		    	,getThumbnailURL: function(mediaRecord, options) {
		    	
		    		var thumbURL = '/thumbnail/'+mediaRecord.id;
		    		
		    		if(options.width && options.height)
		    		{
		    			thumbURL += '/'+options.width+'x'+options.height;
		    			
		    			if(options.fillColor)
		    				thumbURL += 'x'+options.fillColor;
		    		}
		    		
		    		return thumbURL;
		    	}
		    	
		    };
		}();
		
	});
});
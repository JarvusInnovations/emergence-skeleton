Ext.define('ExtUx.Util.File', {
	singleton: true
	
	,icons: {
		'default'					: '/img/icons/filetypes/default.png'
		,'application/pdf'			: '/img/icons/filetypes/pdf.png'
		,'application/psd'			: '/img/icons/filetypes/psd.png'
		,'application/postscript'	: '/img/icons/filetypes/eps.png'
		,'application/illustrator'	: '/img/icons/filetypes/ai.png'
		,'image/jpeg'   			: '/img/icons/filetypes/jpg.png'
		,'image/png'				: '/img/icons/filetypes/png.png'
		,'image/gif'				: '/img/icons/filetypes/gif.png'
		,'image/svg+xml'			: '/img/icons/filetypes/svg.png'
	}
	
	,getIcon: function(media) {
		// normalize input, record, data object, or mimeType string
		var mimeType, filename;
		
		if(Ext.isString(media))
		{
			mimeType = media;
		}
		else
		{
			if(media.isModel)
				media = media.data;
			
			mimeType = media.MIMEType;
			filename = media.OriginalFileName;
		}
		
		// detect AI file
		if((mimeType == 'application/pdf' || mimeType == 'application/postscript') && filename && filename.match(/\.ai$/i))
			mimeType = 'application/illustrator';
		
		// return icon path
		if(this.icons.hasOwnProperty(mimeType))
			return this.icons[mimeType];
		else
			return this.icons['default'];
	}

});
/*
 * SWFUpload adapter for Ext
 * Author: Chris Alfano (chris@devnuts.com) - Sep 06,2009
 * 
 * See SWFUpload documentation: http://demo.swfupload.org/Documentation/#settingsobject
 */
declare('Ext::ux::SWFUpload', function(use) {
    use([{
    	name: 'SWFUpload'
    	,URL: '/jslib/SWFUpload/swfupload.js'
    },'Ext::ux::SWFUpload.css'] , function(use) {
		
		Ext.ux.SWFUpload = Ext.extend(Ext.BoxComponent, {
		
			autoStart: true
			,debugMode: false
			,targetUrl: '/media/json/upload'
			,fieldName: 'mediaFile'
			,swfUrl: '/jslib/SWFUpload/Flash/swfupload.swf'
			,swfUploadCfg: {}
			,relayCookies: false
			,baseParams: []
			,progressBar: false
		
			,initComponent: function() {
			
				if(!this.relayCookies)
					this.relayCookies = [MICS.SessionCookie || 's'];
			
				// init error state
				this.errorOccurred = false; 
			
				// generate ID for SWFUpload container
				this.flashContainerId = Ext.id(null, 'swfupload');
				
				// register custom events
				this.addEvents({
					'ready': true
					,'queueError': true
					,'filesSelected': true
					,'fileQueued': true
					,'uploadError': true
					,'uploadStarted': true
					,'uploadProgress': true
					,'uploadComplete': true
					,'uploadResponse': true
					,'allUploadsComplete': true
				});
			
				// call parent
				this.superclass().initComponent.apply(this, arguments);
				
			}
			
			,onRender: function(container, position) {
			
				// call parent
				this.superclass().onRender.apply(this, arguments);
			
				this.photoUploaderSpan = this.el.createChild({
					tag: 'span'
					,id: this.flashContainerId
					,style: 'margin: 2px'
				});
		
		
				var params = Ext.apply({},this.baseParams);
				Ext.each(this.relayCookies, function(cookieName) {
					params[cookieName] = Ext.util.Cookies.get(cookieName)
				});
				
		
				// create SWF uploader
				var config = this.getConfig(params);
				//console.log('Creating with %o', config);
				this.SWFUpload = new SWFUpload(config);
			}
			
			
			,setPostParams: function(params) {
				
				for(var field in params)
				{
					this.addPostParam(field, params[field]);
				}
				
			}
			
			,addPostParam: function(field, value)
			{
				this.SWFUpload.addPostParam(field, value);
			}
			
			
			,getConfig: function(params) {
				
				return Ext.apply({
					// backend settings
					debug: this.debugMode
					,upload_url: this.targetUrl
					,file_post_name: this.fieldName
					,flash_url: this.swfUrl
					,post_params: params
								
					// trigger settings
					,button_placeholder_id: this.flashContainerId
					,button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT
					,button_width: 100
					,button_height: 22
					,button_image_url: '/img/icons/blank_upload_button.png'
					,button_text_left_padding: 20
					,button_text_top_padding: 2
			
					,file_size_limit: "16 MB"
					,file_types: '*.jpg; *.jpeg; *.png; *.gif; *.JPG; *.JPEG; *.PNG; *.GIF'
					,file_types_description: 'Media'
					,file_upload_limit: "0"
					
					// event handlers
					,swfupload_loaded_handler:function() {
					
						if(false!==this.fireEvent('ready') && this.progressBar)
						{
							this.progressBar.updateProgress(0, 'Ready to upload media', false);
							this.progressBar.enable();
						}
						
					}.createDelegate(this)
					
					,file_queue_error_handler: function(file, errorCode, errorMessage) {
						
						this.errorOccurred = true;
					
						if(false!==this.fireEvent('queueError', file, errorCode, errorMessage))
						{
							Ext.Msg.alert('Upload Failed', 'This file cannot be uploaded:<br><br>'+errorMessage);
						}
										
					}.createDelegate(this)
					
					,upload_error_handler: function(file, errorCode, errorMessage) {
						
						this.errorOccurred = true;
					
						if(false!==this.fireEvent('uploadError', file, errorCode, errorMessage))
						{
							Ext.Msg.alert('Upload Failed', 'An error occured while uploading your file:<br><br>'+errorMessage);
						}
						
					}.createDelegate(this)
					
					,file_dialog_complete_handler: function(numFilesSelected, numFilesQueued, queueTotal) {
						
						// reset error state
						this.errorOccurred = false;
					
						if(false!==this.fireEvent('filesSelected', numFilesSelected, numFilesQueued, queueTotal) && this.progressBar)
						{
							if(numFilesQueued)
								this.progressBar.updateProgress(0, 'Preparing to upload '+queueTotal+(queueTotal>1?' files...':' file...'), false);
							else
								this.progressBar.updateProgress(0, 'Ready to upload media', false);
						}
						
						if(this.autoStart)
						{
							try {
								if (numFilesSelected > 0) {
									this.SWFUpload.startUpload();
								}
								
							} catch (ex) {
								this.SWFUpload.debug(ex);
							}
						}
						
					}.createDelegate(this)
					
					,file_queued_handler: function(file) {
					
						this.fireEvent('fileQueued', file);
					
					}.createDelegate(this)
					
					,upload_start_handler: function(file) {
						
						// skip during errors
						if(this.errorOccurred)
						{
							return;
						}
					
						if(false!==this.fireEvent('uploadStarted',file) && this.progressBar)
						{
							this.progressBar.updateProgress(0, 'Uploading ' + file.name, false);
						}
										
					}.createDelegate(this)
					
					,upload_progress_handler: function(file, bytesLoaded, bytesTotal) {
						
						// skip during errors
						if(this.errorOccurred)
						{
							return;
						}
					
						if(false!==this.fireEvent('uploadProgress', file, bytesLoaded, bytesTotal) && this.progressBar)
						{
							this.progressBar.updateProgress(bytesLoaded / bytesTotal, 'Uploading ' + file.name, true);
						}
		
					}.createDelegate(this)
					
					,upload_success_handler: function(file, responseText, receivedResponse) {
						
						// skip during errors
						if(this.errorOccurred)
						{
							return;
						}
					
						this.fireEvent('uploadResponse', file, responseText, receivedResponse);
						
						if(this.debugMode)
						{
							console.log('Received upload response for %s: %o', file.name, responseText);
						}
						
					}.createDelegate(this)
					
					
					,upload_complete_handler: function(file) {
						
						// skip during errors
						if(this.errorOccurred)
						{
							return;
						}
					
						if(false!==this.fireEvent('uploadComplete', file) && this.progressBar)
						{
							this.progressBar.updateProgress(1, 'Finished uploading ' + file.name, true);
							this.progressBar.el.frame();
						}
							
						if (this.SWFUpload.getStats().files_queued == 0)
						{
							if(false!==this.fireEvent('allUploadsComplete') && this.progressBar)
							{
								this.progressBar.updateText('Finished uploading all files. Ready to upload media');
							}
						}
						else
						{
							// start next file
							if(this.autoStart)
							{
								this.SWFUpload.startUpload();
							}
						}
						
					}.createDelegate(this)
					
					
				}, this.swfUploadCfg);
				
			}
			
			,getStats: function() {
			
				return this.SWFUpload.getStats();
			
			}
			
			,getFile: function(index) {
			
				return this.SWFUpload.getFile(index);
			
			}
			
			,startUpload: function(index) {
			
				return this.SWFUpload.startUpload(index);
			
			}
			
		});
	});
});

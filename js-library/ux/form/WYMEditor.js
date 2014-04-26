declare('Ext::ux::form::WYMEditor', function(use) {
	use([{
		name: 'jQuery'
		,URL: '/jslib/jQuery.js'
	},{
		name: 'WYMeditor'
		,URL: '/jslib/wymeditor/jquery.wymeditor.js'
		,evalStr: 'WYMeditor.XhtmlValidator'
	}], function(use) {
	
/*
		use({
				name: 'WYMeditor::hovertools'
				,URL: '/jslib/wymeditor/plugins/hovertools/jquery.wymeditor.hovertools.js'
				,evalStr: 'WYMeditor.editor.prototype.hovertools'
			}, function(use) {
*/
	
			Ext.ux.form.WYMEditor = Ext.extend(Ext.form.Field, {
			
				wymSettings: null
			
				// Ext.form.Field config
				,allowBlank: true
				,hideMode: 'offsets'
				,fieldClass: 'wymeditor'
				,defaultAutoCreate: {
					tag: 'textarea'
					,style: 'width:1px;height:1px;'
					,autocomplete: 'off'
				}
				
				
				,constructor: function(cfg) {
				
					// patch validator
					if(!WYMeditor.XhtmlValidator._tags['iframe'])
					{
						WYMeditor.XhtmlValidator._tags['iframe'] = {
							attributes: ['width', 'height', 'frameborder', 'scrolling', 'marginheight', 'marginwidth', 'src', 'style']
						};
					}
					
					var config = {
						wymSettings: {
							
							html: ''
							,skin: 'default'
							,jQueryPath: '/jslib/jQuery.js'
							
							//classes panel
							,classesItems: [
	/*
								{'name': 'date', 'title': 'PARA: Date', 'expr': 'p'}
								,{'name': 'hidden-note', 'title': 'PARA: Hidden note','expr': 'p[@class!="important"]'}
								,{'name': 'important', 'title': 'PARA: Important','expr': 'p[@class!="hidden-note"]'}
								,{'name': 'border', 'title': 'IMG: Border', 'expr': 'img'}
								,{'name': 'special', 'title': 'LIST: Special', 'expr': 'ul, ol'}
	*/
							]
							
							//we customize the XHTML structure of WYMeditor by overwriting 
							//the value of boxHtml. In this example, "CONTAINERS" and 
							//"CLASSES" have been moved from "wym_area_right" to "wym_area_top":
							,boxHtml:   "<div class='wym_box'>"
								+ "<div class='wym_area_top'>"
								+ WYMeditor.TOOLS
								+ WYMeditor.CONTAINERS
								//+ WYMeditor.CLASSES
								+ "</div>"
								+ "<div class='wym_area_left'></div>"
								+ "<div class='wym_area_right'>"
								+ "</div>"
								+ "<div class='wym_area_main'>"
								+ WYMeditor.HTML
								+ WYMeditor.IFRAME
								+ WYMeditor.STATUS
								+ "</div>"
								+ "<div class='wym_area_bottom'>"
								+ "</div>"
								+ "</div>"
								
							
							// customize dialog html to load jScout
/*
							,dialogHtml: "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'"
								+ " 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>"
								+ "<html dir='"
								+ WYMeditor.DIRECTION
								+ "'><head>"
								+ "<link rel='stylesheet' type='text/css' media='screen'"
								+ " href='"
								+ WYMeditor.CSS_PATH
								+ "' />"
								+ "<title>"
								+ WYMeditor.DIALOG_TITLE
								+ "</title>"
								// begin additions
								+ "<script src='/js/ext3/adapter/ext/ext-base.js'></script> "
								+ "<script src='/js/ext3/ext-all.js'></script>"
								+ "<script src='/js/jScout.js'></script>"
								// end additions
								+ "<script type='text/javascript'"
								+ " src='"
								+ WYMeditor.JQUERY_PATH
								+ "'></script>"
								+ "<script type='text/javascript'"
								+ " src='"
								+ WYMeditor.WYM_PATH
								+ "'></script>"
								+ "</head>"
								+ WYMeditor.DIALOG_BODY
								+ "</html>"
*/

						}
					};
					
					Ext.apply(config, cfg);
					
					// Add events
					this.addEvents('editorCreated');
					
					Ext.ux.form.WYMEditor.superclass.constructor.call(this, config);
				}
				
				,initComponent: function() {
	
					this.wymEditor = false;
					this.wymSettings = this.wymSettings || {};
					this.backupValue = false;
					
					//Ext.ux.form.WYMEditor.superclass.initComponent.apply(this, arguments);
				}
				
				,initEvents: function() {
					this.originalValue = this.getValue();
				}
			
				,onRender: function(ct, position) {
					
					Ext.ux.form.WYMEditor.superclass.onRender.call(this, ct, position);
					
					// configure editor
					this.wymSettings.html = Ext.value(this.value, '');	
					
					// wire event handlers
					this.wymSettings.postInit = this.wymPostInit.createDelegate(this);
					
					jQuery(this.el.dom).wymeditor(this.wymSettings);
				}
				
				,initValue: function() {
					//console.log('initValue');
					if (!this.rendered)
						Ext.ux.form.WYMEditor.superclass.initValue.call(this);
					else
					{
						if (this.value !== undefined) {
							this.setValue(this.value);
						}
						else
						{
							var v = this.el.value; 
							if ( v )
								this.setValue( v );
						}
					}
				}
				
				,getValue: function() {
				
					var v;
	
					if( !this.rendered || !this.wymEditor )
						v = Ext.value( this.value, '' );
					else
						v = this.wymEditor.xhtml();
						
					// trim trailing breaks
					v = v.replace(/^\s*(<br\s*\/>\s*)+\s*/, '').replace(/\s*(<br\s*\/>\s*)+$/, '');
						
					//console.log('getValue=%o', v);
					return v;
				}
				
				,getRawValue: function() {
	
					var v;
	
					if( !this.rendered || !this.wymEditor )
						v = Ext.value( this.value, '' );
					else
						v = this.wymEditor.html();
					
					// trim trailing breaks
					v = v.replace(/^\s*(<br\s*\/>\s*)+\s*/, '').replace(/\s*(<br\s*\/>\s*)+$/, '');
						
					//console.log('getRawValue=%o', v);
					return v;
				}
				
				,setRawValue: function(v) {
					//console.log('setting raw value: %o', v);
				
					this.value = Ext.value(v, '');
					if (this.rendered && this.wymEditor)
					{
						this.wymEditor.html(this.value);
					}
					else
					{
						this.on('editorCreated', function() {
							this.wymEditor.html(this.value);
						}, this);
					}
				}
				
				,setValue: function(v) {
					//console.log('setting value: %o on %o', v, this.name);
				
					this.value = Ext.value(v, '');
					if (this.rendered && this.wymEditor)
					{
						this.wymEditor.html(this.value);
					}
					else
					{
						this.on('editorCreated', function() {
							this.wymEditor.html(this.value);
						}, this);
					}
				}
				
				,wymPostInit: function(wym) {
					
					if(this.wymEditor)
					{
						// editor already initialized
						this.wymEditor.html(this.value);
						return;
					}
					
					this.wymEditor = wym;
					
					// extend validator
					this.wymEditor.parser._Listener.block_tags.push('iframe');
	
					//we make all sections in area_top render as dropdown menus:
					jQuery(this.wymEditor._box)
						//first we have to select them:
						.find(".wym_area_top .wym_panel")
						//then we remove the existing class which make some of them render as a panels:
						.removeClass("wym_panel")
						//then we add the class which will make them render as a dropdown menu:
						.addClass("wym_dropdown")
						
					jQuery(this.wymEditor._box)
						//first we have to select them:
						.find(".wym_area_top .wym_dropdown")
						//finally we add some css to make the dropdown menus look better:
							.css("width", "160px")
							.css("float", "right")
							.css("margin-right", "5px")
						.find("ul")
							.css("width", "140px");
					
					//add a ">" character to the title of the new dropdown menus (visual cue)
					jQuery(this.wymEditor._box)
						.find(".wym_classes")
						.find(WYMeditor.H2)
							.append("<span>&nbsp;&gt;</span>");
						
					// init plugins
					//this.wymEditor.hovertools();
	
					this.wymEditor.initialized = true;
					
					this.fireEvent('editorCreated');
				}
	
			});
	
			Ext.reg('WYMEditor', Ext.ux.form.WYMEditor);

		//}); //end use
	}); //end use
}); //end declare
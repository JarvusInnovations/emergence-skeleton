declare('Ext::ux::TabAnimator', function(use) {
    use(['Ext::ux::TabAnimator.css'] , function(use) {

		Ext.ux.TabAnimator = function(config) {
			Ext.apply(this, config);
		};
		
		Ext.extend(Ext.ux.TabAnimator, Ext.util.Observable, {
			init: function(tabPanel){
			
				this.tabPanel = tabPanel;
				this.tabEl = false;
				
				
				this.tabPanel.getTemplateArgs = function(item) {
		
					var result = Ext.TabPanel.prototype.getTemplateArgs.call(this, item);
					
					result.cls = result.cls + ' x-hidden';
					
					return result;
				};
				
				
				this.tabPanel.initTab = function(item, index) {
					
					// call prototype
					Ext.TabPanel.prototype.initTab.call(this, item, index);
					
					var tabEl = Ext.get(item.tabEl);
					
					if (item.animateTab)
					{
						tabEl.setStyle('margin-top', '21px').addClass('tab-shrunk');
					}
					
					tabEl.removeClass('x-hidden');
				};
				
			
				this.tabPanel.on('render', function() {
					
					// prepare tab strip for animation
					this.tabPanel.strip.setStyle({
						'overflow-y': 'hidden'
						,height: this.tabPanel.strip.getStyle('height')
					});
					
					
					// setup zoom animation
					this.tabPanel.bwrap.setStyle('position', 'relative');
					
					// create zoomer element
					this.zoomerEl = new Ext.Element(Ext.DomHelper.append(this.tabPanel.bwrap, {
						tag: 'div'
						,cls: 'tab-zoomer'
					}));
					
					// set style and hide
					this.zoomerEl.setStyle({
						position: 'absolute'
						,'z-index': 100
					})
									
					this.zoomerEl.hide();
					
				}, this);
				
				
				this.tabPanel.on('tabchange', function() {

					this.zoomerEl.fadeOut();
					
				}, this, {delay: 20});
			
			}
			
			,addTab: function(panel, animPosition, callback) {
			
				// create a callback function for opening the tab
				var openTab = function() {
					panel.animateTab = true;
					
					var newTab = this.tabPanel.add(panel).show()
						,tabEl = Ext.get(newTab.tabEl);
					
					//console.info('animating: %o', tabEl);
					if(tabEl.hasClass('tab-shrunk'))
					{
						tabEl.animate({
							'margin-top': (this.tabPanel.tabPosition == 'bottom') ? {from: -21, to: 0} : {from: 21, to: 0}
						}, .1).removeClass('tab-shrunk');
					}

					if (callback) callback();

				}.createDelegate(this);
				
				// determine zoom animation parameters
				if(!animPosition)
				{
					// skip zoom animation
					openTab();
					return;	
				}
				else if(typeof animPosition != 'object')
				{
					animPosition = {
						x: this.zoomerEl.parent().getWidth() / 2
						,y: this.zoomerEl.parent().getHeight() / 2
					};
				}
				else
				{
					var translation = this.zoomerEl.parent().translatePoints(animPosition);
					
					animPosition = {
						x: translation.left
						,y: translation.top
					};
				}
				
				// start zoom animation and open tab on completion
				this.zoomerEl.show().animate({
					width: {
						from: 0
						,to: this.zoomerEl.parent().getWidth() - 2
					}
					,height: {
						from: 0
						,to: this.zoomerEl.parent().getHeight() - 2
					}
					,top: {
						from: animPosition.y
						,to: 0
					}
					,left: {
						from: animPosition.x
						,to: 0
					}
					,opacity: {
						from: 0
						,to: 1
					}
				}, 0.35, openTab)
			
		
			}
			
		});


	}); // end use block
	
}); // end package declaration


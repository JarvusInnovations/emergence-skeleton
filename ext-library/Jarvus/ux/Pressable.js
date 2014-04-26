/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext,CKMobile*/
Ext.define('Jarvus.ux.Pressable', {
	extend: 'Ext.mixin.Mixin'
	
	,mixinConfig: {
		id: 'pressable'
		,hooks: {
			initialize: 'initialize'
		}
	}
	
	,config: {
		/**
		 * The CSS class to add to an element during press
		 */
		pressedCls: 'pressed'
		
		/**
		 * Optional element delegate selector
		 */
		,pressedDelegate: null
		
		/**
		 * Target for the pressed class to be applied to, sometimes necessary when pressedDelegate has children
		 */
		,pressedTarget: null
		
		/**
		 * Optional ComponentQuery to target one or more child components instead of the mixed component
		 */
		,pressedSelector: null
	}

	,initialize: function() {
		var me = this
		    ,pressedDelegate = me.getPressedDelegate()
		    ,pressedTarget = me.getPressedTarget()
		    ,pressedSelector = me.getPressedSelector()
		    ,pressedCls = me.getPressedCls()
		    ,_getTarget = function(ev, cmp) {
		    	return ev.getTarget(pressedTarget||pressedDelegate, cmp.element, true);
		    }
		    ,_attachListeners = function(cmp) {
				cmp.element.on({
					delegate: pressedDelegate
					,touchstart: function(ev, t) {
						t = _getTarget(ev, cmp.element);
						
						if(t) {
							t.addCls(pressedCls);
						}
					}
					,touchend: function(ev, t) {
						t = _getTarget(ev, cmp.element);
						
						if(t) {
							t.removeCls(pressedCls);
						}
					}
					,touchmove: function(ev, t) {
						t = _getTarget(ev, cmp.element);
						
						if(t) {
							t.removeCls(pressedCls);
						}
					}
				});
			};
		
		if(pressedSelector) {
			Ext.Array.each(me.query(pressedSelector), _attachListeners);
		}
		else {
			_attachListeners(me);
		}
	}
});
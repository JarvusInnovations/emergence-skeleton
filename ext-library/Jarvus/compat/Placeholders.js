/*
    Placeholder support on input and textarea fields for browsers that don't natively support it
    Orginally written by ryon@jarv.us
    Ported to ExtJS4 by chris@jarv.us
*/

Ext.define('Jarvus.compat.Placeholders', {
    singleton: true
    ,placeholderColor: '#aaa'
    ,placeholderCls: 'placeholder'
    
    ,constructor: function() {
        this.passwordFields = {};
        
        Ext.onReady(this.onDocReady, this);
    }
    
    ,onDocReady: function() {
        
        // test for native support by creating a new input element and seeing if the prototype includes a placeholder
        var i = document.createElement('input');
        this.nativeSupport = ('placeholder' in i);
        
        if(!this.nativeSupport)
            this.initPlaceholders();
    }
    
    ,initPlaceholders: function() {
        // initialize script-based support (called if native support is not found)
        // 1. prefill any fields that have placeholders
        // 2. attach focus/blur event handlers to fill or clear fields as necessary
        // 3. attach event handler to forms to make sure to clear placeholder text before submission

        Ext.select('input, textarea')
            .each(this.fillPlaceholder, this)
            .on({
                scope: this
                ,blur: this.fillPlaceholder
                ,focus: this.clearPlaceholder
        	});
        
        // intercept form submissions to clear placeholders
		Ext.select('form').on('submit', function(ev, t) {
            Ext.fly(t).select('input, textarea').each(this.clearPlaceholder, this);
		}, this);       
    }

    // fillField is called on DOM ready and whenever an input loses focus
    // it handles checking the contents, filling in the text, and changing color to gray
    // as well, password fields will have their type changed to text so the placeholder can be read
    ,fillPlaceholder: function(el) {
        // most often called as an event handler, in which case "el" will be an event object -- grab the dom node from that
    	if (el.target) {
    		el = el.target; 
    	}
    	el = Ext.get(el);
    	ph = el.getAttribute('placeholder');
    	val = el.getValue();
    	if (Ext.isEmpty(val) && ph) {
    		el.dom.value = ph;
    		el.setStyle({ color: this.placeholderColor }).addCls(this.placeholderCls);
    		if (el.getAttribute('type') == 'password') {
    			if(!Ext.isIE)
    				el.set({ 'type': 'text' });
                    
    			this.passwordFields[Ext.id(el)] = true; // store a reference to this node so it can be switched back on focus
    		}
    	}
    }
    
    
    // called on focus and before forms are submitted
    // checks for any field that contains placeholder text, and clears/readies it for input
    // also changes any password fields back to 'password' type to mask input
    ,clearPlaceholder: function(el) {
        if (el.target) {
    		el = el.target;
    	}
    	el = Ext.get(el);
    	ph = el.getAttribute('placeholder');
    	val = el.getValue();
    	if (val == ph) {
    		el.dom.value = '';
    		el.setStyle({ color: '' }).removeCls(this.placeholderCls);
    		if (this.passwordFields[Ext.id(el)]) {
    			if(Ext.isIE)
    				el.dom.select();
    			else
    				el.set({ 'type': 'password' });
    		}
    	}
    }
});
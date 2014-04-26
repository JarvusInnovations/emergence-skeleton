declare('Ext::ux::form::XDateField', function(use) {

	Ext.ux.form.XDateField = Ext.extend(Ext.form.DateField, {
	     submitFormat:'Y-m-d'
	    ,onRender:function() {
	
	        // call parent
	        Ext.ux.form.XDateField.superclass.onRender.apply(this, arguments);
	
	        var name = this.name || this.el.dom.name;
	        this.hiddenField = this.el.insertSibling({
	             tag:'input'
	            ,type:'hidden'
	            ,name:name
	            ,value:this.formatHiddenDate(this.parseDate(this.value))
	        });
	        this.hiddenName = name; // otherwise field is not found by BasicForm::findField
	        this.el.dom.removeAttribute('name');
	        this.el.on({
	             keyup:{scope:this, fn:this.updateHidden}
	            ,blur:{scope:this, fn:this.updateHidden}
	        }, Ext.isIE ? 'after' : 'before');
	
	        this.setValue = this.setValue.createSequence(this.updateHidden);
	
	    } // eo function onRender
	
	    ,onDisable: function(){
	        // call parent
	        Ext.ux.form.XDateField.superclass.onDisable.apply(this, arguments);
	        if(this.hiddenField) {
	            this.hiddenField.dom.setAttribute('disabled','disabled');
	        }
	    } // of function onDisable
	
	    ,onEnable: function(){
	        // call parent
	        Ext.ux.form.XDateField.superclass.onEnable.apply(this, arguments);
	        if(this.hiddenField) {
	            this.hiddenField.dom.removeAttribute('disabled');
	        }
	    } // eo function onEnable
	
	    ,formatHiddenDate : function(date){
	        if(!Ext.isDate(date)) {
	            return date;
	        }
	        if('timestamp' === this.submitFormat) {
	            return date.getTime()/1000;
	        }
	        else {
	            return Ext.util.Format.date(date, this.submitFormat);
	        }
	    }
	
	    ,updateHidden:function() {
	        this.hiddenField.dom.value = this.formatHiddenDate(this.getValue());
	    } // eo function updateHidden
	
	}); // end of extend
	
	// register xtype
	Ext.reg('xdatefield', Ext.ux.form.XDateField);


}); // end of declare

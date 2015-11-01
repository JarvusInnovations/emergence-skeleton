/*
 * See http://www.extjs.com/forum/showthread.php?t=82404
 * "basicForm.updateRecord bug with radioGroup field"
 */

declare('Ext::hotfix::t82404', function(use) {



	Ext.form.RadioGroup.override({
	
		getName : function() {
			return this.groupName || this.name;
		}
	
	    ,getValue : function(){
	        var out = '';
	        this.eachItem(function(item){
	            if(item.checked){
	           // I use getGroupValue() for retrive the selected inputValue instead of the Radio component. 
	                out = item.getGroupValue();
	                return false;
	            }
	        });
	        return out;
	    }
	    
		// I override the isDirty function I belive that CheckboxGorup will have their own function
	    ,isDirty : function() {
	        if(this.disabled || !this.rendered) {
	            return false;
	        }
	        return String(this.getValue()) !== String(this.originalValue);
	    }
	    
	    //originalValue for RadioGroup will be set only through Ext.form.BasicForm.setValues so I set it in initValue.
	    ,initValue : function() {
	        this.originalValue = this.getValue();
	    }
	    
	    ,onRender : function() {
	    	
	    	Ext.form.CheckboxGroup.prototype.onRender.apply(this, arguments);
	    	
	    	if(this.buffered)
	    	{
	    		this.buffered = false;
	    		this.setValue.apply(this, this.value);
	    	}
	    
	    }
	    
	});



});
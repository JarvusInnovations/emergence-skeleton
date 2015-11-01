declare('Jarvus::search::RecordsSearchField', function(use) {
    use([] , function(use) {

		Jarvus.search.RecordsSearchField = Ext.extend(Ext.form.TriggerField, {
		
			triggerClass: 'x-form-search-trigger'
			,emptyText: 'Enter a search query here and press enter...'
			,store: false
			,queryField: 'q'
			,selectOnFocus: true
			
			,initComponent: function() {
						
				Jarvus.search.RecordsSearchField.superclass.initComponent.apply(this, arguments);
				
			}
			
			,onRender: function() {
			
				Jarvus.search.RecordsSearchField.superclass.onRender.apply(this, arguments);
			}

						
		});
				
	});
});
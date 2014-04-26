declare('Media::MediaRecord', function(use) {
	use([], function() {
	
		Media.MediaRecord = Ext.data.Record.create([{
			name: 'ID'
			,type: 'int'
		},{
			name: 'Class'
			,allowBlank: false
		},{
			name: 'ContextClass'
		},{
			name: 'ContextID'
			,type: 'int'
		},{
			name: 'MIMEType'
			,allowBlank: false
		},{
			name: 'Width'
			,type: 'int'
			,allowBlank: false
		},{
			name: 'Height'
			,type: 'int'
			,allowBlank: false
		},{
			name: 'Duration'
			,type: 'float'
		},{
			name: 'Caption'
			,convert: function(value, record) {
				return value == null ? '' : value;
			}
		},{
			name: 'Created'
			,type: 'date'
			,dateFormat: 'timestamp'
		},{
			name: 'CreatorID'
			,type: 'int'
		}]);
			
			
		Media.MediaRecord.fromData = function(data) {
		
			if(!Media.MediaRecord.reader)
				Media.MediaRecord.reader = new Ext.data.JsonReader({
					fields: Media.MediaRecord
					,idProperty: 'ID'
				});
				
			var fi = Media.MediaRecord.prototype.fields.items
				,fl = Media.MediaRecord.prototype.fields.length
				,converted = Media.MediaRecord.reader.extractValues(data, fi, fl)
				,record = new Media.MediaRecord(converted, data.ID);
				
			
			return record;
		}

		
		
	});
});
Ext.define('Jarvus.patch.TemplateParams', {
	override: 'Ext.Template'
	
	,constructor: function(html) {
		var me = this,
			args = arguments,
			buffer = [],
			i = 0,
			length = args.length,
			value;

		me.initialConfig = {};
		
		if(length == 1 && Ext.isArray(html))
		{
			args = html;
			length = args.length;
		}

		if (length > 1) {
			for (; i < length; i++) {
				value = args[i];
				if (typeof value == 'object') {
					Ext.apply(me.initialConfig, value);
					Ext.apply(me, value);
				} else {
					buffer.push(value);
				}
			}
		} else {
			buffer.push(html);
		}

		// @private
		me.html = buffer.join('');

		if (me.compiled) {
			me.compile();
		}
	}
});
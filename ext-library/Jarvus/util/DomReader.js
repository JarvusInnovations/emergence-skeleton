/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext,CKMobile*/
Ext.define('Jarvus.util.DomReader', {
	singleton: true
	
	,createContainer: function(html, tag) {
		var node = document.createElement(tag||'div');
		node.innerHTML = html;
		return node;
	}

	,extractForm: function(node) {
		var inputNodes = ( Ext.isString(node) ? this.createContainer(node) : node ).querySelectorAll('input[name]')
		    ,i = 0, inputNode
		    ,params = {};

		for(; i < inputNodes.length; i++) {
			inputNode = inputNodes[i];
			switch(inputNode.type) {
				case 'checkbox':
				case 'radio':
					if(inputNode.checked) {
						params[inputNode.name] = inputNode.value;
					}
					break;
					
				default:
					params[inputNode.name] = inputNode.value;
			}
		}
		
		return params;
	}
});
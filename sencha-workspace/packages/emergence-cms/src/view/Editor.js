/*jslint browser: true, undef: true *//*global Ext,Emergence*/
/**
 * TODO: Move UI->model workflows out of change events and into syncContentRecord method
 */
Ext.define('Emergence.cms.view.Editor', {
    extend: 'Ext.dashboard.Dashboard',
    xtype: 'emergence-cms-editor',
    requires:[
        'Ext.form.field.Text',
        'Ext.container.ButtonGroup',

        'Emergence.cms.view.EditorController',
        'Emergence.cms.view.Toolbar',
        'Emergence.cms.model.Content',

        // composers
        'Emergence.cms.view.composer.Markdown',
        'Emergence.cms.view.composer.Html',
        'Emergence.cms.view.composer.Multimedia',
        'Emergence.cms.view.composer.Embed',
        'Emergence.cms.view.composer.Unknown',
        
        // hotfixes
        'Jarvus.ext.patch.form.field.TagFieldFilterPickList', // hotfix for http://www.sencha.com/forum/showthread.php?288294-tagfield-config-filterPickList-implementation-partly-broken&p=1053510
        'Jarvus.ext.patch.button.HrefBeforeRender' // hotfix for http://www.sencha.com/forum/showthread.php?288306-button-setHref-setParams-doesn-t-check-if-rendered-before-touching-DOM&p=1053557
    ],

    controller: 'emergence-cms-editor',

    config: {
        contentRecord: null
    },

//     composers: [
//         'Emergence.cms.view.composer.Html',
//         'Emergence.cms.view.composer.Multimedia',
//         'Emergence.cms.view.composer.Embed'
//     ],

    columnWidths: [1],
    maxColumns: 1,
    cls: ['emergence-content-editor'],
    bodyStyle: {
        borderWidth: '1px 0',
        padding: 0
    },

    dockedItems: [{
        dock: 'top',

        xtype: 'toolbar',
        border: false,
        padding: '8 0 4 8',
        items: [
//            'Title:',
            {
                reference: 'titleField',
                flex: 1,
    
                xtype: 'textfield',
                cls: 'field-title',
                selectOnFocus: true,
                emptyText: 'Title'
            }
        ]
    },{
        dock: 'top',
        
        xtype: 'toolbar',
        border: false,
        padding: '4 0 0 8',
        items: [
            {
                xtype: 'tbtext',
                text: '<i class="fa fa-lg fa-tags"></i> Tags'
            },{
                reference: 'tagsField',
                flex: 1,
    
                xtype: 'tagfield',
                allowBlank: true,
                tooltip: 'Press enter after each tag',
                displayField: 'Title',
                valueField: 'ID',
                triggerAction: 'all',
                delimiter: ',',
                queryMode: 'local',
                forceSelection: false,
                createNewOnEnter: true,
                createNewOnBlur: true,
                width: 200,
                minChars: 2,
                filterPickList: true,
                typeAhead: true,
//                emptyText: 'Biology, Homepage', // Temporarily disabled due to bug EXTJS-13378: http://www.sencha.com/forum/showthread.php?285390-emptyText-breaks-the-new-Ext.form.field.Tag-component
                store: {
                    autoLoad: true,
                    fields: [
                        {name: 'ID', type: 'int', useNull: true},
                        'Title'
                    ],
                    proxy: {
                        type: 'records',
                        url: '/tags'
                    }
                }
            }
        ]
    },{
        dock: 'top',

        xtype: 'emergence-cms-toolbar',
        border: false,
        layout: {
            overflowHandler: 'menu'
        }
    },{
        reference: 'inserterCt',

        xtype: 'buttongroup',
        border: 1,
        cls: 'segmented-btn-group',
        dock: 'bottom',
        title: 'Add a content block:',
        minHeight: 100,
        bodyPadding: 0,
        defaults: {
            scale: 'large',
            iconAlign: 'top',
            flex: 1
        },
        layout: {
            type: 'hbox',
            pack: 'center',
            align: 'stretch'
        }
    }],


    getSelectedDateTime: function() {
        var publishedTimeBtn = this.lookupReference('publishedTimeBtn'),
            date = publishedTimeBtn.down('datefield').getValue() || new Date(),
            time = publishedTimeBtn.down('timefield').getValue() || new Date();
            
        date.setHours(time.getHours());
        date.setMinutes(time.getMinutes());
        date.setSeconds(0);
        
        return date;
    },
    
//    applyToolbar: function(toolbar) {
//        if (Ext.isBoolean(toolbar)) {
//            if (!toolbar) {
//                return false;
//            }
//
//            toolbar = {};
//        }
//
//        return Ext.ComponentManager.create(toolbar, 'emergence-cms-toolbar');
//    },
//
//    updateToolbar: function(newToolbar, oldToolbar) {
//        var me = this;
//
//        Ext.suspendLayouts();
//
//        if (oldToolbar) {
//            me.remove(oldToolbar);
//        }
//
//        if (newToolbar) {
//            newToolbar.dock = 'top';
//            if (Ext.isArray(me.dockedItems)) {
//                me.dockedItems.push(newToolbar);
//            } else {
//                me.addDocked(newToolbar, 1);
//            }
//        }
//
//        Ext.resumeLayouts(true);
//    },


    // @override
//     initComponent: function() {
//         var me = this,
//             composers = me.composers,
//             statusBtn,
//             visibilityBtn,
//             publishBtn,
//             publishCheckBox,
//             toolbarCt,
//             publishDateTime,
//             inserterCt,
//             //,composerCt = me.down('#composerCt')
//             i = 0, composerClassName, composerClass, inserterBtn;
//
//         me.on('drop', me.onComposerDrop, me);
//
//
//         // fire event on saveBtn click
//         me.down('#saveBtn').on('click', 'onSaveClick', me);
//
//         // fire event on viewBtn click
//         me.down('#viewBtn').on('click', 'onViewClick', me);
//
//         // fire event for status option click
//         statusBtn = me.down('#statusBtn');
//         Ext.each(statusBtn.menu.items.items, function(option)  {
//             option.on('click', 'onStatusClick', me);
//         });
//
//         // fire event for visibility option click
//         visibilityBtn = me.down('#visibilityBtn');
//         Ext.each(visibilityBtn.menu.items.items, function(option)  {
//             option.on('click', 'onVisibilityClick', me);
//         });
//
//         //fire event for publish change
//         publishBtn = me.down('#publishBtn');
//         publishCheckBox = publishBtn.menu.items.items[0];
//         publishDateTime = publishBtn.menu.items.items[1];
//         publishCheckBox.on('change', 'onPublishImmediatelyCheck', me);
//         publishDateTime.on('change', 'onPublishTimeSet', me);
//
//         //composerCt.setLoading(false);
//     },
//
//
//     openRecord: function(record, options) {
//         options = Ext.apply({
//             newWindow: false,
//             pathAppend: false,
//             hash: false
//         }, options);
//
//         var url = record.toUrl();
//
//         if (options.pathAppend) {
//             url += options.pathAppend;
//         }
//
//         if (options.hash) {
//             url += '#'+options.hash;
//         }
//
//         if (options.newWindow) {
//             window.open(url);
//         } else {
//             window.location = url;
//         }
//     },
//
//     errorResponseToText: function(obj) {
//         var errRecords,
//             msg = '<strong>' + ( obj.message ? obj.message : 'There was a problem saving your changes' ) +'</strong>',
//             i = 0, field;
//
//         if (obj.failed) {
//             errRecords = obj.failed;
//         } else if(obj.data && obj.data.validationErrors) {
//             errRecords = [obj.data];
//         }
//
//         if (errRecords && errRecords.length) {
//             msg += ':<ul>';
//             for (; i < errRecords.length; i++) {
//                 for (field in errRecords[i].validationErrors) {
//                     msg += '<li>'+errRecords[i].validationErrors[field]+'</li>';
//                 }
//             }
//             msg += '</ul>';
//         }
//
//         return msg;
//     },

    applyContentRecord: function(contentRecord) {
        if (Ext.isObject(contentRecord) && !contentRecord.isModel){
            contentRecord = Ext.create('Emergence.cms.model.Content', contentRecord);
        }
        
        return contentRecord;
    },

    updateContentRecord: function(contentRecord, oldContentRecord) {
        this.fireEvent('contentrecordchange', this, contentRecord, oldContentRecord);
    }
//
//     syncContentRecord: function() {
//         var me = this,
//             composerCt = me.down('#composerCt'),
//             contentRecord = me.contentRecord,
//             order = 1, itemData,
//             items = [];
//
//         // update title
//         contentRecord.set('Title', me.down('#titleField').getValue());
//
//         // update tags list
//         contentRecord.set('tags', Ext.Array.map(me.down('#tagsField').getValueRecords(), function(tag) {
//             return tag.get('ID') || tag.get('Title');
//         }));
//
//         // compile and set items array
//         composerCt.items.each(function(composer) {
//             itemData = composer.getItemData();
//             items.push(Ext.apply({}, {
//                 ContentID: contentRecord.get('ID'),
//                 Order: order++
//             }, itemData));
//         });
//
//         contentRecord.set('items', items);
//
//         return contentRecord;
//     },
//
//     onInserterBtnClick: function(btn) {
//         var composerCt = this.down('#composerCt'),
//             composer = composerCt.add(Ext.create(btn.composerClassName));
//
//         composer.on('dragstart', function() {
//             composerCt.items.each(function(item) {
//                 if (item !== composer) {
//                     item.fireEvent('siblingdragstart', item, composer);
//                 }
//             });
//         });
//     },
//
//     onComposerDrop: function(dropEvent) {
//         var composer = dropEvent.panel;
//
//         composer.fireEvent('dropped', composer, dropEvent);
//
//         dropEvent.column.items.each(function(item) {
//             if (item !== composer) {
//                 item.fireEvent('siblingdrop', item, composer);
//             }
//         });
//     },
//
//     onPublishImmediatelyCheck: function(item, newValue, oldValue, eOpts) {
//         var me = this,
//             publishDateTime,
//             contentRecord;
//
//         if (item.checked) {
//             me.contentRecord.set('Published', null);
//         } else {
//             me.contentRecord.set('Published', new Date());
//         }
//
//         me.updatePublished(item);
//     },
//
//     onPublishTimeSet: function(item, newValue, oldValue, eOpts) {
//         var me = this,
//             contentRecord;
//
//         me.contentRecord.set('Published', newValue);
//         me.updatePublished(item);
//     },
//
//     updatePublished: function(item){
//     },
//
//     onVisibilityClick: function(item, e) {
//         var me = this,
//             contentRecord,
//             contentVisibility;
//
//         contentVisibility = me.down('#visibilityBtn');
//         me.contentRecord.set('Visibility', item.text);
//         contentVisibility.setIconCls('icon-visibility-' + me.contentRecord.get('Visibility'));
//     },
//
//     onStatusClick: function(item, e) {
//         var me = this,
//             contentRecord,
//             contentStatus;
//
//         contentStatus = me.down('#statusBtn');
//         me.contentRecord.set('Status', item.text);
//         contentStatus.setIconCls('icon-status-' + me.contentRecord.get('Status'));
//     },
//
//     onViewClick: function() {
//         this.openRecord(this.contentRecord, {newWindow: true});
//     },
//
//     onSaveClick: function() {
//         var me = this,
//             contentRecord = me.getContentRecord(),
//             wasPhantom = contentRecord.phantom;
//
//         me.setLoading('Saving&hellip;');
//         contentRecord = me.syncContentRecord();
//
//         contentRecord.save({
//             success: function() {
//                 if (wasPhantom) {
//                     me.setLoading('Opening new post&hellip;');
//                     me.openRecord(contentRecord, {
//                         pathAppend: '/edit'
//                     });
//                 } else {
//                     me.setLoading(false);
//                 }
//             },
//             failure: function() {
//                 window.alert('Failed to save blog post, please backup your work to another application and report this to your technical support contact');
//             }
//         });
//     }
});
Ext.define('EmergenceEditor.view.tab.Search', {
    extend: 'Ext.panel.Panel',
    xtype: 'emergence-tab-search',
    requires: [
        'EmergenceEditor.view.FeedView',
        'EmergenceEditor.store.SearchResults'
    ],
    mixins: [
        'EmergenceEditor.mixin.Tabbable'
    ],

    statics: {
        buildToken: function(config) {
            return Ext.Object.toQueryString(config);
        },
        parseToken: function(token) {
            var query = Ext.Object.fromQueryString(token);

            return {
                content: query.content,
                contentFormat: query.contentFormat,
                case: query.case,
                filename: query.filename,
                include: query.include,
                path: query.path
            };
        }
    },


    config: {
        content: null,
        contentFormat: null,
        case: null,
        filename: null,
        include: null,
        path: null,

        componentCls: 'emergence-tab-search',
        layout: 'fit',
        items: [{
            xtype: 'emergence-feedview',
            store: {
                type: 'emergence-searchresults'
            },
            emptyText: 'Nothing found',
            tpl: [
                '<section class="feed-ct">',
                '    <tpl for=".">',
                '        <article class="feed-item <tpl if="ContentMatch">match-content<tpl else>match-nocontent</tpl>">',
                '            <tpl if="!values.ContentMatch">',
                '               <span class="path">{FullDirname}</span>',
                '            </tpl>',
                '            <span class="filename">',
                '                <a href="#/{FullPath}<tpl for="ContentMatch">${line}</tpl>" title="{FullPath}">{Handle}</a>',
                '            </span>',
                '            <tpl for="ContentMatch">',
                '                <span class="line-number">{line}</span>',
                '                <code>{prefix:htmlEncode}<mark>{match:htmlEncode}</mark>{suffix:htmlEncode}</code>',
                '            </tpl>',
                '        </article>',
                '    </tpl>',
                '</section>'
            ]
        }],
    },


    // lifecycle methods
    initComponent: function() {
        this.callParent(arguments);
        this.dataView = this.down('dataview');
    },
    getState: function() {
        return this.mixins.tabbable.getTabbableState.call(this);
    },

    buildFullToken: function() {
        return 'search?' + this.getToken();
    },


    // config handlers
    updateContent: function () {
        this.setLoadNeeded(true);
    },

    updateContentFormat: function () {
        this.setLoadNeeded(true);
    },

    updateCase: function () {
        this.setLoadNeeded(true);
    },

    updateFilename: function () {
        this.setLoadNeeded(true);
    },

    updateInclude: function () {
        this.setLoadNeeded(true);
    },

    updatePath: function () {
        this.setLoadNeeded(true);
    },


    // public methods
    load: function(options) {
        return this.dataView.getStore().load(options);
    }
});
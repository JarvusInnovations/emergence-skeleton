/*
 * This file launches the application by asking Ext JS to create
 * and launch() the Application class.
 */
Ext.application({
    extend: 'EmergencePullTool.Application',

    name: 'EmergencePullTool',

    requires: [
        // This will automatically load all classes in the EmergencePullTool namespace
        // so that application classes do not need to require each other.
        'EmergencePullTool.*'
    ],

    // The name of the initial view to create.
    mainView: 'EmergencePullTool.view.main.Main'
});

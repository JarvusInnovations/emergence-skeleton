/*
 * This file launches the application by asking Ext JS to create
 * and launch() the Application class.
 */
Ext.application({
    extend: 'EmergenceEditor.Application',

    name: 'EmergenceEditor',

    requires: [
        // This will automatically load all classes in the EmergenceEditor namespace
        // so that application classes do not need to require each other.
        'EmergenceEditor.*'
    ],

    // The name of the initial view to create.
    mainView: 'EmergenceEditor.view.main.Main'
});

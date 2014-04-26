/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A minimal layout that defers sizing and styling of child components to CSS rules.
 * 
 * This layout is similar to {@link Ext.layout.container.Auto}, but does not inject a clearing div
 * after the child elements. It is useful for rendering components into elements like tables and lists
 * where injecting a random div tag would lead to invalid markup.
 */
Ext.define('Jarvus.layout.container.Raw', {
    extend: 'Ext.layout.container.Auto'
    ,alias: 'layout.raw'
    
    ,type: 'raw'
    ,renderTpl: '{%this.renderBody(out,values)%}' // like auto, but no clearEl
});
virtualpage.panel.Settings = function(config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        cls: 'virtualpage-formpanel',
        layout: 'anchor',
        hideMode: 'offsets',
        items: [{
            xtype: 'modx-tabs',
            defaults: {
                border: false,
                autoHeight: true,
                autoWidth: true,
                deferredRender: false,
                forceLayout: true
            },
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('virtualpage_routes'),
                layout: 'anchor',
                items: [{
                    html: _('virtualpage_routes_intro'),
                    cls: 'panel-desc'
                }, {
                    xtype: 'virtualpage-grid-route',
                    cls: 'virtualpage-grid main-wrapper'
                }]
            }, {
                title: _('virtualpage_handlers'),
                layout: 'anchor',
                items: [{
                    html: _('virtualpage_handlers_intro'),
                    cls: 'panel-desc'
                }, {
                    xtype: 'virtualpage-grid-handler',
                    cls: 'virtualpage-grid main-wrapper'
                }]
            }, {
                title: _('virtualpage_events'),
                layout: 'anchor',
                items: [{
                    html: _('virtualpage_events_intro'),
                    cls: 'panel-desc'
                }, {
                    xtype: 'virtualpage-grid-event',
                    cls: 'virtualpage-grid main-wrapper'
                }]
            }]
        }]
    });
    virtualpage.panel.Settings.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.panel.Settings, MODx.Panel);
Ext.reg('virtualpage-panel-settings', virtualpage.panel.Settings);

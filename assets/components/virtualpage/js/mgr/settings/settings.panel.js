virtualpage.page.Settings = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'virtualpage-panel-settings'
            , renderTo: 'virtualpage-panel-settings-div'
        }]
    });
    virtualpage.page.Settings.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.page.Settings, MODx.Component);
Ext.reg('virtualpage-page-settings', virtualpage.page.Settings);

virtualpage.panel.Settings = function(config) {
    config = config || {};
    Ext.apply(config, {
        border: false
        , deferredRender: true
        , baseCls: 'modx-formpanel'
        , items: [{
            html: '<h2>' + _('virtualpage') + ' :: ' + _('vp_settings') + '</h2>'
            , border: false
            , cls: 'modx-page-header container'
        }, {
            xtype: 'modx-tabs'
            , id: 'virtualpage-settings-tabs'
            , bodyStyle: 'padding: 10px'
            , defaults: {border: false, autoHeight: true}
            , border: true
            , hideMode: 'offsets'

            , items: [{
                title: _('vp_route')
                , items: [{
                    html: '<p>' + _('vp_route_intro') + '</p>'
                    , border: false
                    , bodyCssClass: 'panel-desc'
                    , bodyStyle: 'margin-bottom: 10px'
                }, {
                    xtype: 'virtualpage-grid-route'
                }]
            },{
                title: _('vp_event')
                , items: [{
                    html: '<p>' + _('vp_event_intro') + '</p>'
                    , border: false
                    , bodyCssClass: 'panel-desc'
                    , bodyStyle: 'margin-bottom: 10px'
                }, {
                    xtype: 'virtualpage-grid-event'
                }]
            }


            ]

        }]
    });
    virtualpage.panel.Settings.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.panel.Settings, MODx.Panel);
Ext.reg('virtualpage-panel-settings', virtualpage.panel.Settings);
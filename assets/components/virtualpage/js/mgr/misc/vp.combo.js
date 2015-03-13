Ext.namespace('virtualpage.combo');

virtualpage.combo.PluginEvent = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'event'
        ,primaryKey: 'name'
        , hiddenName: 'name'
        , displayField: 'name'
        , valueField: 'name'
        , editable: true
        , fields: ['name','service','groupname','enabled','priority','propertyset','menu']
        , pageSize: 10
        , emptyText: _('vp_combo_select')
        , hideMode: 'offsets'
        , url: virtualpage.config.connector_url
        , baseParams: {
            action: 'mgr/misc/pluginevent/getlist',
            limit: 0
        }
    });
    virtualpage.combo.PluginEvent.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.combo.PluginEvent, MODx.combo.ComboBox);
Ext.reg('virtualpage-combo-plugin-event', virtualpage.combo.PluginEvent);


virtualpage.combo.Event = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'event'
        , hiddenName: 'event'
        , displayField: 'name'
        , valueField: 'id'
        , editable: true
        , fields: ['name','id']
        , pageSize: 10
        , emptyText: _('vp_combo_select')
        , hideMode: 'offsets'
        , url: virtualpage.config.connector_url
        , baseParams: {
            action: 'mgr/settings/event/getlist',
            combo: true,
            limit: 0
        }
    });
    virtualpage.combo.Event.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.combo.Event, MODx.combo.ComboBox);
Ext.reg('virtualpage-combo-event', virtualpage.combo.Event);
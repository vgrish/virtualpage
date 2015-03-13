Ext.namespace('virtualpage.combo');

virtualpage.combo.event = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'event'
        ,primaryKey: 'name'
        , hiddenName: 'name'
        , displayField: 'name'
        , valueField: 'name'
        , editable: true
        , fields: ['name','service','groupname','enabled','priority','propertyset','menu']
        , pageSize: 20
        , emptyText: _('vp_combo_select')
        , hideMode: 'offsets'
        , url: virtualpage.config.connector_url
        , baseParams: {
            action: 'mgr/misc/event/getlist',
            plugin: 1,
            limit: 0,
        }
    });
    virtualpage.combo.event.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.combo.event, MODx.combo.ComboBox);
Ext.reg('virtualpage-combo-event', virtualpage.combo.event);


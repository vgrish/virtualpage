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
        //, editable: true
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


virtualpage.combo.Handler = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'handler'
        , hiddenName: 'handler'
        , displayField: 'name'
        , valueField: 'id'
        //, editable: true
        , fields: ['name','id']
        , pageSize: 10
        , emptyText: _('vp_combo_select')
        , hideMode: 'offsets'
        , url: virtualpage.config.connector_url
        , baseParams: {
            action: 'mgr/settings/handler/getlist',
            combo: true,
            limit: 0
        }
    });
    virtualpage.combo.Handler.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.combo.Handler, MODx.combo.ComboBox);
Ext.reg('virtualpage-combo-handler', virtualpage.combo.Handler);


virtualpage.combo.Metod = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['metod','display']
            ,data: [
				['GET,POST',_('vp_metod_get_post')],
                ['GET',_('vp_metod_get')],
                ['POST',_('vp_metod_post')]
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'metod'
        ,hiddenName: 'metod'

    });
    virtualpage.combo.Metod.superclass.constructor.call(this,config);
};
Ext.extend(virtualpage.combo.Metod,MODx.combo.ComboBox);
Ext.reg('virtualpage-combo-metod',virtualpage.combo.Metod);

virtualpage.combo.Type = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['type','display']
            ,data: [
                [0,_('vp_type_resource')],
                [1,_('vp_type_snippet')],
				[2,_('vp_type_chunk')]
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'type'
        ,hiddenName: 'type'

    });
    virtualpage.combo.Type.superclass.constructor.call(this,config);
};
Ext.extend(virtualpage.combo.Type,MODx.combo.ComboBox);
Ext.reg('virtualpage-combo-type',virtualpage.combo.Type);


virtualpage.combo.Entry = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        name: 'entry'
        ,hiddenName: 'entry'
        ,displayField: 'name'
        ,valueField: 'id'
        ,editable: true
        ,fields: ['id','name']
        ,pageSize: 10
        ,emptyText: _('vp_combo_select')
        ,hideMode: 'offsets'
        ,url: virtualpage.config.connector_url
        ,baseParams: {
            action: 'mgr/misc/entry/getlist'
            ,element: 'resource'
            ,combo: true
            ,limit: 0
        }
    });
    virtualpage.combo.Entry.superclass.constructor.call(this,config);
};
Ext.extend(virtualpage.combo.Entry,MODx.combo.ComboBox);
Ext.reg('virtualpage-combo-entry',virtualpage.combo.Entry);

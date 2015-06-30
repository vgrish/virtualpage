virtualpage.window.UpdateEvent = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('vp_menu_update'),
        url: virtualpage.config.connector_url,
        action: 'mgr/settings/event/update',
        fields: this.getFields(config),
        keys: this.getKeys(config),
        width: 600,
        //height: 450,
        layout: 'anchor',
        autoHeight: true,
        cls: 'virtualpage-window ' + (MODx.modx23 ? 'modx23' : 'modx22')
    });
    virtualpage.window.UpdateEvent.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.window.UpdateEvent, MODx.Window, {

    getKeys: function() {
        return [{
            key: Ext.EventObject.ENTER,
            shift: true,
            fn: this.submit,
            scope: this
        }];
    },

    getFields: function(config) {
        return [{
            xtype: 'hidden',
            name: 'id'
        }, {
            xtype: 'virtualpage-combo-plugin-event',
            fieldLabel: _('vp_name'),
            name: 'name',
            anchor: '99.5%',
            allowBlank: false
        },{
            xtype: 'textarea',
            fieldLabel: _('vp_description'),
            name: 'description',
            anchor: '99.5%',
            height: 50
        },{
            xtype: 'checkboxgroup',
            columns: 4,
            items: [{
                xtype: 'xcheckbox',
                fieldLabel: '',
                boxLabel: _('vp_active'),
                name: 'active',
                checked: config.record.active
            }]
        }];
    }

});
Ext.reg('virtualpage-window-event-update', virtualpage.window.UpdateEvent);

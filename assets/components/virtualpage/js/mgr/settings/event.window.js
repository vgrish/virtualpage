virtualpage.window.UpdateEvent = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('create'),
        url: virtualpage.config.connector_url,
        action: 'mgr/settings/event/update',
        fields: this.getFields(config),
        keys: this.getKeys(config),
        width: 600,
        autoHeight: true,
        cls: 'virtualpage-panel-event'
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
            layout: 'form',
            defaults: {border: false, anchor: '100%'},
            items: [{
                xtype: 'hidden',
                name: 'id'
            }, {
                xtype: 'virtualpage-combo-plugin-event',
                fieldLabel: _('virtualpage_name'),
                name: 'name',
                allowBlank: false
            }, {
                xtype: 'textarea',
                fieldLabel: _('virtualpage_description'),
                name: 'description',
                height: 50
            }, {
                xtype: 'checkboxgroup',
                columns: 4,
                items: [{
                    xtype: 'xcheckbox',
                    fieldLabel: '',
                    boxLabel: _('virtualpage_active'),
                    name: 'active',
                    checked: config.record.active
                }]
            }]
        }];
    }

});
Ext.reg('virtualpage-window-event-update', virtualpage.window.UpdateEvent);

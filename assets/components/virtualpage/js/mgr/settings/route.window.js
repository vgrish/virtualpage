virtualpage.window.UpdateRoute = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('vp_menu_update'),
        url: virtualpage.config.connector_url,
        action: 'mgr/settings/route/update',
        fields: this.getFields(config),
        keys: this.getKeys(config),
        width: 600,
        //height: 450,
        layout: 'anchor',
        autoHeight: true,
        cls: 'virtualpage-window ' + (MODx.modx23 ? 'modx23' : 'modx22')
    });
    virtualpage.window.UpdateRoute.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.window.UpdateRoute, MODx.Window, {

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
            xtype: 'textfield',
            fieldLabel: _('vp_route'),
            name: 'route',
            anchor: '99.5%',
            allowBlank: false
        }, {
            items: [{
                layout: 'form',
                cls: 'modx-panel',
                items: [{
                    layout: 'column',
                    border: false,
                    items: [{
                        columnWidth: .49,
                        border: false,
                        layout: 'form',
                        items: this.getLeftFields(config)
                    }, {
                        columnWidth: .51,
                        border: false,
                        layout: 'form',
                        cls: 'right-column',
                        items: this.getRightFields(config)
                    }]
                }]
            }]
        }, {
            xtype: 'textarea',
            fieldLabel: _('vp_placeholders'),
            name: 'properties',
            anchor: '99.5%'
        }, {
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
    },

    getLeftFields: function(config) {
        return [{
            xtype: 'virtualpage-combo-metod',
            fieldLabel: _('vp_metod'),
            name: 'metod',
            anchor: '99%',
            allowBlank: false
        }, {
            xtype: 'virtualpage-combo-event',
            fieldLabel: _('vp_event'),
            name: 'event',
            anchor: '99%',
            allowBlank: false
        }];
    },

    getRightFields: function(config) {
        return [ {
            xtype: 'virtualpage-combo-handler',
            fieldLabel: _('vp_handler'),
            name: 'handler',
            anchor: '99%',
            allowBlank: false
        }];
    }

});
Ext.reg('virtualpage-window-route-update', virtualpage.window.UpdateRoute);

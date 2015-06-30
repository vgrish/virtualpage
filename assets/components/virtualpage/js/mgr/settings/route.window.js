virtualpage.window.UpdateRoute = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('virtualpage_action_edit'),
        url: virtualpage.config.connector_url,
        action: 'mgr/sets/update',
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
            anchor: '99%',
            allowBlank: false
        }, {
            items: [{
                layout: 'form',
                cls: 'modx-panel',
                items: [{
                    layout: 'column',
                    border: false,
                    items: [{
                        columnWidth: .5,
                        border: false,
                        layout: 'form',
                        items: this.getLeftFields(config)
                    }, {
                        columnWidth: .5,
                        border: false,
                        layout: 'form',
                        cls: 'right-column',
                        items: this.getRightFields(config)
                    }]
                }]
            }]
        }, {
            xtype: 'textarea',
            fieldLabel: _('vp_description'),
            name: 'description',
            anchor: '99%'
        }];
    },

    getLeftFields: function(config) {
        return [{
            xtype: 'virtualpage-combo-file-type',
            fieldLabel: _('type'),
            name: 'name',
            anchor: '99%'
        }, {
            xtype: 'virtualpage-combo-file-format',
            fieldLabel: _('virtualpage_format'),
            name: 'parent',
            anchor: '99%'
        }, {
            xtype: 'virtualpage-combo-file-orientation',
            fieldLabel: _('virtualpage_orientation'),
            name: 'orientation',
            anchor: '99%'
        }];
    },

    getRightFields: function(config) {
        return [{
            xtype: 'virtualpage-combo-chunk',
            fieldLabel: _('virtualpage_row'),
            name: 'row',
            anchor: '99%',
            hiddenName: 'row'
        }, {
            xtype: 'virtualpage-combo-chunk',
            fieldLabel: _('virtualpage_wrapper'),
            name: 'wrapper',
            anchor: '99%',
            hiddenName: 'wrapper'
        }, {
            xtype: 'checkboxgroup',
            columns: 2,
            items: [{
                xtype: 'xcheckbox',
                fieldLabel: '',
                boxLabel: _('virtualpage_updatable'),
                name: 'updatable',
                checked: config.record.updatable
            }, {
                xtype: 'xcheckbox',
                fieldLabel: '',
                boxLabel: _('virtualpage_undeletable'),
                name: 'undeletable',
                checked: config.record.undeletable
            }]
        }];
    }

});
Ext.reg('virtualpage-window-route-update', virtualpage.window.UpdateRoute);

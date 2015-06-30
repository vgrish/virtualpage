virtualpage.window.UpdateHandler = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('vp_menu_update'),
        url: virtualpage.config.connector_url,
        action: 'mgr/settings/handler/update',
        fields: this.getFields(config),
        keys: this.getKeys(config),
        width: 600,
        //height: 450,
        layout: 'anchor',
        autoHeight: true,
        cls: 'virtualpage-window ' + (MODx.modx23 ? 'modx23' : 'modx22')
    });
    virtualpage.window.UpdateHandler.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.window.UpdateHandler, MODx.Window, {

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
            fieldLabel: _('vp_name'),
            name: 'name',
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
            fieldLabel: _('vp_content'),
            name: 'content',
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
            },{
                xtype: 'xcheckbox',
                fieldLabel: '',
                boxLabel: _('vp_cache'),
                name: 'cache',
                checked: config.record.cache
            }]
        }];
    },

    getLeftFields: function(config) {
        return [{
            xtype: 'virtualpage-combo-type',
            fieldLabel: _('vp_type'),
            name: 'type',
            anchor: '99%',
            allowBlank: false,
            listeners: {
                afterrender: {
                    fn: function(r) {
                        this.handleChangeType(0);
                    },
                    scope: this
                },
                select: {
                    fn: function(r) {
                        this.handleChangeType(1);
                    },
                    scope: this
                }
            }
        }];
    },

    getRightFields: function(config) {
        return [ {
            xtype: 'virtualpage-combo-entry',
            fieldLabel: _('vp_entry'),
            name: 'entry',
            anchor: '99%',
            allowBlank: false
        }];
    },

    handleChangeType: function(change) {
        var f = this.fp.getForm();
        var _type = f.findField('type');
        var _entry = f.findField('entry');
        var _content= f.findField('content');

        var type = _type.getValue();
        var entry = _entry.getValue();

        switch (type) {
            case 0:
            case '0':
            {
                _entry.baseParams.element = 'resource';
                _content.disable().hide();
                break;
            }
            case 1:
            case '1':
            {
                _entry.baseParams.element = 'snippet';
                _content.disable().hide();
                break;
            }
            case 2:
            case '2':
            {
                _entry.baseParams.element = 'chunk';
                _content.disable().hide();
                break;
            }
            case 3:
            case '3':
            {
                _entry.baseParams.element = 'template';
                _content.enable().show();
                break;
            }
        }
        if(!!_entry.pageTb) {
            _entry.pageTb.show();
        }
        if ((1 == change)) {
            _entry.setValue();
        }
        _entry.store.load();
    }

});
Ext.reg('virtualpage-window-handler-update', virtualpage.window.UpdateHandler);

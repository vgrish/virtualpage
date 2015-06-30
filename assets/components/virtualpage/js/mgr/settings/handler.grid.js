virtualpage.grid.Handler = function(config) {
    config = config || {};

    this.exp = new Ext.grid.RowExpander({
        expandOnDblClick: false,
        tpl: new Ext.Template('<p class="desc">{description}</p>'),
        renderer: function(v, p, record) {
            return record.data.description != '' && record.data.description != null ? '<div class="x-grid3-row-expander">&#160;</div>' : '&#160;';
        }
    });
    this.dd = function(grid) {
        this.dropTarget = new Ext.dd.DropTarget(grid.container, {
            ddGroup: 'dd',
            copy: false,
            notifyDrop: function(dd, e, data) {
                var store = grid.store.data.items;
                var target = store[dd.getDragData(e).rowIndex].id;
                var source = store[data.rowIndex].id;
                if (target != source) {
                    dd.el.mask(_('loading'), 'x-mask-loading');
                    MODx.Ajax.request({
                        url: virtualpage.config.connector_url,
                        params: {
                            action: config.action || 'mgr/settings/handler/sort',
                            source: source,
                            target: target
                        },
                        listeners: {
                            success: {
                                fn: function(r) {
                                    dd.el.unmask();
                                    grid.refresh();
                                },
                                scope: grid
                            },
                            failure: {
                                fn: function(r) {
                                    dd.el.unmask();
                                },
                                scope: grid
                            }
                        }
                    });
                }
            }
        });
    };
    Ext.applyIf(config, {
        id: 'virtualpage-grid-handler',
        url: virtualpage.config.connector_url,
        baseParams: {
            action: 'mgr/settings/handler/getlist'
        },
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        listeners: this.getListeners(config),
        paging: true,
        remoteSort: true,
        autoHeight: true,
        save_action: 'mgr/settings/handler/updatefromgrid',
        autosave: true,
        save_callback: this.updateRow,
        plugins: this.exp,
        ddGroup: 'dd',
        enableDragDrop: true
    });
    virtualpage.grid.Handler.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.grid.Handler, MODx.grid.Grid, {
    windows: {},

    getFields: function(config) {
        var fields = ['id', 'name', 'type', 'entry', 'content', 'description', 'cache', 'active', 'name_type'];

        return fields;
    },

    getColumns: function(config) {
        var columns = [this.exp];
        var add = {
            id: {
                width: 25,
                sortable: true
            },
            name: {
                width: 50,
                sortable: true,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            type: {
                width: 50,
                sortable: true,
                editor: {
                    xtype: 'virtualpage-combo-type',
                    allowBlank: false
                },
                renderer: virtualpage.utils.renderType
            },
            entry: {
                width: 50,
                sortable: true
            },
            cache: {
                width: 35,
                sortable: false,
                editor: {
                    xtype: 'combo-boolean',
                    renderer: 'boolean'
                }
            },
            active: {
                width: 35,
                sortable: false,
                editor: {
                    xtype: 'combo-boolean',
                    renderer: 'boolean'
                }
            }
        };

        for (var field in add) {
            if (add[field]) {
                Ext.applyIf(add[field], {
                    header: _('vp_' + field),
                    tooltip: _('vp_tooltip_' + field),
                    dataIndex: field
                });
                columns.push(add[field]);
            }
        }

        return columns;
    },

    getTopBar: function(config) {
        var tbar = [];
        tbar.push({
            text: _('vp_btn_create'),
            handler: this.createHandler,
            scope: this
        });

        return tbar;
    },

    getMenu: function() {
        var m = [];
        m.push({
            text: _('vp_menu_update'),
            handler: this.updateHandler
        });
        m.push('-');
        m.push({
            text: _('vp_menu_remove'),
            handler: this.removeHandler
        });
        this.addContextMenuItem(m);
    },

    getListeners: function(config) {
        var listeners = {};
        listeners.render = {fn: this.dd, scope: this};

        return listeners;
    },

    updateRow: function(response) {
        this.refresh();
    },

    createHandler: function(btn, e) {
        var record = {
            type: 0,
            active: 1
        };
        var w = Ext.getCmp('virtualpage-window-handler-create');
        if (w) {
            w.hide().getEl().remove();
        }
        w = MODx.load({
            xtype: 'virtualpage-window-handler-update',
            title: _('vp_menu_create'),
            action: 'mgr/settings/handler/create',
            record: record,
            id: 'virtualpage-window-handler-create',
            listeners: {
                success: {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
        w.fp.getForm().reset();
        w.fp.getForm().setValues(record);
        w.show(e.target);
    },

    updateHandler: function(btn, e, row) {
        var record = typeof(row) != 'undefined' ? row.data : this.menu.record;

        MODx.Ajax.request({
            url: virtualpage.config.connector_url,
            params: {
                action: 'mgr/settings/handler/get',
                id: record.id
            },
            listeners: {
                success: {
                    fn: function(r) {
                        var record = r.object;
                        var w = MODx.load({
                            xtype: 'virtualpage-window-handler-update',
                            record: record,
                            listeners: {
                                success: {
                                    fn: this.refresh,
                                    scope: this
                                }
                            }
                        });
                        w.fp.getForm().reset();
                        w.fp.getForm().setValues(record);
                        w.show(e.target);
                    },
                    scope: this
                }
            }
        });
    },

    removeHandler: function(btn, e) {
        if (!this.menu.record) return false;

        MODx.msg.confirm({
            title: _('vp_menu_remove') + ' "' + this.menu.record.name + '"',
            text: _('vp_menu_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/settings/handler/remove',
                id: this.menu.record.id
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.refresh();
                    },
                    scope: this
                }
            }
        });
    }

});
Ext.reg('virtualpage-grid-handler', virtualpage.grid.Handler);

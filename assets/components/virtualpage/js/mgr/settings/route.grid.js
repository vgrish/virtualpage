virtualpage.grid.Route = function(config) {
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
                            action: config.action || 'mgr/settings/route/sort',
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
        id: 'virtualpage-grid-route',
        url: virtualpage.config.connector_url,
        baseParams: {
            action: 'mgr/settings/route/getlist'
        },
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        listeners: this.getListeners(config),
        paging: true,
        remoteSort: true,
        autoHeight: true,
        save_action: 'mgr/settings/route/updatefromgrid',
        autosave: true,
        save_callback: this.updateRow,
        plugins: this.exp,
        ddGroup: 'dd',
        enableDragDrop: true

    });
    virtualpage.grid.Route.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.grid.Route, MODx.grid.Grid, {
    windows: {},

    getFields: function(config) {
        var fields = ['id', 'metod', 'route', 'handler', 'description', 'properties', 'active', 'event', 'handler_name'];

        return fields;
    },

    getColumns: function(config) {
        var columns = [this.exp];
        var add = {
            id: {
                width: 25,
                sortable: true
            },
            metod: {
                width: 50,
                sortable: true,
                editor: {
                    xtype: 'virtualpage-combo-metod',
                    allowBlank: false
                }
            },
            route: {
                width: 50,
                sortable: true,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            handler: {
                width: 35,
                sortable: true,
                editor: {
                    xtype: 'virtualpage-combo-handler',
                    allowBlank: false
                },
                renderer: virtualpage.utils.renderHandler
            },
            event: {
                width: 35,
                sortable: true,
                editor: {
                    xtype: 'virtualpage-combo-event',
                    allowBlank: false
                },
                renderer: virtualpage.utils.renderEvent
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
            handler: this.createRoute,
            scope: this
        });

        return tbar;
    },

    getMenu: function() {
        var m = [];
        m.push({
            text: _('vp_menu_update'),
            handler: this.updateRoute
        });
        m.push('-');
        m.push({
            text: _('vp_menu_remove'),
            handler: this.removeRoute
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

    createRoute: function(btn, e) {
        if (!this.windows.createRoute) {
            this.windows.createRoute = MODx.load({
                xtype: 'virtualpage-window-route-create',
                fields: this.getRouteFields('create'),
                listeners: {
                    success: {
                        fn: function() {
                            this.refresh();
                        },
                        scope: this
                    }
                }
            });
        }
        this.windows.createRoute.fp.getForm().reset();
        this.windows.createRoute.fp.getForm().setValues({
            active: 1,
            metod: 'GET,POST'
        });
        this.windows.createRoute.show(e.target);
    },

    updateRoute: function(btn, e) {
        if (!this.menu.record || !this.menu.record.id) return false;
        var r = this.menu.record;

        if (!this.windows.updateRoute) {
            this.windows.updateRoute = MODx.load({
                xtype: 'virtualpage-window-route-update',
                record: r,
                fields: this.getRouteFields('update'),
                listeners: {
                    success: {
                        fn: function() {
                            this.refresh();
                        },
                        scope: this
                    }
                }
            });
        }
        this.windows.updateRoute.fp.getForm().reset();
        this.windows.updateRoute.fp.getForm().setValues(r);
        this.windows.updateRoute.show(e.target);
    },

    removeRoute: function(btn, e) {
        if (!this.menu.record) return false;

        MODx.msg.confirm({
            title: _('vp_menu_remove') + ' "' + this.menu.record.name + '"',
            text: _('vp_menu_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/settings/route/remove',
                id: this.menu.record.id,
                event: this.menu.record.event
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
    },

    /*getRouteFields: function(type) {
        var fields = [];

        fields.push({
            xtype: 'hidden',
            name: 'id',
            id: 'virtualpage-route-id-' + type
        }, {
            xtype: 'virtualpage-combo-metod',
            fieldLabel: _('vp_metod'),
            name: 'metod',
            allowBlank: false,
            anchor: '99%',
            id: 'virtualpage-route-metod-' + type
        }, {
            xtype: 'textfield',
            fieldLabel: _('vp_route'),
            name: 'route',
            allowBlank: false,
            anchor: '99%',
            id: 'virtualpage-route-route-' + type
        }, {
            xtype: 'virtualpage-combo-event',
            fieldLabel: _('vp_event'),
            name: 'event',
            allowBlank: false,
            anchor: '99%',
            id: 'virtualpage-route-event-' + type
        }, {
            xtype: 'virtualpage-combo-handler',
            fieldLabel: _('vp_handler'),
            name: 'handler',
            allowBlank: false,
            anchor: '99%',
            id: 'virtualpage-route-handler-' + type
        }, {
            xtype: 'textarea',
            fieldLabel: _('vp_placeholders'),
            name: 'properties',
            anchor: '99%',
            id: 'virtualpage-route-properties-' + type
        }, {
            xtype: 'textarea',
            fieldLabel: _('vp_description'),
            name: 'description',
            anchor: '99%',
            id: 'virtualpage-route-description-' + type
        });

        fields.push({
            xtype: 'xcheckbox',
            fieldLabel: '',
            boxLabel: _('vp_active'),
            name: 'active',
            id: 'virtualpage-route-active-' + type
        });

        return fields;
    }*/

});
Ext.reg('virtualpage-grid-route', virtualpage.grid.Route);

/*

virtualpage.window.CreateRoute = function(config) {
    config = config || {};
    this.ident = config.ident || 'mecitem' + Ext.id();
    Ext.applyIf(config, {
        title: _('vp_menu_create'),
        id: this.ident,
        width: 600,
        autoHeight: true,
        labelAlign: 'left',
        labelWidth: 180,
        url: virtualpage.config.connector_url,
        action: 'mgr/settings/route/create',
        fields: config.fields,
        keys: [{
            key: Ext.EventObject.ENTER,
            shift: true,
            fn: function() {
                this.submit()
            },
            scope: this
        }]
    });
    virtualpage.window.CreateRoute.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.window.CreateRoute, MODx.Window);
Ext.reg('virtualpage-window-route-create', virtualpage.window.CreateRoute);


virtualpage.window.UpdateRoute = function(config) {
    config = config || {};
    this.ident = config.ident || 'meuitem' + Ext.id();
    Ext.applyIf(config, {
        title: _('vp_menu_update'),
        id: this.ident,
        width: 600,
        autoHeight: true,
        labelAlign: 'left',
        labelWidth: 180,
        url: virtualpage.config.connector_url,
        action: 'mgr/settings/route/update',
        fields: config.fields,
        keys: [{
            key: Ext.EventObject.ENTER,
            shift: true,
            fn: function() {
                this.submit()
            },
            scope: this
        }]
    });
    virtualpage.window.UpdateRoute.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.window.UpdateRoute, MODx.Window);
Ext.reg('virtualpage-window-route-update', virtualpage.window.UpdateRoute);
*/

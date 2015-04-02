virtualpage.grid.Handler = function(config) {
    config = config || {};

    this.exp = new Ext.grid.RowExpander({
        expandOnDblClick: false
        ,tpl : new Ext.Template('<p class="desc">{description}</p>')
        ,renderer : function(v, p, record){return record.data.description != '' && record.data.description != null ? '<div class="x-grid3-row-expander">&#160;</div>' : '&#160;';}
    });
    this.dd = function(grid) {
        this.dropTarget = new Ext.dd.DropTarget(grid.container, {
            ddGroup : 'dd',
            copy:false,
            notifyDrop : function(dd, e, data) {
                var store = grid.store.data.items;
                var target = store[dd.getDragData(e).rowIndex].id;
                var source = store[data.rowIndex].id;
                if (target != source) {
                    dd.el.mask(_('loading'),'x-mask-loading');
                    MODx.Ajax.request({
                        url: virtualpage.config.connector_url
                        ,params: {
                            action: config.action || 'mgr/settings/handler/sort'
                            ,source: source
                            ,target: target
                        }
                        ,listeners: {
                            success: {fn:function(r) {dd.el.unmask();grid.refresh();},scope:grid}
                            ,failure: {fn:function(r) {dd.el.unmask();},scope:grid}
                        }
                    });
                }
            }
        });
    };
    Ext.applyIf(config,{
        id: 'virtualpage-grid-handler'
        ,url: virtualpage.config.connector_url
        ,baseParams: {
            action: 'mgr/settings/handler/getlist'
        }
        ,fields: ['id', 'name', 'type', 'entry', 'content', 'description', 'cache', 'active', 'name_type']
        ,autoHeight: true
        ,paging: true
        ,remoteSort: true
        ,save_action: 'mgr/settings/handler/updatefromgrid'
        ,autosave: true
		,save_callback: this.updateRow
        ,plugins: this.exp
        ,columns: [this.exp
            ,{header: _('vp_id'),dataIndex: 'id',width: 50, sortable: true}
            ,{header: _('vp_name'),dataIndex: 'name',width: 50, editor: {xtype: 'textfield', allowBlank: false}, sortable: true}
            ,{header: _('vp_type'),dataIndex: 'type',width:50, editor: {xtype:'virtualpage-combo-type', allowBlank: false}, sortable: true, renderer: virtualpage.utils.renderType}
            ,{header: _('vp_entry'),dataIndex: 'entry',width: 50, sortable: true}
            ,{header: _('vp_cache'),dataIndex: 'cache',sortable:true, width:50, editor:{xtype:'combo-boolean', renderer:'boolean'}}
            ,{header: _('vp_active'),dataIndex: 'active',sortable:true, width:50, editor:{xtype:'combo-boolean', renderer:'boolean'}}
        ]
        ,tbar: [{
            text: _('vp_btn_create')
            ,handler: this.createHandler
            ,scope: this
        }]
        ,ddGroup: 'dd'
        ,enableDragDrop: true
        ,listeners: {render: {fn: this.dd, scope: this}}
    });
    virtualpage.grid.Handler.superclass.constructor.call(this,config);
};
Ext.extend(virtualpage.grid.Handler,MODx.grid.Grid,{
    windows: {}

    ,getMenu: function() {
        var m = [];
        m.push({
            text: _('vp_menu_update')
            ,handler: this.updateHandler
        });
        m.push('-');
        m.push({
            text: _('vp_menu_remove')
            ,handler: this.removeHandler
        });
        this.addContextMenuItem(m);
    }

	,updateRow: function(response) {
		Ext.getCmp('virtualpage-grid-handler').refresh();
	}

    ,createHandler: function(btn,e) {
        if (!this.windows.createHandler) {
            this.windows.createHandler = MODx.load({
                xtype: 'virtualpage-window-handler-create'
                ,fields: this.getHandlerFields('create')
                ,listeners: {
                    success: {fn:function() { this.refresh(); },scope:this}
                }
            });
        }
        this.windows.createHandler.fp.getForm().reset();
        this.windows.createHandler.fp.getForm().setValues({
            type: 0
            ,active: 1
        });
        this.windows.createHandler.show(e.target);
    }

    ,updateHandler: function(btn,e) {
        if (!this.menu.record || !this.menu.record.id) return false;
        var r = this.menu.record;
        if (this.windows.updateHandler) {
            try {
                this.windows.updateHandler.close();
                this.windows.updateHandler.destroy();
            } catch (e) {}
        }
        this.windows.updateHandler = MODx.load({
            xtype: 'virtualpage-window-handler-update'
            ,record: r
            ,fields: this.getHandlerFields('update')
            ,listeners: {
                success: {fn:function() { this.refresh(); },scope:this}
            }
        });

        this.windows.updateHandler.fp.getForm().reset();
        this.windows.updateHandler.show(e.target);
        this.windows.updateHandler.fp.getForm().setValues(r);
    }

    ,removeHandler: function(btn,e) {
        if (!this.menu.record) return false;

        MODx.msg.confirm({
            title: _('vp_menu_remove') + ' "' + this.menu.record.name + '"'
            ,text: _('vp_menu_remove_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/settings/handler/remove'
                ,id: this.menu.record.id
            }
            ,listeners: {
                success: {fn:function(r) {this.refresh();}, scope:this}
            }
        });
    }

    ,handleChangeType: function(type, change) {
        var el = Ext.getCmp('virtualpage-handler-type-'+type);
        var entry = Ext.getCmp('virtualpage-handler-entry-'+type);
        var content = Ext.getCmp('virtualpage-handler-content-'+type);

        if(type !== 'update') {entry.reset();}
        if((change == 1) || (change == '1')) {
            entry.clearValue();
            entry.reset();
        }

        switch (el.value) {
            case 0:
            case '0': {
                entry.baseParams.element = 'resource';
                content.disable().hide();
                break;
            }
            case 1:
            case '1': {
                entry.baseParams.element = 'snippet';
                content.disable().hide();
                break;
            }
			case 2:
			case '2': {
				entry.baseParams.element = 'chunk';
                content.disable().hide();
				break;
			}
            case 3:
            case '3': {
                entry.baseParams.element = 'template';
                content.enable().show();
                break;
            }
        }
        entry.store.load();

    }

    ,getHandlerFields: function(type) {
        var fields = [];

        fields.push(
            {xtype: 'hidden',name: 'id', id: 'virtualpage-handler-id-'+type}
            ,{xtype: 'textfield',fieldLabel: _('vp_name'), name: 'name', allowBlank: false, anchor: '99%', id: 'virtualpage-handler-name-'+type}
            ,{xtype: 'virtualpage-combo-type',fieldLabel: _('vp_type'), name: 'type', allowBlank: false, anchor: '99%', id: 'virtualpage-handler-type-'+type
                ,listeners: {
                    afterrender: {fn: function(r) { this.handleChangeType(type, 0);},scope:this }
                    ,select: {fn: function(r) { this.handleChangeType(type, 1);},scope:this }
                }
            }
            ,{xtype: 'virtualpage-combo-entry',fieldLabel: _('vp_entry'), name: 'entry', allowBlank: false, anchor: '99%', id: 'virtualpage-handler-entry-'+type}
            ,{xtype: 'textarea', fieldLabel: _('vp_content'), name: 'content', anchor: '99%', id: 'virtualpage-handler-content-'+type}
            ,{xtype: 'textarea', fieldLabel: _('vp_description'), name: 'description', anchor: '99%', id: 'virtualpage-handler-description-'+type}
        );

        fields.push({xtype: 'checkboxgroup'
            ,columns: 2
            ,items: [
                {xtype: 'xcheckbox', fieldLabel: '', boxLabel: _('vp_active'), name: 'active', id: 'virtualpage-handler-active-'+type}
                ,{xtype: 'xcheckbox', fieldLabel: '', boxLabel: _('vp_cache'), name: 'cache', id: 'virtualpage-handler-cache-'+type}
            ]
            ,id: 'msop2-product-option-group-'+type
        });

        return fields;
    }

});
Ext.reg('virtualpage-grid-handler',virtualpage.grid.Handler);


virtualpage.window.CreateHandler = function(config) {
    config = config || {};
    this.ident = config.ident || 'mecitem'+Ext.id();
    Ext.applyIf(config,{
        title: _('vp_menu_create')
        ,id: this.ident
        ,width: 600
        ,autoHeight: true
        ,labelAlign: 'left'
        ,labelWidth: 180
        ,url: virtualpage.config.connector_url
        ,action: 'mgr/settings/handler/create'
        ,fields: config.fields
        ,keys: [{key: Ext.EventObject.ENTER,shift: true,fn: function() {this.submit() },scope: this}]
    });
    virtualpage.window.CreateHandler.superclass.constructor.call(this,config);
};
Ext.extend(virtualpage.window.CreateHandler,MODx.Window);
Ext.reg('virtualpage-window-handler-create',virtualpage.window.CreateHandler);


virtualpage.window.UpdateHandler = function(config) {
    config = config || {};
    this.ident = config.ident || 'meuitem'+Ext.id();
    Ext.applyIf(config,{
        title: _('vp_menu_update')
        ,id: this.ident
        ,width: 600
        ,autoHeight: true
        ,labelAlign: 'left'
        ,labelWidth: 180
        ,url: virtualpage.config.connector_url
        ,action: 'mgr/settings/handler/update'
        ,fields: config.fields
        ,keys: [{key: Ext.EventObject.ENTER,shift: true,fn: function() {this.submit() },scope: this}]
    });
    virtualpage.window.UpdateHandler.superclass.constructor.call(this,config);
};
Ext.extend(virtualpage.window.UpdateHandler,MODx.Window);
Ext.reg('virtualpage-window-handler-update',virtualpage.window.UpdateHandler);
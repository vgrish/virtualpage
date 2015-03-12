virtualpage.window.CreateItem = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'virtualpage-item-window-create';
	}
	Ext.applyIf(config, {
		title: _('virtualpage_item_create'),
		width: 550,
		autoHeight: true,
		url: virtualpage.config.connector_url,
		action: 'mgr/item/create',
		fields: this.getFields(config),
		keys: [{
			key: Ext.EventObject.ENTER, shift: true, fn: function () {
				this.submit()
			}, scope: this
		}]
	});
	virtualpage.window.CreateItem.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.window.CreateItem, MODx.Window, {

	getFields: function (config) {
		return [{
			xtype: 'textfield',
			fieldLabel: _('virtualpage_item_name'),
			name: 'name',
			id: config.id + '-name',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'textarea',
			fieldLabel: _('virtualpage_item_description'),
			name: 'description',
			id: config.id + '-description',
			height: 150,
			anchor: '99%'
		}, {
			xtype: 'xcheckbox',
			boxLabel: _('virtualpage_item_active'),
			name: 'active',
			id: config.id + '-active',
			checked: true,
		}];
	}

});
Ext.reg('virtualpage-item-window-create', virtualpage.window.CreateItem);


virtualpage.window.UpdateItem = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'virtualpage-item-window-update';
	}
	Ext.applyIf(config, {
		title: _('virtualpage_item_update'),
		width: 550,
		autoHeight: true,
		url: virtualpage.config.connector_url,
		action: 'mgr/item/update',
		fields: this.getFields(config),
		keys: [{
			key: Ext.EventObject.ENTER, shift: true, fn: function () {
				this.submit()
			}, scope: this
		}]
	});
	virtualpage.window.UpdateItem.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.window.UpdateItem, MODx.Window, {

	getFields: function (config) {
		return [{
			xtype: 'hidden',
			name: 'id',
			id: config.id + '-id',
		}, {
			xtype: 'textfield',
			fieldLabel: _('virtualpage_item_name'),
			name: 'name',
			id: config.id + '-name',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'textarea',
			fieldLabel: _('virtualpage_item_description'),
			name: 'description',
			id: config.id + '-description',
			anchor: '99%',
			height: 150,
		}, {
			xtype: 'xcheckbox',
			boxLabel: _('virtualpage_item_active'),
			name: 'active',
			id: config.id + '-active',
		}];
	}

});
Ext.reg('virtualpage-item-window-update', virtualpage.window.UpdateItem);
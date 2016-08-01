virtualpage.window.UpdateHandler = function (config) {
	config = config || {};

	Ext.applyIf(config, {
		title: _('create'),
		url: virtualpage.config.connector_url,
		action: 'mgr/settings/handler/update',
		fields: this.getFields(config),
		keys: this.getKeys(config),
		width: 600,
		autoHeight: true,
		cls: 'virtualpage-panel-handler'
	});
	virtualpage.window.UpdateHandler.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.window.UpdateHandler, MODx.Window, {

	getKeys: function () {
		return [{
			key: Ext.EventObject.ENTER,
			shift: true,
			fn: this.submit,
			scope: this
		}];
	},

	getFields: function (config) {
		return [{
			layout: 'form',
			defaults: {border: false, anchor: '100%'},
			items: [{
				xtype: 'hidden',
				name: 'id'
			}, {
				xtype: 'textfield',
				fieldLabel: _('virtualpage_name'),
				name: 'name',
				allowBlank: false
			}, {
				items: [{
					layout: 'form',
					defaults: {border: false, anchor: '100%'},
					items: [{
						layout: 'column',
						border: false,
						items: [{
							columnWidth: .49,
							border: false,
							layout: 'form',
							items: [{
								xtype: 'virtualpage-combo-type',
								fieldLabel: _('virtualpage_type'),
								name: 'type',
								anchor: '100%',
								allowBlank: false,
								listeners: {
									afterrender: {
										fn: function (r) {
											this.handleChangeType(0);
										},
										scope: this
									},
									select: {
										fn: function (r) {
											this.handleChangeType(1);
										},
										scope: this
									}
								}
							}]
						}, {
							columnWidth: .51,
							border: false,
							layout: 'form',
							cls: 'right-column',
							items: [{
								xtype: 'virtualpage-combo-entry',
								fieldLabel: _('virtualpage_entry'),
								name: 'entry',
								anchor: '100%',
								allowBlank: false
							}]
						}]
					}]
				}]
			}, {
				xtype: 'textarea',
				fieldLabel: _('virtualpage_content'),
				name: 'content',
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
				}, {
					xtype: 'xcheckbox',
					fieldLabel: '',
					boxLabel: _('virtualpage_cache'),
					name: 'cache',
					checked: config.record.cache
				}]
			}]
		}];
	},

	handleChangeType: function (change) {

		var f = this.fp.getForm();
		var _type = f.findField('type');
		var _entry = f.findField('entry');
		var _content = f.findField('content');

		var type = parseInt(_type.getValue());
		var entry = parseInt(_entry.getValue());

		switch (type) {
			case 0:
			{
				_entry.baseParams.element = 'resource';
				_content.disable().hide();
				break;
			}
			case 1:
			{
				_entry.baseParams.element = 'snippet';
				_content.disable().hide();
				break;
			}
			case 2:
			{
				_entry.baseParams.element = 'chunk';
				_content.disable().hide();
				break;
			}
			case 3:
			{
				_entry.baseParams.element = 'template';
				_content.enable().show();
				break;
			}
		}

		_entry.baseParams.id = entry;

		if (1 == change) {
			_entry.setValue();
			_entry.store.load();
		}

		if (!!_entry.pageTb) {
			_entry.pageTb.show();
		}

	}

});
Ext.reg('virtualpage-window-handler-update', virtualpage.window.UpdateHandler);

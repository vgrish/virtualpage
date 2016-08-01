virtualpage.window.UpdateRoute = function (config) {
	config = config || {};

	Ext.applyIf(config, {
		title: _('create'),
		url: virtualpage.config.connector_url,
		action: 'mgr/settings/route/update',
		fields: this.getFields(config),
		keys: this.getKeys(config),
		width: 600,
		autoHeight: true,
		cls: 'virtualpage-panel-route'
	});
	virtualpage.window.UpdateRoute.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.window.UpdateRoute, MODx.Window, {

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
				fieldLabel: _('virtualpage_route'),
				name: 'route',
				allowBlank: false
			}, {
				items: [{
					layout: 'form',
					cls: 'modx-panel',
					items: [{
						layout: 'column',
						defaults: {border: false, anchor: '100%'},
						items: [{
							columnWidth: .49,
							border: false,
							layout: 'form',
							items: [{
								xtype: 'virtualpage-combo-metod',
								fieldLabel: _('virtualpage_metod'),
								name: 'metod',
								anchor: '100%',
								allowBlank: false
							}, {
								xtype: 'virtualpage-combo-event',
								fieldLabel: _('virtualpage_event'),
								name: 'event',
								anchor: '100%',
								allowBlank: false
							}]
						}, {
							columnWidth: .51,
							border: false,
							layout: 'form',
							cls: 'right-column',
							items: [{
								xtype: 'virtualpage-combo-handler',
								fieldLabel: _('virtualpage_handler'),
								name: 'handler',
								anchor: '100%',
								allowBlank: false
							}]
						}]
					}]
				}]
			}, {
				xtype: 'textarea',
				fieldLabel: _('virtualpage_placeholders'),
				name: 'properties',
				setValue: function (value) {
					if (Ext.isObject(value)) {
						value = Ext.util.JSON.encode(value);
					}
					return Ext.form.TextField.superclass.setValue.call(this, value);
				}
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
Ext.reg('virtualpage-window-route-update', virtualpage.window.UpdateRoute);

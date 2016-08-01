virtualpage.grid.Handler = function (config) {
	config = config || {};

	this.exp = new Ext.grid.RowExpander({
		expandOnDblClick: false,
		enableCaching: false,
		tpl: new Ext.XTemplate(
			'<tpl for=".">',

			'<table class="virtualpage-expander"><tbody>',

			'<tpl if="description">',
			'<tr>',
			'<td><b>' + _('virtualpage_description') + ': </b>{description}</td>',
			'</tr>',
			'</tpl>',

			' </tbody></table>',

			'</tpl>',
			{
				compiled: true,
			}
		),
		renderer: function (v, p, record) {
			return !!record.json['description'] ? '<div class="x-grid3-row-expander">&#160;</div>' : '&#160;';
		}
	});

	this.exp.on('beforeexpand', function (rowexpander, record, body, rowIndex) {
		record['data']['json'] = record['json'];
		record['data'] = Ext.applyIf(record['data'], record['json']);
		return true;
	});

	this.dd = function (grid) {
		this.dropTarget = new Ext.dd.DropTarget(grid.container, {
			ddGroup: 'dd',
			copy: false,
			notifyDrop: function (dd, e, data) {
				var store = grid.store.data.items;
				var target = store[dd.getDragData(e).rowIndex].id;
				var source = store[data.rowIndex].id;
				if (target != source) {
					dd.el.mask(_('loading'), 'x-mask-loading');
					MODx.Ajax.request({
						url: virtualpage.config.connector_url,
						params: {
							action: config.action || 'mgr/handler/sort',
							source: source,
							target: target
						},
						listeners: {
							success: {
								fn: function (r) {
									dd.el.unmask();
									grid.refresh();
								},
								scope: grid
							},
							failure: {
								fn: function (r) {
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

	this.sm = new Ext.grid.CheckboxSelectionModel();

	Ext.applyIf(config, {
		url: virtualpage.config.connector_url,
		baseParams: {
			action: 'mgr/handler/getlist',
			sort: 'rank',
			dir: 'asc'
		},
		save_action: 'mgr/handler/updatefromgrid',
		autosave: true,
		save_callback: this._updateRow,
		fields: this.getFields(config),
		columns: this.getColumns(config),
		tbar: this.getTopBar(config),
		listeners: this.getListeners(config),

		sm: this.sm,
		plugins: [this.exp],

		ddGroup: 'dd',
		enableDragDrop: true,

		paging: true,
		pageSize: 10,
		remoteSort: true,
		viewConfig: {
			forceFit: true,
			enableRowBody: true,
			autoFill: true,
			showPreview: true,
			scrollOffset: 0
		},
		autoHeight: true,
		cls: 'virtualpage-grid',
		bodyCssClass: 'grid-with-buttons',
		stateful: false,
	});
	virtualpage.grid.Handler.superclass.constructor.call(this, config);
	this.exp.addEvents('beforeexpand', 'beforecollapse');

};
Ext.extend(virtualpage.grid.Handler, MODx.grid.Grid, {
	windows: {},

	getFields: function (config) {
		var fields = [
			'id', 'name', 'type', 'entry', 'content', 'description', 'cache', 'active',
			'name_type', 'rank', 'properties', 'actions'
		];

		return fields;
	},

	getTopBar: function (config) {
		var tbar = [];

		var component = ['menu', 'download', 'left', 'search', 'spacer'];

		var add = {
			menu: {
				text: '<i class="icon icon-cogs"></i> ',
				menu: [{
					text: '<i class="icon icon-plus"></i> ' + _('virtualpage_action_create'),
					cls: 'virtualpage-cogs',
					handler: this.create,
					scope: this
				}, {
					text: '<i class="icon icon-trash-o red"></i> ' + _('virtualpage_action_remove'),
					cls: 'virtualpage-cogs',
					handler: this.remove,
					scope: this
				}, '-', {
					text: '<i class="icon icon-toggle-on green"></i> ' + _('virtualpage_action_turnon'),
					cls: 'virtualpage-cogs',
					handler: this.active,
					scope: this
				}, {
					text: '<i class="icon icon-toggle-off red"></i> ' + _('virtualpage_action_turnoff'),
					cls: 'virtualpage-cogs',
					handler: this.inactive,
					scope: this
				}]
			},
			left: '->',

			search: {
				xtype: 'virtualpage-field-search',
				width: 190,
				listeners: {
					search: {
						fn: function (field) {
							this._doSearch(field);
						},
						scope: this
					},
					clear: {
						fn: function (field) {
							field.setValue('');
							this._clearSearch();
						},
						scope: this
					}
				}
			},

			spacer: {
				xtype: 'spacer',
				style: 'width:1px;'
			}
		};

		component.filter(function (item) {
			if (add[item]) {
				tbar.push(add[item]);
			}
		});

		var items = [];
		if (tbar.length > 0) {
			items.push(new Ext.Toolbar(tbar));
		}

		return new Ext.Panel({items: items});
	},

	getColumns: function (config) {
		var columns = [this.exp, this.sm];
		var add = {
			id: {
				width: 5,
				/*hidden: true,*/
			},
			name: {
				width: 10,
				sortable: true,
				editor: {
					xtype: 'textfield',
					allowBlank: false
				}
			},
			type: {
				width: 10,
				sortable: true,
				editor: {
					xtype: 'virtualpage-combo-type',
					allowBlank: false
				},
				renderer: virtualpage.tools.renderType
			},
			entry: {
				width: 10,
				sortable: true
			},
			/*cache: {
				width: 35,
				sortable: false,
				editor: {
					xtype: 'combo-boolean',
					renderer: 'boolean'
				}
			},*/
			actions: {
				width: 10,
				sortable: false,
				id: 'actions',
				renderer: virtualpage.tools.renderActions,
			}
		};

		var fields = this.getFields();
		fields.filter(function (field) {
			if (add[field]) {
				Ext.applyIf(add[field], {
					header: _('virtualpage_header_' + field),
					tooltip: _('virtualpage_tooltip_' + field),
					dataIndex: field
				});
				columns.push(add[field]);
			}
		});

		return columns;
	},

	getListeners: function (config) {
		return Ext.applyIf(config.listeners || {}, {
			render: {
				fn: this.dd,
				scope: this
			}
		});
	},

	getMenu: function (grid, rowIndex) {
		var ids = this._getSelectedIds();
		var row = grid.getStore().getAt(rowIndex);
		var menu = virtualpage.tools.getMenu(row.data['actions'], this, ids);
		this.addContextMenuItem(menu);
	},

	onClick: function (e) {
		var elem = e.getTarget();
		if (elem.nodeName == 'BUTTON') {
			var row = this.getSelectionModel().getSelected();
			if (typeof(row) != 'undefined') {
				var action = elem.getAttribute('action');
				if (action == 'showMenu') {
					var ri = this.getStore().find('id', row.id);
					return this._showMenu(this, ri, e);
				} else if (typeof this[action] === 'function') {
					this.menu.record = row.data;
					return this[action](this, e);
				}
			}
		}
		return this.processEvent('click', e);
	},

	setAction: function (method, field, value) {
		var ids = this._getSelectedIds();
		if (!ids.length && (field !== 'false')) {
			return false;
		}
		MODx.Ajax.request({
			url: virtualpage.config.connector_url,
			params: {
				action: 'mgr/handler/multiple',
				method: method,
				field_name: field,
				field_value: value,
				ids: Ext.util.JSON.encode(ids)
			},
			listeners: {
				success: {
					fn: function () {
						this.refresh();
					},
					scope: this
				},
				failure: {
					fn: function (response) {
						MODx.msg.alert(_('error'), response.message);
					},
					scope: this
				}
			}
		})
	},

	remove: function (text, action) {
		if (this.destroying) {
			return MODx.grid.Grid.superclass.remove.apply(this, arguments);
		}

		Ext.MessageBox.confirm(
			_('virtualpage_action_remove'),
			_('virtualpage_confirm_remove'),
			function (e) {
				if (e == 'yes') {
					this.setAction('remove');
				} else {
					this.fireHandler('cancel');
				}
			}, this);
	},

	active: function (btn, e) {
		this.setAction('setproperty', 'active', 1);
	},

	inactive: function (btn, e) {
		this.setAction('setproperty', 'active', 0);
	},

	cacheon: function (btn, e) {
		this.setAction('setproperty', 'cache', 1);
	},

	cacheoff: function (btn, e) {
		this.setAction('setproperty', 'cache', 0);
	},

	create: function (btn, e) {
		var record = {
			type: 0,
			active: 1
		};
		
		var w = MODx.load({
			xtype: 'virtualpage-window-handler-update',
			action: 'mgr/handler/create',
			record: record,
			class: this.config.class,
			listeners: {
				success: {
					fn: function () {
						this.refresh();
					}, scope: this
				}
			}
		});
		w.reset();
		w.setValues(record);
		w.show(e.target);
	},

	update: function (btn, e, row) {
		if (typeof(row) != 'undefined') {
			this.menu.record = row.data;
		}
		else if (!this.menu.record) {
			return false;
		}
		var id = this.menu.record.id;
		MODx.Ajax.request({
			url: this.config.url,
			params: {
				action: 'mgr/handler/get',
				id: id
			},
			listeners: {
				success: {
					fn: function (r) {
						var record = r.object;
						var w = MODx.load({
							xtype: 'virtualpage-window-handler-update',
							title: _('virtualpage_action_update'),
							action: 'mgr/handler/update',
							record: record,
							update: true,
							listeners: {
								success: {
									fn: this.refresh,
									scope: this
								}
							}
						});
						w.reset();
						w.setValues(record);
						w.show(e.target);
					}, scope: this
				}
			}
		});
	},

	_filterByCombo: function (cb) {
		this.getStore().baseParams[cb.name] = cb.value;
		this.getBottomToolbar().changePage(1);
	},

	_doSearch: function (tf) {
		this.getStore().baseParams.query = tf.getValue();
		this.getBottomToolbar().changePage(1);
	},

	_clearSearch: function () {
		this.getStore().baseParams.query = '';
		this.getBottomToolbar().changePage(1);
	},

	_updateRow: function () {
		this.refresh();
	},

	_getSelectedIds: function () {
		var ids = [];
		var selected = this.getSelectionModel().getSelections();

		for (var i in selected) {
			if (!selected.hasOwnProperty(i)) {
				continue;
			}
			ids.push(selected[i]['id']);
		}

		return ids;
	}

});
Ext.reg('virtualpage-grid-handler', virtualpage.grid.Handler);

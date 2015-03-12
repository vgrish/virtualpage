virtualpage.panel.Home = function (config) {
	config = config || {};
	Ext.apply(config, {
		baseCls: 'modx-formpanel',
		layout: 'anchor',
		/*
		 stateful: true,
		 stateId: 'virtualpage-panel-home',
		 stateEvents: ['tabchange'],
		 getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
		 */
		hideMode: 'offsets',
		items: [{
			html: '<h2>' + _('virtualpage') + '</h2>',
			cls: '',
			style: {margin: '15px 0'}
		}, {
			xtype: 'modx-tabs',
			defaults: {border: false, autoHeight: true},
			border: true,
			hideMode: 'offsets',
			items: [{
				title: _('virtualpage_items'),
				layout: 'anchor',
				items: [{
					html: _('virtualpage_intro_msg'),
					cls: 'panel-desc',
				}, {
					xtype: 'virtualpage-grid-items',
					cls: 'main-wrapper',
				}]
			}]
		}]
	});
	virtualpage.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.panel.Home, MODx.Panel);
Ext.reg('virtualpage-panel-home', virtualpage.panel.Home);

virtualpage.page.Home = function (config) {
	config = config || {};
	Ext.applyIf(config, {
		components: [{
			xtype: 'virtualpage-panel-home', renderTo: 'virtualpage-panel-home-div'
		}]
	});
	virtualpage.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage.page.Home, MODx.Component);
Ext.reg('virtualpage-page-home', virtualpage.page.Home);
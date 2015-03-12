var virtualpage = function (config) {
	config = config || {};
	virtualpage.superclass.constructor.call(this, config);
};
Ext.extend(virtualpage, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('virtualpage', virtualpage);

virtualpage = new virtualpage();
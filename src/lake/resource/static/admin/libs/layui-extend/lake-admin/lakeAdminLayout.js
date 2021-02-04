/*!
 * lakeAdminLayout.js v1.0.2
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
layui.define([
    'element', 
    'layer', 
    'form', 
    'lakeTool', 
    'lakeBind', 
    'lakeAdminSkin', 
    'contextMenu'
], function(exports) {
    var element = layui.element,
        layer = layui.layer,
        $ = layui.jquery,
        form = layui.form,
        lakeAdminSkin = layui.lakeAdminSkin,
        contextMenu = layui.contextMenu;
    
    // 监听主题
    lakeAdminSkin.listenSkin();
    
    !(function() {
        if (contextMenu) {
            $('.layui-card-header').on('contextmenu', function (e) {
                contextMenu.bind(this, [{
                    icon: 'layui-icon layui-icon-up',
                    name: '回到顶部',
                    click: function () {
                        $('html,body').animate({
                            scrollTop: 0
                        },'slow');
                    }
                }, {
                    icon: 'layui-icon layui-icon-refresh-3',
                    name: '刷新页面',
                    click: function () {
                        window.location.reload();
                    }
                }, {
                    icon: 'layui-icon layui-icon-down',
                    name: '回到底部',
                    click: function () {
                        $("html,body").animate({
                            scrollTop: document.body.clientHeight
                        },1500);
                    }
                }]);
                return false;
            });
        }
        
    })();
    
    exports('lakeAdminLayout', {});
});
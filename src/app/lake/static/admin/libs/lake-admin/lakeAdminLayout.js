layui.define(['element', 'layer', 'form', 'contextMenu'], function(exports) {
    var element = layui.element,
        layer = layui.layer,
        $ = layui.jquery,
        form = layui.form,
        contextMenu = layui.contextMenu;
        
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
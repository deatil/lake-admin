/*!
 * lakeAdminMenu.js v1.0.2
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
!(function(a){
    layui.define(['jquery'], function (exports) {
        var jquery = layui.$;
        
        exports('lakeAdminMenu', a(jquery));
    });
})(function($) {
    
    // 生成菜单
    var lakeAdminMenu = {
        buildTop: function(object) {
            var html = '';
            $.each(object, function(i, o) {
                if ((typeof o.items) != 'undefined') {
                    var url = 'javascript:;';
                    if (o.url) {
                        url = o.url;
                    }
                    
                    var icon = '';
                    if (o.icon) {
                        icon = '<i class="iconfont ' + o.icon + '"></i>&nbsp;';
                    }
                    
                    html += '<li class="layui-nav-item">'
                        + '<a href="' + url + '" title="' + o.title + '" lay-id="' + o.menuid + '" data-id="' + o.id + '">'
                            + icon
                            + '<span class="layui-nav-title">' + o.title + '</span>'
                        + '</a>'
                    + '</li>';
                }
            });
            
            return html;
        },
        buildLeft: function(object) {
            var thiz = this;
            var menu_html = '';
            
            $.each(object, function(i, data) {
                var child_html = '';
                
                if (data.items && typeof(data.items) === 'object') {
                    child_html = thiz.buildLeftChild(data.items);
                }
                
                var menu_icon = '';
                if (data.icon) {
                    menu_icon = '<i class="iconfont ' + data.icon + '"></i>&nbsp;';
                }
                
                var url = 'javascript:;';
                if (data.url) {
                    url = data.url;
                }
                
                menu_html += 
                    '<li class="layui-nav-item layui-nav-itemed lake-admin-nav-item">'
                        + '<a href="' + url + '" class="lay-tip-title" lay-id="' + data.menuid + '" data-id="' + data.id + '" lay-icon="iconfont ' + data.icon + '" lay-title="' + data.title + '">'
                            + menu_icon
                            + '<span class="layui-nav-title">'
                                + '<cite>' + data.title + '</cite>'
                            +'</span>'
                        + '</a>'
                        + child_html
                    + '</li>';
            });
            
            return menu_html;
        },
        buildLeftChild: function(object) {
            var thiz = this;
            var menu_dd_html = '';
            
            $.each(object, function(i, data) {
                var menu_icon = '';
                if (data.icon) {
                    menu_icon = '<i class="iconfont ' + data.icon + '"></i>&nbsp;';
                }
                
                var menu_child_html = '';
                if (data.items && typeof(data.items) === 'object') {
                    menu_child_html = thiz.buildLeftChild(data.items);
                }
                
                var url = 'javascript:;';
                if (data.url) {
                    url = data.url;
                }
                
                menu_dd_html += '<dd>'
                    + '<a href="' + url + '" class="lay-tip-title" lay-id="' + data.menuid + '" data-id="' + data.id + '" lay-icon="iconfont ' + data.icon + '" lay-title="' + data.title + '">'
                        + menu_icon
                        + '<span class="layui-nav-title">' 
                            + data.title 
                        + '</span>'
                    + '</a>'
                    + menu_child_html
                + '</dd>';
            });
            
            var menu_html = '<dl class="layui-nav-child">' 
                + menu_dd_html 
                + '</dl>';
            
            return menu_html;
        },
        
        // 通过菜单id查找菜单配置对象
        getMenuByID: function(mid, menus) {
            var thiz = this;
            var ret = {};
            
            if (mid) {
                $.each(menus, function(i, o) {
                    if (o.menuid && o.menuid == mid) {
                        ret = o;
                        return false
                    } else if (o.items) {
                        var tmp = thiz.getMenuByID(mid, o.items);
                        if (tmp.menuid && mid == tmp.menuid) {
                            ret = tmp;
                            return false
                        }
                    }
                });
            }
            return ret;
        },

        getTopMenuByID: function(mid, menus) {
            var ret = {};
            var menu = this.getMenuByID(mid, menus);
            if (menu) {
                if (menu.parent) {
                    var tmp = this.getTopMenuByID(menu.parent, menus);
                    if (tmp && tmp.menuid) {
                        ret = tmp;
                    }
                } else {
                    ret = menu;
                }
            }
            return ret;
        },
        
    };
    
    return lakeAdminMenu;
});

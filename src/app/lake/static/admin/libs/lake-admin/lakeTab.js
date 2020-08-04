/*!
 * lakeTab.js v1.0.2
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
layui.define(['jquery'], function (exports) {
    var $ = layui.$;
    
    $.fn.lakeTab = function(options) {
        var opts = $.extend({}, $.fn.lakeTab.defaults, options);
        
        var thiz = this;
        
        return {
            add: function(id, title, icon) {
                var li = $(thiz).find('li[lay-id=' + id + ']');
                if (li.length > 0) {
                    li.addClass('current');
                } else {
                    var title = icon + '<span class="layui-nav-title">' + title + '</span>';
                    var li = $('<li class="layui-tab-item lake-admin-tab-item">' + title + '<i class="layui-icon layui-unselect layui-tab-close">&#x1006;</i></li>').attr('lay-id', id);
                    li.appendTo('#body_history');
                }
            },
            remove: function(li) {
                var prev_li = li.prev('li'),
                    data_id = li.attr('lay-id');
                
            },
            prev: function() {
                
            },
            next: function() {
                
            },
            animate: function(leftWith) {
                $(thiz).animate({
                    left: leftWith
                }, 200, 'swing');
            },
            
        };
    };
    
    $.fn.lakeTab.defaults = {
        el: "",
    };
    
    exports('lakeTab', {});
});

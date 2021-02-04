/*!
 * lakeAdminContextmenu.js v1.0.2
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
!(function(a){
    layui.define(['jquery', 'jqueryCookie'], function (exports) {
        var jquery = layui.$;
        
        exports('lakeAdminContextmenu', a(jquery));
    });
})(function($) {
    
    // 右键
    var lakeAdminContextmenu = {
        
        // 重新刷新页面，使用location.reload()有可能导致重新提交
        reloadPage: function(win) {
            var location = win.location;
            location.href = location.pathname + location.search;
        },
        
        close: function(el) {
            el = (typeof el === 'object') ? el : $(el);
            
            el.find('div.lake-admin-contextmenu').remove();
        },
        
        listen: function() {
            var thiz = this;
            
            // 右键
            $(document).on('contextmenu', ".lake-admin-top-tab li", function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $target = e.target.nodeName === 'LI' ? e.target : e.target.parentElement;
                //判断，如果存在右键菜单的div，则移除，保存页面上只存在一个
                if ($(document).find('div.lake-admin-contextmenu').length > 0) {
                    $(document).find('div.lake-admin-contextmenu').remove();
                }

                var thisDataId = $(this).attr('lay-id');
                if (!(thisDataId != '' && thisDataId != 'default')) {
                    var ul = '<ul>';
                        ul += '<li data-target="lake-admin-contextmenu-refresh-page" title="刷新当前选项卡"><i class="iconfont icon-shuaxin" aria-hidden="true"></i> 刷新</li>';
                        ul += '<li data-target="lake-admin-contextmenu-close-all-page" title="关闭全部选项卡"><i class="iconfont icon-richangqingli" aria-hidden="true"></i> 全部关闭</li>';
                        ul += '</ul>';
                } else {
                    var ul = '<ul>';
                        ul += '<li data-target="lake-admin-contextmenu-refresh-page" title="刷新当前选项卡"><i class="iconfont icon-shuaxin" aria-hidden="true"></i> 刷新</li>';
                        ul += '<li data-target="lake-admin-contextmenu-close-current-page" title="关闭当前选项卡"><i class="layui-icon layui-icon-close" aria-hidden="true"></i> 关闭当前</li>';
                        ul += '<li data-target="lake-admin-contextmenu-close-other-page" title="关闭其他选项卡"><i class="layui-icon layui-icon-radio" aria-hidden="true"></i> 关闭其他</li>';
                        ul += '<li data-target="lake-admin-contextmenu-close-prev-page" title="关闭左侧选项卡"><i class="layui-icon layui-icon-left" aria-hidden="true"></i> 关闭左侧</li>';
                        ul += '<li data-target="lake-admin-contextmenu-close-next-page" title="关闭右侧选项卡"><i class="layui-icon layui-icon-right" aria-hidden="true"></i> 关闭右侧</li>';
                        ul += '<li data-target="lake-admin-contextmenu-close-all-page" title="关闭全部选项卡"><i class="iconfont icon-richangqingli" aria-hidden="true"></i> 全部关闭</li>';
                        ul += '</ul>';
                }
                
                var contextmenuHtml = '<div class="lake-admin-contextmenu">' + ul + '</div>';
                contextmenuHtml = $(contextmenuHtml).css({
                    "top": e.pageY + 'px',
                    "left": e.pageX + 'px',
                    "width": "130px",
                    "background-color": "white",
                    "z-index": 999999,
                });
                
                var contextmenuHtmlMask = '<div class="lake-admin-contextmenu-mask"></div>';
                contextmenuHtmlMask = $(contextmenuHtmlMask).css({
                    "position": "absolute",
                    "top": 0,
                    "bottom": 0,
                    "width": "100%",
                    "background-color": "rgb(255, 255, 255,0)",
                    "padding": "0",
                    "overflow": "hidden",
                    "z-index": 900000,
                });
                
                // 将dom添加到body的末尾
                $('body').append(contextmenuHtml)
                    .append(contextmenuHtmlMask);
            
                var topNav = $(".lake-admin-top-tab");
                
                // 获取当前点击选项卡的id值
                var id = $($target).attr('lay-id');
                var $context = $(document).find('div.lake-admin-contextmenu');
                var $maskContext = $(document).find('div.lake-admin-contextmenu-mask');
                if ($context.length > 0) {
                    $context.eq(0).children('ul').children('li').each(function () {
                        var $that = $(this);
                        // 绑定菜单的点击事件
                        $that.on('click', function () {
                            // 获取点击的target值
                            var target = $that.data('target');
                            
                            switch (target) {
                                // 刷新当前
                                case 'lake-admin-contextmenu-refresh-page': 
                                    var index = layer.load();
                                    var iframe = $('#iframe_' + id);
                                    if (iframe[0].contentWindow) {
                                        thiz.reloadPage(iframe[0].contentWindow);
                                        layer.close(index);
                                    }
                                    break;
                                // 关闭当前
                                case 'lake-admin-contextmenu-close-current-page': 
                                    if ($($target).find(".layui-tab-close").length > 0) {
                                        $($target).find(".layui-tab-close").trigger('click');
                                    }
                                    break;
                                // 关闭左侧
                                case 'lake-admin-contextmenu-close-prev-page': 
                                    var hasCurrentPostion = false;
                                    topNav.children('li').each(function () {
                                        if ($(this).attr('lay-id') == id 
                                            || hasCurrentPostion == true
                                        ) {
                                            hasCurrentPostion = true;
                                            return ;
                                        }
                                        
                                        if ($(this).find(".layui-tab-close").length > 0) {
                                            $(this).find(".layui-tab-close").trigger('click');
                                        }
                                    });
                                    
                                    $('li[lay-id="'+id+'"]').trigger('click');
                                    
                                    break;
                                // 关闭右侧
                                case 'lake-admin-contextmenu-close-next-page': 
                                    var hasCurrentPostion = false;
                                    topNav.children('li').each(function () {
                                        if ($(this).attr('lay-id') == id) {
                                            hasCurrentPostion = true;
                                            return ;
                                        }
                                        
                                        if (hasCurrentPostion == false) {
                                            return ;
                                        }
                                        
                                        if ($(this).find(".layui-tab-close").length > 0) {
                                            $(this).find(".layui-tab-close").trigger('click');
                                        }
                                    });
                                    
                                    $('li[lay-id="'+id+'"]').trigger('click');
                                    
                                    break;
                                // 关闭其他
                                case 'lake-admin-contextmenu-close-other-page': 
                                    topNav.children('li').each(function () {
                                        if ($(this).attr('lay-id') == id) {
                                            return;
                                        }
                                        
                                        if ($(this).find(".layui-tab-close").length > 0) {
                                            $(this).find(".layui-tab-close").trigger('click');
                                        }
                                    });
                                    
                                    $('li[lay-id="'+id+'"]').trigger('click');
                                    
                                    break;
                                // 全部关闭
                                case 'lake-admin-contextmenu-close-all-page': 
                                    topNav.children('li').each(function () {
                                        if ($(this).find(".layui-tab-close").length > 0) {
                                            $(this).find(".layui-tab-close").trigger('click');
                                        }
                                    });
                                    
                                    $('li[lay-id="default"]').trigger('click');
                                    
                                    break;
                            }
                            
                            // 处理完后移除右键菜单的dom
                            $maskContext.trigger('click');
                        });
                    });

                    $maskContext.on('click', function () {
                        $context.remove();
                        $maskContext.remove();
                    });
                }
                return false;
            });
            
        }
    };
    
    return lakeAdminContextmenu;
});

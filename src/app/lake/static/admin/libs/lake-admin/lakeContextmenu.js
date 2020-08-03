/*!
 * lakeContextmenu.js v1.0.2
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
!(function(a){
    layui.define(['jquery', 'jqueryCookie'], function (exports) {
        var jquery = layui.$;
        
        exports('lakeContextmenu', a(jquery));
    });
})(function($) {
    
    // 右键
    var lakeContextmenu = {
        
        // 重新刷新页面，使用location.reload()有可能导致重新提交
        reloadPage: function(win) {
            var location = win.location;
            location.href = location.pathname + location.search;
        },
        
        listen: function() {
            var thiz = this;
            
            // 右键
            $(document).on('contextmenu', ".lake-admin-top-tab li", function (e) {
                e.preventDefault();
                e.stopPropagation();
            
                var $that = $(e.target);

                var $target = e.target.nodeName === 'LI' ? e.target : e.target.parentElement;
                //判断，如果存在右键菜单的div，则移除，保存页面上只存在一个
                if ($(document).find('div.lake-admin-contextmenu').length > 0) {
                    $(document).find('div.lake-admin-contextmenu').remove();
                }
                //创建一个div
                var div = document.createElement('div');
                //设置一些属性
                div.className = 'lake-admin-contextmenu';
                div.style.width = '130px';
                div.style.backgroundColor = 'white';

                var this_data_id = $(this).attr('lay-id');
                if (!(this_data_id != '' && this_data_id != 'default')) {
                    var ul = '<ul>';
                    ul += '<li data-target="lake-admin-contextmenu-refresh-page" title="刷新当前选项卡"><i class="iconfont icon-shuaxin" aria-hidden="true"></i> 刷新</li>';
                    ul += '<li data-target="lake-admin-contextmenu-close-other-page" title="关闭其他选项卡"><i class="layui-icon layui-icon-radio" aria-hidden="true"></i> 关闭其他</li>';
                    ul += '<li data-target="lake-admin-contextmenu-close-all-page" title="关闭全部选项卡"><i class="iconfont icon-richangqingli" aria-hidden="true"></i> 全部关闭</li>';
                    ul += '</ul>';
                } else {
                    var ul = '<ul>';
                    ul += '<li data-target="lake-admin-contextmenu-refresh-page" title="刷新当前选项卡"><i class="iconfont icon-shuaxin" aria-hidden="true"></i> 刷新</li>';
                    ul += '<li data-target="lake-admin-contextmenu-close-current-page" title="关闭当前选项卡"><i class="layui-icon layui-icon-close" aria-hidden="true"></i> 关闭当前</li>';
                    ul += '<li data-target="lake-admin-contextmenu-close-other-page" title="关闭其他选项卡"><i class="layui-icon layui-icon-radio" aria-hidden="true"></i> 关闭其他</li>';
                    ul += '<li data-target="lake-admin-contextmenu-close-all-page" title="关闭全部选项卡"><i class="iconfont icon-richangqingli" aria-hidden="true"></i> 全部关闭</li>';
                    ul += '</ul>';
                }
                
                div.innerHTML = ul;
                div.style.top = e.pageY + 'px';
                div.style.left = e.pageX + 'px';
                // 将dom添加到body的末尾
                document.getElementsByTagName('body')[0].appendChild(div);
            
                var top_nav = $(".lake-admin-top-tab");
                // 获取当前点击选项卡的id值
                var id = $($target).attr('lay-id');
                // 获取当前点击选项卡的索引值
                var clickIndex = $($target).attr('lay-id');
                var top_tab_ul = $('#body_history');
                var top_tab_prev_with = $('#layui_iframe_refresh').outerWidth(true) + $('#page-prev').outerWidth(true);
                var $context = $(document).find('div.lake-admin-contextmenu');
                if ($context.length > 0) {
                    $context.eq(0).children('ul').children('li').each(function () {
                        var $that = $(this);
                        //绑定菜单的点击事件
                        $that.on('click', function () {
                            //获取点击的target值
                            var target = $that.data('target');
                            //
                            switch (target) {
                                case 'lake-admin-contextmenu-refresh-page': //刷新当前
                                    var index = layer.load();
                                    var iframe = $('#iframe_' + id);
                                    if (iframe[0].contentWindow) {
                                        thiz.reloadPage(iframe[0].contentWindow);
                                        layer.close(index);
                                    }
                                    break;
                                case 'lake-admin-contextmenu-close-current-page': //关闭当前
                                    if ($($target).find(".layui-tab-close").length > 0) {
                                        $($target).find(".layui-tab-close").trigger('click');
                                    }
                                    break;
                                case 'lake-admin-contextmenu-close-other-page': //关闭其他
                                    top_nav.children('li').each(function () {
                                        if ($(this).attr('lay-id') == id) {
                                            return;
                                        }
                                        
                                        if ($(this).find(".layui-tab-close").length > 0) {
                                            $(this).find(".layui-tab-close").trigger('click');
                                        }
                                    });
                                    
                                    top_tab_ul.animate({
                                        left: top_tab_prev_with
                                    }, 200, 'swing');
                                    
                                    $('li[lay-id="'+id+'"]').trigger('click');
                                    
                                    break;
                                case 'lake-admin-contextmenu-close-all-page': //全部关闭
                                    top_nav.children('li').each(function () {
                                        if ($(this).find(".layui-tab-close").length > 0) {
                                            $(this).find(".layui-tab-close").trigger('click');
                                        }
                                    });
                                    
                                    top_tab_ul.animate({
                                        left: top_tab_prev_with
                                    }, 200, 'swing');
                                    
                                    $('li[lay-id="default"]').trigger('click');
                                    
                                    break;
                            }
                            
                            //处理完后移除右键菜单的dom
                            $context.remove();
                        });
                    });

                    $(document).on('click', function () {
                        $context.remove();
                    });
                }
                return false;
            });
            
        }
    };
    
    return lakeContextmenu;
});

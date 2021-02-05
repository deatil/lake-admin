/*!
 * lakeAdmin.js v1.0.2
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
layui.define([
    "element", 
    "layer", 
    "form", 
    "utils",
    "jquery", 
    "jqueryCookie", 
    "jqueryDragsort", 
    "lakeAdminMenu", 
    "lakeAdminSkin", 
    "lakeAdminTool", 
    "lakeAdminContextmenu", 
    'lakeAdminLockScreen',
    "lakeAdminMenuLeftZoom", 
    "lakeAdminFullscreen", 
], function(exports) {
    var $ = layui.jquery,
        layer = layui.layer,
        element = layui.element,
        form = layui.form,
        utils = layui.utils,
        lakeAdminMenu = layui.lakeAdminMenu,
        lakeAdminSkin = layui.lakeAdminSkin,
        lakeAdminTool = layui.lakeAdminTool,
        lakeAdminMenuLeftZoom = layui.lakeAdminMenuLeftZoom,
        lakeAdminContextmenu = layui.lakeAdminContextmenu;
    
    var lakeAdmin = {
        menus: lake_menus,
        nowTabMenuid: '', // 当前tab的ID
        openTabNum: 10, // 最大可打开窗口数量
        
        renderHtml: function() {
            $('#top_nav_menus').html(lakeAdminMenu.buildTop(lakeAdmin.menus));
            element.render(); //重新渲染
            
            lakeAdminTool.topMenuScroll();
            $(window).on('resize', function() {
                lakeAdminTool.topMenuScroll();
            })
            
            // iframe 加载事件
            var iframeDefault = document.getElementById('iframe_default');
            $(iframeDefault.contentWindow.document).ready(function() {
                $(iframeDefault).show();
            });
            
            this.listen();
            
            // 后台位在第一个导航
            $('#top_nav_menus li:first > a').trigger("click");
            
            // 监听主题
            lakeAdminSkin.listen();
            
            // 监听右键
            lakeAdminContextmenu.listen();
            
            // 左侧菜单放大
            lakeAdminMenuLeftZoom.listen('list');
            
            $('.admin-side-full').lakeAdminFullscreen();
            
            // 刷新打开当前页面
            if ($.cookie('lake-admin-menuid') != undefined) {
                var lake_admin_menuid = $.cookie('lake-admin-menuid');
                
                // 选择顶部菜单
                lakeAdminTool.topMenuClick(lake_admin_menuid, lakeAdmin.menus);
                
                // 点击左侧菜单
                $("#side_menus_bar a[lay-id="+lake_admin_menuid+"], .js-menu-nav a[lay-id="+lake_admin_menuid+"]").trigger('click');
            }
            
            // 拖拽排序
            $(".lake-admin-top-tab").dragsort({
                itemSelector: 'li.lake-admin-tab-item',
                dragSelector: ".item-dragsort,.js-dragsort",
                placeHolderTemplate: "<li class='lake-admin-tab-item'></li>",
                dragBetween: true,
                scrollSpeed: 5
            });
            
        },
        
        listen: function() {
            
            // 顶部导航点击
            $('#top_nav_menus').on('click', 'a', function(e) {
                // 取消事件的默认动作
                e.preventDefault();
                // 终止事件 不再派发事件
                e.stopPropagation();
                
                var data_id = $(this).attr('lay-id'),
                    menu_data_id = $(this).attr('data-id'),
                    data_list = lakeAdmin.menus[menu_data_id],
                    sideMenusBar = $('#side_menus_bar');

                if (sideMenusBar.attr('lay-id') == data_id) {
                    return false;
                };
                
                var index = $(this).parent().index();
                if (index > 0) {
                    sideMenusBar.addClass("lake-admin-module");
                } else {
                    sideMenusBar.removeClass("lake-admin-module");
                }

                // 显示左侧菜单
                var html = lakeAdminMenu.buildLeft(data_list['items']);
                sideMenusBar.html(html).attr('lay-id', data_id);
                element.render(); //重新渲染
                
                $(".lake-admin-module > li").removeClass("layui-nav-itemed");
                $('.lake-admin-module > li:first').addClass("layui-nav-itemed");
                
                // 左侧选择高亮
                var topmenu = lakeAdminMenu.getTopMenuByID(lakeAdmin.nowTabMenuid, lakeAdmin.menus);
                if (topmenu && topmenu.menuid == data_id) {
                    lakeAdminTool.selectLeftMenu(lakeAdmin.nowTabMenuid);
                }
            });

            // 模型左侧点击
            /*
            $(document).on('click', '.lake-admin-module > li > a', function() {
                $(this).parent()
                    .siblings('li')
                    .removeClass('layui-nav-itemed');
            });
            */

            // 左边菜单点击
            $(document).on('click', '#side_menus_bar a, .js-menu-nav a', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var $this = $(this),
                    _dt = $this.parent(),
                    _dl = $this.next('dl');
                    
                // 子菜单显示&隐藏
                if (_dl.length) {
                    return false;
                };
                
                var body_history_id = $(this).attr('lay-id');
                var body_history_li = $('#body_history li[lay-id=' + body_history_id + ']');
                if (body_history_li.length <= 0) {
                    if ($("#body_history li").length >= lakeAdmin.openTabNum) {
                        layer.msg('只能同时打开' + lakeAdmin.openTabNum + '个选项卡哦。不然系统会卡的！');
                        return;
                    }
                }

                // 父级高亮
                $("#side_menus_bar .layui-nav-item").removeClass("layui-nav-item-active");
                $(this).parents(".layui-nav-child")
                    .parent()
                    .addClass('layui-nav-item-active');
                    
                $("#side_menus_bar").hover(function() {
                    $(this).addClass("layui-nav-item-bar-hide");
                }, function() {
                    $(this).removeClass("layui-nav-item-bar-hide");
                });
                
                var dataId = $(this).attr('lay-id'),
                    icon = $(this).attr('lay-icon'),
                    title = $(this).attr('lay-title'),
                    href = this.href;
                
                lakeAdmin.nowTabMenuid = dataId;

                lakeAdminTool.iframeJudge({
                    elem: $this,
                    href: href,
                    id: dataId,
                    icon: icon,
                    title: title
                });

            });

            // 点击一个tab页
            $('#body_history').on('click focus', 'li', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var data_id = $(this).attr('lay-id');
                if (data_id) {
                    // 选择顶部菜单
                    lakeAdminTool.topMenuClick(data_id, lakeAdmin.menus);
                    
                    // 选择左边菜单
                    lakeAdminTool.selectLeftMenu(data_id);
                    
                    lakeAdmin.nowTabMenuid = data_id;
                }

                $(this).addClass('layui-this').siblings('li').removeClass('layui-this');
                
                try {
                    var menuid = data_id;
                    if (menuid) {
                        $.cookie('lake-admin-menuid', menuid, {
                            expires: 1,
                        });
                    }
                } catch (err) {}
                
                lakeAdminTool.showTabWidth($(this));
                
                $('#iframe_' + data_id).show().siblings('iframe').hide(); //隐藏其它iframe
            });

            // 关闭一个tab页
            $('#body_history').on('click', '.layui-tab-close', function(e) {
                e.stopPropagation();
                e.preventDefault();
                
                var li = $(this).parent(),
                    prev_li = li.prev('li'),
                    data_id = li.attr('lay-id');
                
                var topTabUl = $('#body_history');
                var topTabPrevWith = $('#layui_iframe_refresh').outerWidth(true) + $('#page-prev').outerWidth(true);
                
                li.hide(60, function() {
                    $(this).remove(); // 移除选项卡
                    $('#iframe_' + data_id).remove(); // 移除iframe页面
                    var current_li = $('#body_history li.layui-this');
                    // 找到关闭后当前应该显示的选项卡
                    current_li = current_li.length ? current_li : prev_li;
                    lakeAdminTool.showTab(current_li);
                    
                    if (topTabUl.find().length <= 2) {
                        topTabUl.animate({
                            left: topTabPrevWith
                        }, 200);
                    }
                });
            });

            // 上一个选项卡
            $('#page-prev').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                var ul = $('#body_history'),
                    current = ul.find('.layui-this'),
                    li = current.prev('li');
                lakeAdminTool.showTab(li);
            });

            // 下一个选项卡
            $('#page-next').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                var ul = $('#body_history'),
                    current = ul.find('.layui-this'),
                    li = current.next('li');
                lakeAdminTool.showTab(li);
            });
            
            // 刷新当前页
            $(document).on("click", ".lake-admin-refresh-page", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var index = layer.load();
                var id = $('#body_history .layui-this').attr('lay-id'),
                    iframe = $('#iframe_' + id);
                if (iframe[0].contentWindow) {
                    lakeAdminTool.reloadPage(iframe[0].contentWindow);
                    layer.close(index);
                }
                
                $(document).find('div.lake-admin-contextmenu').remove();
            });
            
            // 本页前进
            $(document).on("click", ".lake-admin-refresh-page-back", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var id = $('#body_history .layui-this').attr('lay-id'),
                    iframe = $('#iframe_' + id);
                if (iframe[0].contentWindow) {
                    iframe[0].contentWindow.history.go(-1);;
                }
                
                $(document).find('div.lake-admin-contextmenu').remove();
            });
            
            // 本页后退
            $(document).on("click", ".lake-admin-refresh-page-forward", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var id = $('#body_history .layui-this').attr('lay-id'),
                    iframe = $('#iframe_' + id);
                if (iframe[0].contentWindow) {
                    iframe[0].contentWindow.history.go(1);
                }
                
                $(document).find('div.lake-admin-contextmenu').remove();
            });

            // 关闭当前选项卡
            $(document).on("click", ".lake-admin-close-current-page", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if ($("#body_history li").length > 1) {
                    var current_li = $("#body_history li.layui-this");
                    
                    if (current_li.find(".layui-tab-close").length > 0) {
                        current_li.find(".layui-tab-close").trigger('click');
                    }
                } else {
                    layer.msg("没有可以关闭的窗口了");
                }

                $(document).find('div.lake-admin-contextmenu').remove();
            });

            // 关闭其他选项卡
            $(document).on("click", ".lake-admin-close-other-page", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if ($("#body_history li").length > 1) {
                    var this_data_id = $("#body_history li.layui-this").attr('lay-id');
                    $("#body_history li").each(function() {
                        if ($(this).attr('lay-id') == this_data_id) {
                            return;
                        }
                        
                        if ($(this).find(".layui-tab-close").length > 0) {
                            $(this).find(".layui-tab-close").trigger('click');
                        }
                    });
                } else {
                    layer.msg("没有可以关闭的窗口了");
                }

                $(document).find('div.lake-admin-contextmenu').remove();
            });

            // 关闭全部选项卡
            $(document).on("click", ".lake-admin-close-all-page", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if ($("#body_history li").length > 1) {
                    $("#body_history li").each(function() {
                        if ($(this).find(".layui-tab-close").length > 0) {
                            $(this).find(".layui-tab-close").trigger('click');
                        }
                    });

                } else {
                    layer.msg("没有可以关闭的窗口了");
                }
                
                $('li[lay-id="default"]').trigger('click');

                $(document).find('div.lake-admin-contextmenu').remove();
            });
            
            // 顶部鼠标移上显示
            var top_nav_layer_tips;
            $(document).on('mouseenter', ".lake-admin-top-tip", function() {
                var title = $(this).attr("lay-title");
                top_nav_layer_tips = layer.tips(title, this, {
                    tips: [1, '#009688'],
                });
            });
            $(document).on('mouseleave', ".lake-admin-top-tip", function() {
                layer.close(top_nav_layer_tips);
            });
            
            // 清除缓存
            $(document).on('click', ".js-lake-admin-clearcache dd a", function() {
                var url = $(this).closest('dl').data('url');
                var index = layer.msg('清除缓存中，请稍候', { 
                    icon: 16, 
                    time: false, 
                    shade: 0.8 
                });
                
                $.ajax({
                    url: url,
                    dataType: 'json',
                    method: 'POST',
                    data: { 
                        type: $(this).data("type") 
                    },
                    cache: false,
                    success: function(res) {
                        layer.close(index);
                        if (res.code == 1) {
                            layer.msg("缓存清除成功！");
                        }else{
                            layer.msg('清除缓存失败！');
                        }
                    },
                    error: function() {
                        layer.close(index);
                        layer.msg('清除缓存失败！');
                    }
                });
            });
            
            // 退出登陆
            $(document).on('click', '.js-lake-admin-logout', function (e) {
                // 取消事件的默认动作
                e.preventDefault();
                // 终止事件 不再派发事件
                e.stopPropagation();
                
                var url = $(this).attr('href');
                layer.confirm('您确定要退出登陆吗？', { 
                    icon: 3, 
                    title: '提示信息' 
                }, function(index) {
                    $.cookie('lake-admin-menuid', "", {expires: -1});
                    location.href = url;
                });
            });
            
            // 手机设备适配
            var treeMobile = $('.lake-admin-site-tree-mobile'),
                shadeMobile = $('.lake-admin-site-mobile-shade')
            treeMobile.on('click', function() {
                $('body').addClass('lake-admin-site-mobile');
                $('body').find('.layui-layout-admin-collapse').removeClass('layui-layout-admin-collapse');
            });
            shadeMobile.on('click', function() {
                $('body').removeClass('lake-admin-site-mobile');
            });
            
        }
    }
    
    // 构建页面
    lakeAdmin.renderHtml();
    
    exports('lakeAdmin', {});
})

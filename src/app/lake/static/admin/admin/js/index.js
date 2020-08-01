layui.define(['element', 'layer', 'form', 'jquery', 'jquery_cookie', "jquery_dragsort", "utils"], function(exports) {
    var $ = layui.jquery,
        element = layui.element,
        form = layui.form,
        utils = layui.utils,
        layer = layui.layer;
    
    // 生成菜单
    var menuMake = {
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
                    '<li class="layui-nav-item layui-nav-itemed">'
                        + '<a href="' + url + '" class="lay-tip-title" lay-id="' + data.menuid + '" data-id="' + data.id + '" lay-icon="iconfont ' + data.icon + '" lay-title="' + data.title + '">'
                            + menu_icon
                            + '<span class="layui-nav-title"><b>' + data.title + '</b></span>'
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
                        + '<span class="layui-nav-title">' + data.title + '</span>'
                    + '</a>'
                    + menu_child_html
                + '</dd>';
            });
            
            var menu_html = '<dl class="layui-nav-child">' 
                + menu_dd_html 
                + '</dl>';
            
            return menu_html;
        },
        // 选择左边菜单
        selectLeftMenu: function(data_id) {
            // 选择左边菜单
            $("#side_menus_bar").find(".layui-this").removeClass('layui-this');
            $("#side_menus_bar").find("a[lay-id=" + data_id + "]").parent().addClass('layui-this');
            
            if ($("#side_menus_bar.lake-admin-module").find("a[lay-id=" + data_id + "]").length > 0) {
                $("#side_menus_bar").find("a[lay-id=" + data_id + "]")
                    .parent().parent().parent()
                    .addClass('layui-nav-itemed')
                    .siblings().removeClass('layui-nav-itemed');
            }
        }
    };
    
    var lake = {
        config: {
            menus: lake_menus,
            nowTabMenuid: '', // 当前tab的ID
            openTabNum: 10, // 最大可打开窗口数量
        },
        
        renderHtml: function() {
            $('#top_nav_menus').html(menuMake.buildTop(this.config.menus));
            element.render(); //重新渲染
            
            // iframe 加载事件
            var iframeDefault = document.getElementById('iframe_default');
            $(iframeDefault.contentWindow.document).ready(function() {
                $(iframeDefault).show();
            });
            
            // 拖拽排序
            $(".lake-admin-top-tab").dragsort({
                itemSelector: 'li.lake-admin-tab-item',
                dragSelector: ".item-dragsort,.js-dragsort",
                placeHolderTemplate: "<li class='lake-admin-tab-item'></li>",
                scrollSpeed: 5
            });
        },
        
        // 重新刷新页面，使用location.reload()有可能导致重新提交
        reloadPage: function(win) {
            var location = win.location;
            location.href = location.pathname + location.search;
        },
        
        // 通过菜单id查找菜单配置对象
        getMenuByID: function(mid, menugroup) {
            var thiz = this;
            var ret = {};
            if (!menugroup) {
                menugroup = thiz.config.menus;
            }
            if (!mid) {
                ret = menugroup['default'];
            } else {
                $.each(menugroup, function(i, o) {
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

        getTopMenuByID: function(mid) {
            var ret = {};
            var menu = this.getMenuByID(mid);
            if (menu) {
                if (menu.parent) {
                    var tmp = this.getTopMenuByID(menu.parent);
                    if (tmp && tmp.menuid) {
                        ret = tmp;
                    }
                } else {
                    ret = menu;
                }
            }
            return ret;
        },
        
        topMenuClick: function(curid) {
            if (curid == "default") {
                var objtopmenu = $('#top_nav_menus li:first-child').find("a");
            } else {
                var topmenu = this.getTopMenuByID(curid);
                var objtopmenu = $('#top_nav_menus').find("a[lay-id=" + topmenu.menuid + "]");
            }
            
            if (objtopmenu.parent().attr("class") != "layui-this") {
                //选中当前顶部菜单
                objtopmenu.parent().addClass('layui-this').siblings().removeClass('layui-this');
                //触发事件
                objtopmenu.click();
            }
        },

        // 判断显示或创建iframe
        iframeJudge: function(options) {
            var thiz = this;
            var elem = options.elem,
                href = options.href,
                id = options.id,
                li = $('#body_history li[lay-id=' + id + ']');
                
            // 如果iframe标签是已经存在的，则显示并让选项卡高亮,并不显示loading
            if (li.length > 0) {
                var iframe = $('#iframe_' + id);
                setTimeout(function() {
                    $('#loading').hide();
                }, 500);
                li.addClass('current');
                if (iframe[0].contentWindow && iframe[0].contentWindow.location.href !== href) {
                    iframe[0].contentWindow.location.href = href;
                }
                $('#body_frame iframe').hide();
                $('#iframe_' + id).show();
                thiz.showTab(li); //计算此tab的位置，如果不在屏幕内，则移动导航位置
            } else {
                // 创建一个并加以标识
                var iframeAttr = {
                    src: href,
                    id: 'iframe_' + id,
                    frameborder: '0',
                    scrolling: 'auto',
                    height: '100%',
                    width: '100%'
                };
                var iframe = $('<iframe/>').prop(iframeAttr).appendTo('#body_frame .layui-tab-content .lake-admin-iframe-box');

                $(iframe[0].contentWindow.document).ready(function() {
                    $('#body_frame iframe').hide();
                    setTimeout(function() {
                        $('#loading').hide();
                    }, 500);
                    
                    if (options.icon) {
                        var icon = '<i class="item-dragsort ' + options.icon + '"></i>&nbsp;';
                    } else {
                        var icon = '';
                    }
                    
                    var title = icon + '<span class="layui-nav-title">' + options.title + '</span>';
                    var li = $('<li class="layui-tab-item lake-admin-tab-item">' + title + '<i class="layui-icon layui-unselect layui-tab-close">&#x1006;</i></li>').attr('lay-id', id);
                    li.appendTo('#body_history');
                    thiz.showTab(li); //计算此tab的位置，如果不在屏幕内，则移动导航位置
                });
            }
        },

        // 顶部导航时位置判断
        showTabWidth: function(li) {
            if (li.length) {
                var ul = $('#body_history'),
                    li_offset = li.offset(),
                    li_width = li.outerWidth(true),
                    next_left = $('#page-next').offset().left, //右边按钮的界限位置
                    prev_right = $('#page-prev').offset().left + $('#page-prev').outerWidth(true); //左边按钮的界限位置
                if (li_offset.left + li_width > next_left) { //如果将要移动的元素在不可见的右边，则需要移动
                    var distance = li_offset.left + li_width - next_left; //计算当前父元素的右边距离，算出右移多少像素
                    ul.animate({
                        left: '-=' + distance
                    }, 200, 'swing');
                } else if (li_offset.left < prev_right) { //如果将要移动的元素在不可见的左边，则需要移动
                    var distance = prev_right - li_offset.left; //计算当前父元素的左边距离，算出左移多少像素
                    ul.animate({
                        left: '+=' + distance
                    }, 200, 'swing');
                }
            }
        },

        // 显示顶部导航时作位置判断，点击左边菜单、上一tab、下一tab时公用
        showTab: function(li) {
            if (li.length > 0) {
                li.trigger('click');
            }
        }
        
    };
    
    // 构建页面
    lake.renderHtml();
    
    $('.admin-side-full').on('click', function () {
        if (localStorage.full == 0) {
            localStorage.full=1;
            var docElm = document.documentElement;
            //W3C
            if (docElm.requestFullscreen) {
                docElm.requestFullscreen();
            }
            //FireFox
            else if (docElm.mozRequestFullScreen) {
                docElm.mozRequestFullScreen();
            }
            //Chrome等
            else if (docElm.webkitRequestFullScreen) {
                docElm.webkitRequestFullScreen();
            }
            //IE11
            else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
            
            $(this).removeClass('icon-fullscreen')
                .addClass('icon-narrow');
            layer.msg('按Esc即可退出全屏');
        } else {
            localStorage.full=0;
            if(document.exitFullscreen) {
                document.exitFullscreen();
            } else if(document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if(document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
            
            $(this).removeClass('icon-narrow')
                .addClass('icon-fullscreen');
        }
    });

    // 顶部导航点击
    $('#top_nav_menus').on('click', 'a', function(e) {
        // 取消事件的默认动作
        e.preventDefault();
        // 终止事件 不再派发事件
        e.stopPropagation();
        
        $(this).parent().addClass('current')
            .siblings().removeClass('current');
        var data_id = $(this).attr('lay-id'),
            menu_data_id = $(this).attr('data-id'),
            data_list = lake.config.menus[menu_data_id],
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
        var html = menuMake.buildLeft(data_list['items']);
        sideMenusBar.html(html).attr('lay-id', data_id);
        element.render(); //重新渲染
        
        $(".lake-admin-module > li").removeClass("layui-nav-itemed");
        $('.lake-admin-module > li:first').addClass("layui-nav-itemed");
        
        // 左侧选择高亮
        var topmenu = lake.getTopMenuByID(lake.config.nowTabMenuid);
        if (topmenu && topmenu.menuid == data_id) {
            menuMake.selectLeftMenu(lake.config.nowTabMenuid);
        }
    });

    // 后台位在第一个导航
    $('#top_nav_menus li:first > a').trigger("click");

    // 模型左侧点击
    $(document).on('click', '.lake-admin-module > li > a', function() {
        $(this).parent()
            .siblings('li')
            .removeClass('layui-nav-itemed');
    });

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
            if ($("#body_history li").length >= lake.config.openTabNum) {
                layer.msg('只能同时打开' + lake.config.openTabNum + '个选项卡哦。不然系统会卡的！');
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
        
        var data_id = $(this).attr('lay-id'),
            icon = $(this).attr('lay-icon'),
            title = $(this).attr('lay-title'),
            li = $('#body_history li[lay-id=' + data_id + ']');
        var href = this.href;
        
        lake.config.nowTabMenuid = data_id;

        lake.iframeJudge({
            elem: $this,
            href: href,
            id: data_id,
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
            lake.topMenuClick(data_id);
            
            // 选择左边菜单
            $("#side_menus_bar").find(".layui-this").removeClass('layui-this');
            $("#side_menus_bar").find("a[lay-id=" + data_id + "]").parent().addClass('layui-this');
            
            if ($("#side_menus_bar.lake-admin-module").find("a[lay-id=" + data_id + "]").length > 0) {
                $("#side_menus_bar").find("a[lay-id=" + data_id + "]")
                    .parent().parent().parent()
                    .addClass('layui-nav-itemed')
                    .siblings().removeClass('layui-nav-itemed');
            }
            
            lake.config.nowTabMenuid = data_id;
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
        
        lake.showTabWidth($(this));
        
        $('#iframe_' + data_id).show().siblings('iframe').hide(); //隐藏其它iframe
    });

    // 关闭一个tab页
    $('#body_history').on('click', '.layui-tab-close', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var li = $(this).parent(),
            prev_li = li.prev('li'),
            data_id = li.attr('lay-id');
        li.hide(60, function() {
            $(this).remove(); // 移除选项卡
            $('#iframe_' + data_id).remove(); // 移除iframe页面
            var current_li = $('#body_history li.layui-this');
            // 找到关闭后当前应该显示的选项卡
            current_li = current_li.length ? current_li : prev_li;
            
            lake.showTab(current_li);
        });
    });

    // 上一个选项卡
    $('#page-prev').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        var ul = $('#body_history'),
            current = ul.find('.layui-this'),
            li = current.prev('li');
        lake.showTab(li);
    });

    // 下一个选项卡
    $('#page-next').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        var ul = $('#body_history'),
            current = ul.find('.layui-this'),
            li = current.next('li');
        lake.showTab(li);
    });
    
    // 刷新打开当前页面
    if ($.cookie('lake-admin-menuid') != undefined) {
        var lake_admin_menuid = $.cookie('lake-admin-menuid');
        
        // 选择顶部菜单
        lake.topMenuClick(lake_admin_menuid);
        
        // 点击左侧菜单
        $("#side_menus_bar a[lay-id="+lake_admin_menuid+"], .js-menu-nav a[lay-id="+lake_admin_menuid+"]").trigger('click');
    }

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
                                lake.reloadPage(iframe[0].contentWindow);
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
    
    // 刷新当前页
    $(document).on("click", ".lake-admin-refresh-page", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var index = layer.load();
        var id = $('#body_history .layui-this').attr('lay-id'),
            iframe = $('#iframe_' + id);
        if (iframe[0].contentWindow) {
            lake.reloadPage(iframe[0].contentWindow);
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
        
        var top_tab_ul = $('#body_history');
        var top_tab_prev_with = $('#layui_iframe_refresh').outerWidth(true) + $('#page-prev').outerWidth(true);
        
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
        
        top_tab_ul.animate({
            left: top_tab_prev_with
        }, 200, 'swing');

        $(document).find('div.lake-admin-contextmenu').remove();
    });

    // 关闭全部选项卡
    $(document).on("click", ".lake-admin-close-all-page", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var top_tab_ul = $('#body_history');
        var top_tab_prev_with = $('#layui_iframe_refresh').outerWidth(true) + $('#page-prev').outerWidth(true);
        
        if ($("#body_history li").length > 1) {
            $("#body_history li").each(function() {
                if ($(this).find(".layui-tab-close").length > 0) {
                    $(this).find(".layui-tab-close").trigger('click');
                }
            });

        } else {
            layer.msg("没有可以关闭的窗口了");
        }
        
        top_tab_ul.animate({
            left: top_tab_prev_with
        }, 200, 'swing');
        
        $('li[lay-id="default"]').trigger('click');

        $(document).find('div.lake-admin-contextmenu').remove();
    });

    // 隐藏左侧导航
    $(document).on('click', ".admin-menu-toggle", function() {
        if ($(".layui-layout-admin").hasClass("layui-layout-admin-collapse")) {
            $(".layui-layout-admin").removeClass("layui-layout-admin-collapse");
            $.cookie('admin-collapse', null);
        } else {
            $(".layui-layout-admin").addClass("layui-layout-admin-collapse");
            $.cookie('admin-collapse', 'collapse', {
                expires: 1,
            });
        }
    });
    
    // 设置默认状态
    if ($.cookie('admin-collapse') == 'collapse') {
        $(".layui-layout-admin").addClass("layui-layout-admin-collapse");
    }
    
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
    
    // 左侧导航标题
    var left_nav_layer_tips;
    $(document).on('mouseenter', ".layui-layout-admin-collapse .lay-tip-title", function() {
        var title = $(this).attr("lay-title");
        left_nav_layer_tips = layer.tips(title, this, {
            tips: [2, '#009688'],
        });
    });
    $(document).on('mouseleave', ".layui-layout-admin-collapse .lay-tip-title", function() {
        layer.close(left_nav_layer_tips);
    });
    
    // 清除缓存
    $(document).on('click', ".js-lake-admin-clearcache dd a", function() {
        $.ajax({
            url: clear_cache_url,
            dataType: 'json',
            data: { 
                type: $(this).data("type") 
            },
            cache: false,
            success: function(res) {
                if (res.code == 1) {
                    var index = layer.msg('清除缓存中，请稍候', { icon: 16, time: false, shade: 0.8 });
                    setTimeout(function() {
                        layer.close(index);
                        layer.msg("缓存清除成功！");
                    }, 1000);
                }else{
                    layer.msg('清除缓存失败');
                }
            },
            error: function() {
                layer.msg('清除缓存失败');
            }
        });
    });
    
    // 监听顶部右侧皮肤
    $(document).on('click', '.lake-admin-skin dd a', function (elem) {
        // 修改skin
        if ($(this).parent('dd').attr('data-skin')) {
            $.cookie('lake-admin-skin', $(this).parent('dd').attr('data-skin'), {
                expires: 10,
            });
            skin();
        }
    });
    
    // 皮肤
    function skin() {
        var arr = $.cookie('lake-admin-skin');
        var skin = (arr != null) ? arr : "black";
        var body = $('body');
        body.removeClass('lake-admin-skin-black');
        body.removeClass('lake-admin-skin-white');
        body.removeClass('lake-admin-skin-blue');
        body.addClass('lake-admin-skin-' + skin);
        
        $(".lake-admin-skin dd")
            .removeClass("lake-admin-skin-active");
        $(".lake-admin-skin dd[data-skin="+skin+"]")
            .addClass("lake-admin-skin-active");
    }
    skin();
    
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
            $.cookie('lake-admin-menuid', '', {
                expires: 0,
            });
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

    exports('index', {});
})

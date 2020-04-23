layui.define(['element', 'layer', 'jquery', 'jquery_cookie'], function(exports) {
    var $ = layui.jquery,
        element = layui.element,
        layer = layui.layer;
    
    // 添加cookie
    $ = layui.jquery_cookie($);

    var menus = SUBMENU_CONFIG;
    var nowTabMenuid = ''; // 当前tab的ID
    var openTabNum = 10; // 最大可打开窗口数量
    
    // iframe 加载事件
    var iframe_default = document.getElementById('iframe_default');
    var def_iframe_height = 0;
    $(iframe_default.contentWindow.document).ready(function() {
        $(iframe_default).show();
    });
    var html = [];
    $.each(menus, function(i, o) {
        if ((typeof o.items) != 'undefined') {
            html.push('<li class="layui-nav-item"><a href="javascript:;" title="' + o.title + '" lay-id="' + o.menuid + '" data-id="' + o.id + '"><i class="iconfont ' + o.icon + '"></i>&nbsp;<span class="layui-nav-title">' + o.title + '</span></a></li>');
        }
    });
    $('#top_nav_menus').html(html.join(''));
    element.render(); //重新渲染
    
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
            data_list = menus[menu_data_id],
            html = [],
            child_html = [],
            child_index = 0,
            side_menus_bar = $('#side_menus_bar');

        if (side_menus_bar.attr('lay-id') == data_id) {
            return false;
        };
        
        var index = $(this).parent().index();
        if (index > 0) {
            side_menus_bar.addClass("lake-admin-module");
        } else {
            side_menus_bar.removeClass("lake-admin-module");
        }

        // 显示左侧菜单
        show_left_menu(data_list['items']);
        side_menus_bar.html(html.join('')).attr('lay-id', data_id);
        element.render(); //重新渲染
        
        $(".lake-admin-module > li").removeClass("layui-nav-itemed");
        $('.lake-admin-module > li:first').addClass("layui-nav-itemed");
        
        // 左侧选择高亮
        var topmenu = getTopMenuByID(nowTabMenuid);
        if (topmenu && topmenu.menuid == data_id) {
            selectLeftMenu(nowTabMenuid);
        }

        // 显示左侧菜单
        function show_left_menu(data) {
            for (var attr in data) {
                if (data[attr] && typeof(data[attr]) === 'object') {
                    //循环子对象
                    if (!data[attr].url && attr === 'items') {
                        // 子菜单添加识别属性
                        $.each(data[attr], function(i, o) {
                            child_index++;
                            o.isChild = true;
                            o.child_index = child_index;
                        });
                    }
                    show_left_menu(data[attr]); // 继续执行循环(筛选子菜单)
                } else {
                    if (attr === 'title') {
                        data.url = data.url ? data.url : '#';
                        if (!(data['isChild'])) {
                            // 一级菜单
                            html.push('<li class="layui-nav-item layui-nav-itemed"><a href="' + data.url + '" class="lay-tip-title" lay-id="' + data.menuid + '" data-id="' + data.id + '" lay-icon="iconfont ' + data.icon + '" lay-title="' + data.title + '"><i class="iconfont ' + data.icon + '"></i>&nbsp;<span class="layui-nav-title"><b>' + data.title + '</b></span></a>');
                        } else {
                            // 二级菜单
                            child_html.push('<dd><a href="' + data.url + '" class="lay-tip-title" lay-id="' + data.menuid + '" data-id="' + data.id + '" lay-icon="iconfont ' + data.icon + '" lay-title="' + data.title + '"><i class="iconfont ' + data.icon + '"></i>&nbsp;<span class="layui-nav-title">' + data.title + '</span></a></dd>');
                            // 二级菜单全部push完毕
                            if (data.child_index == child_index) {
                                html.push('<dl class="layui-nav-child">' + child_html.join('') + '</dl></li>');
                                child_html = [];
                            }
                        }
                    }
                }
            }
        };
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
            
        //子菜单显示&隐藏
        if (_dl.length) {
            return false;
        };
        
        var body_history_id = $(this).attr('lay-id');
        var body_history_li = $('#body_history li[lay-id=' + body_history_id + ']');
        if (body_history_li.length <= 0) {
            if ($("#body_history li").length >= openTabNum) {
                layer.msg('只能同时打开' + openTabNum + '个选项卡哦。不然系统会卡的！');
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
        
        nowTabMenuid = data_id;

        iframeJudge({
            elem: $this,
            href: href,
            id: data_id,
            icon: icon,
            title: title
        });

    });

    // 判断显示或创建iframe
    function iframeJudge(options) {
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
            showTab(li); //计算此tab的位置，如果不在屏幕内，则移动导航位置
        } else {
            //创建一个并加以标识
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
                var tpl = '<i class="' + options.icon + '"></i>&nbsp;<span class="layui-nav-title">' + options.title + '</span>';
                var li = $('<li>' + tpl + '<i class="layui-icon layui-unselect layui-tab-close">&#x1006;</i></li>').attr('lay-id', id);
                li.appendTo('#body_history');
                showTab(li); //计算此tab的位置，如果不在屏幕内，则移动导航位置
            });
        }
    }

    // 点击一个tab页
    $('#body_history').on('click focus', 'li', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var data_id = $(this).attr('lay-id');
        if (data_id) {
            // 选择顶部菜单
            var curid = data_id;
            if (curid == "default") curid = "default";
            var topmenu = getTopMenuByID(curid);
            var objtopmenu = $('#top_nav_menus').find("a[lay-id=" + topmenu.menuid + "]");
            if (objtopmenu.parent().attr("class") != "layui-this") {
                //选中当前顶部菜单
                objtopmenu.parent().addClass('layui-this').siblings().removeClass('layui-this');
                //触发事件
                objtopmenu.click();
            }

            // 选择左边菜单
            $("#side_menus_bar").find(".layui-this").removeClass('layui-this');
            $("#side_menus_bar").find("a[lay-id=" + data_id + "]").parent().addClass('layui-this');
            
            if ($("#side_menus_bar.lake-admin-module").find("a[lay-id=" + data_id + "]").length > 0) {				
                $("#side_menus_bar").find("a[lay-id=" + data_id + "]")
                    .parent().parent().parent()
                    .addClass('layui-nav-itemed')
                    .siblings().removeClass('layui-nav-itemed');
            }
            
            nowTabMenuid = data_id;
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
        
        showTabWidth($(this));
        
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
            
            showTab(current_li);
            
            try {
                var menuid = cur_data_id;
                if (menuid) {
                    $.cookie('lake-admin-menuid', menuid, {
                        expires: 1,
                    });
                }
            } catch (err) {}
        });
    });

    // 上一个选项卡
    $('#page-prev').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        var ul = $('#body_history'),
            current = ul.find('.layui-this'),
            li = current.prev('li');
        showTab(li);
    });

    // 下一个选项卡
    $('#page-next').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        var ul = $('#body_history'),
            current = ul.find('.layui-this'),
            li = current.next('li');
        showTab(li);
    });

    // 顶部导航时位置判断
    function showTabWidth(li) {
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
    }

    // 显示顶部导航时作位置判断，点击左边菜单、上一tab、下一tab时公用
    function showTab(li) {
        if (li.length) {
            li.trigger('click');
        }
    }
    
    // 选择左边菜单
    function selectLeftMenu(data_id) {
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
    
    // 刷新打开当前页面
    if ($.cookie('lake-admin-menuid') != undefined) {
        var lake_admin_menuid = $.cookie('lake-admin-menuid');
        
        // 选择顶部菜单
        var curid = lake_admin_menuid;
        if (curid == "default") curid = "default";
        var topmenu = getTopMenuByID(curid);
        var objtopmenu = $('#top_nav_menus').find("a[lay-id=" + topmenu.menuid + "]");
        if (objtopmenu.parent().attr("class") != "layui-this") {
            //选中当前顶部菜单
            objtopmenu.parent().addClass('layui-this').siblings().removeClass('layui-this');
            //触发事件
            objtopmenu.click();
        }		
        
        // 点击左侧菜单
        $("#side_menus_bar a[lay-id="+lake_admin_menuid+"], .js-menu-nav a[lay-id="+lake_admin_menuid+"]").trigger('click');
        
        nowTabMenuid = lake_admin_menuid;
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
                                reloadPage(iframe[0].contentWindow);
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
            reloadPage(iframe[0].contentWindow);
            layer.close(index);
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

    // 通过菜单id查找菜单配置对象
    function getMenuByID(mid, menugroup) {
        var ret = {};
        if (!menugroup) menugroup = menus;
        if (!mid) {
            ret = menugroup['default'];
        } else {
            $.each(menugroup, function(i, o) {
                if (o.menuid && o.menuid == mid) {
                    ret = o;
                    return false
                } else if (o.items) {
                    var tmp = getMenuByID(mid, o.items);
                    if (tmp.menuid && mid == tmp.menuid) {
                        ret = tmp;
                        return false
                    }
                }
            });
        }
        return ret;
    }

    function getTopMenuByID(mid) {
        var ret = {};
        var menu = getMenuByID(mid);
        if (menu) {
            if (menu.parent) {
                var tmp = getTopMenuByID(menu.parent);
                if (tmp && tmp.menuid) {
                    ret = tmp;
                }
            } else {
                ret = menu;
            }
        }
        return ret;
    }

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

    // 重新刷新页面，使用location.reload()有可能导致重新提交
    function reloadPage(win) {
        var location = win.location;
        location.href = location.pathname + location.search;
    }

    // 用于维持在线
    function online() {}
    
    //维持在线
    /*setInterval(function() {
        online();
    }, 60000);*/
    
    // 清除缓存
    $(document).on('click', "dl#deletecache dd a", function() {
        $.ajax({
            url: clear_cache_url,
            dataType: 'json',
            data: { type: $(this).data("type") },
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

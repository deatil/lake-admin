function getProjectUrl() {
    var layuiDir = layui.cache.base;
    if (!layuiDir) {
        var js = document.scripts, last = js.length - 1, src;
        for (var i = last; i > 0; i--) {
            if (js[i].readyState === 'interactive') {
                src = js[i].src;
                break;
            }
        }
        var jsPath = src || js[last].src;
        layuiDir = jsPath.substring(0, jsPath.lastIndexOf('/') + 1);
    }
    return layuiDir;
}

layui.define(['element', 'layer', 'form', 'jquery', 'jqueryCookie', "md5", "utils"], function(exports) {
    var $ = layui.jquery,
        element = layui.element,
        form = layui.form,
        utils = layui.utils,
        md5 = layui.md5,
        layer = layui.layer;
        
    var lakeSkinRootPath = getProjectUrl() + 'lock-screen/';
    var userAvatar = $(".lake-admin-user-avatar").attr("src");
    var userNickname = $(".lake-admin-user-avatar").attr("alt");
    
    var html = '<!-- 锁屏 -->\
<link rel="stylesheet" href="' + lakeSkinRootPath + 'lock-screen.css">\
<script src="' + lakeSkinRootPath + 'snowflake.js"></script>\
<div class="lock-screen" style="display:none;">\
    <div class="lock-bg">\
        <img class="lock-gradual active" src="' + lakeSkinRootPath + 'wallpaper/100001.jpg" alt=""/>\
        <img class="lock-gradual" src="' + lakeSkinRootPath + 'wallpaper/100002.jpg" alt=""/>\
        <img class="lock-gradual" src="' + lakeSkinRootPath + 'wallpaper/100003.jpg" alt=""/>\
        <img class="lock-gradual" src="' + lakeSkinRootPath + 'wallpaper/100004.jpg" alt=""/>\
        <img class="lock-gradual" src="' + lakeSkinRootPath + 'wallpaper/100005.jpg" alt=""/>\
        <img class="lock-gradual" src="' + lakeSkinRootPath + 'wallpaper/100006.jpg" alt=""/>\
        <img class="lock-gradual" src="' + lakeSkinRootPath + 'wallpaper/100007.jpg" alt=""/>\
        <img class="lock-gradual" src="' + lakeSkinRootPath + 'wallpaper/100008.jpg" alt=""/>\
    </div>\
    <div class="lock-content">\
        <!--雪花-->\
        <div class="snowflake">\
            <canvas id="snowflake"></canvas>\
        </div>\
        <!--雪花 END-->\
        <div class="time">\
            <div>\
                <div class="hhmmss"></div>\
                <div class="yyyymmdd"></div>\
            </div>\
        </div>\
        <div class="quit" id="lockQuit">\
            <i class="layui-icon layui-icon-logout" title="退出登录"></i>\
        </div>\
        <table class="unlock">\
            <tr>\
                <td>\
                    <div class="layui-form lock-form">\
                        <div class="lock-head">\
                            <img src="'+userAvatar+'" alt="'+userNickname+'"/>\
                        </div>\
                        <div class="layui-form-item">\
                            <div class="layui-col-xs8 layui-col-sm8 layui-col-md8">\
                                <input type="password" required lay-verify="required" id="lockPassword" name="lock_password" style="border-radius: 0;border:0;height: 44px" placeholder="请输入登录密码" autocomplete="off"\
                                       class="layui-input"/>\
                            </div>\
                            <div class="layui-col-xs4 layui-col-sm4 layui-col-md4">\
                                <button style="width: 100%;box-sizing:border-box;border-radius: 0;" type="button" lay-submit lay-filter="lockSubmit"\
                                        class="layui-btn lock-btn layui-btn-lg layui-btn-normal">确定\
                                </button>\
                            </div>\
                        </div>\
                    </div>\
                </td>\
            </tr>\
        </table>\
    </div>\
</div>\
';

    $('body').append(html);
    
    // 锁定账号
    var lock_inter = "";
    var menuid = "";
    lockShowInit(utils);
    $(".js-lake-admin-lock").on('click', function() {
        layer.confirm("确定要锁定屏幕吗？", function(index) {
            var lock_url = $('.lake-admin-lock').data('lock-url');
            $.post(lock_url, {}, function (res) {
                if (res.code == 1) {
                    layer.close(index);
                    utils.local("isLock", '1');//设置锁屏缓存防止刷新失效
                    lockShowInit(utils);//锁屏
                    
                    menuid = $.cookie('lake-admin-menuid');
                    $.cookie('lake-admin-menuid', "", {expires: -1});
                } else {
                    layer.alert(res.msg);
                }
            });
        });
    });

    // 锁屏方法
    function lockShowInit(utils) {
        let localLock = utils.local("isLock");
        $("#lockPassword").val("");
        if(!localLock){
            return;
        }

        $(".lock-screen").show();
        Snowflake("snowflake"); // 雪花

        var lock_bgs = $(".lock-screen .lock-bg img");
        $(".lock-content .time .hhmmss").html(utils.dateFormat("", "hh <p lock='lock'>:</p> mm"));
        $(".lock-content .time .yyyymmdd").html(utils.dateFormat("", "yyyy 年 M 月 dd 日"));

        var i = 0, k = 0;
        lock_inter = setInterval(function () {
            i++;
            if (i % 8 == 0) {
                k = k + 1 >= lock_bgs.length ? 0 : k + 1;
                i = 0;
                lock_bgs.removeClass("active");
                $(lock_bgs[k]).addClass("active");
            }
            $(".lock-content .time .hhmmss").html(utils.dateFormat("", "hh <p lock='lock'>:</p> mm"));
        }, 1000);
    }

    //提交密码
    form.on('submit(lockSubmit)', function(data) {
        var unlock_url = $('.lake-admin-lock').data('unlock-url');
        var password = data.field.lock_password;
        $.post(unlock_url, {
            password: md5(password)
        }, function (res) {
            layer.msg(res.msg, {
                time:1500,
                anim: 6,
                zIndex: 999999991
            }, function () {
                if (res.code==1){
                    utils.local("isLock", null);   //清除锁屏的缓存
                    $("#lockPassword").val("");   //清除输入框的密码
                    $(".lock-screen").hide();
                    clearInterval(lock_inter);
                    
                    $.cookie('lake-admin-menuid', menuid, {
                        expires: 1,
                    });
                } else {
                    layer.alert("解锁屏幕失败！");
                }
            });
        });
        return false;
    });

    //退出登录
    $("#lockQuit").on('click', function() {
        var logout_url = $('.lake-admin-lock').data('logout-url');
        window.location.replace(logout_url);
    });
    
    exports('lakeAdminLockScreen', {});
})

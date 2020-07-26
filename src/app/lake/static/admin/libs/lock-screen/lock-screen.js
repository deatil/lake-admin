layui.define(['element', 'layer', 'form', 'jquery', 'jquery_cookie', "utils"], function(exports) {
    var $ = layui.jquery,
        element = layui.element,
        form = layui.form,
        utils = layui.utils,
        layer = layui.layer;

    // 锁定账户
    var lock_inter = "";
    lockShowInit(utils);
    $(".js-lake-admin-lock").on('click', function() {
        layer.confirm("确定要锁定账户吗？", function(index) {
            var lock_url = $('.lake-admin-lock').attr('data-lock');
            $.post(lock_url, {}, function (res) {
                if (res.code == 1) {
                    layer.close(index);
                    utils.local("isLock", '1');//设置锁屏缓存防止刷新失效
                    lockShowInit(utils);//锁屏
                    
                    $.cookie('lake-admin-menuid', '', {
                        expires: 0,
                    });
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
        var unlock_url = $('.lake-admin-lock').attr('data-unlock');
        var password = data.field.lock_password;
        $.post(unlock_url, {
            password: hex_md5(password)
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
                } else {
                    layer.alert("解锁账户失败！");
                }
            });
        });
        return false;
    });

    //退出登录
    $("#lockQuit").on('click', function() {
        var logout_url = $('.lake-admin-lock').attr('data-logout');
        window.location.replace(logout_url);
    });
    
    exports('lock_screen', {});
})

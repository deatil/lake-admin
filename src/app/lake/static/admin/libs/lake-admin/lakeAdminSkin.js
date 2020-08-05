/*!
 * lakeAdminSkin.js v1.0.2
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
!(function(a){
    layui.define(['jquery', 'jqueryCookie'], function (exports) {
        var jquery = layui.$;
        
        exports('lakeAdminSkin', a(jquery));
    });
})(function($) {
    
    // 主题
    var lakeAdminSkin = {
        // 皮肤
        change: function() {
            var arr = $.cookie('lake-admin-skin');
            var skin = (arr != null) ? arr : "black";
            var body = $('body');
            $(".lake-admin-skin").removeClass(function (index, className) { 
                return (className.match(/(^|\s)lake-admin-skin-\S+/g) || []).join(' ');
            });
            body.addClass('lake-admin-skin-' + skin);
            
            $(".lake-admin-skin dd")
                .removeClass("lake-admin-skin-active");
            $(".lake-admin-skin dd[data-skin="+skin+"]")
                .addClass("lake-admin-skin-active");
        },
        
        listen: function() {
            var thiz = this;
            this.change();
            
            // 监听顶部右侧皮肤
            $(document).on('click', '.lake-admin-skin dd a', function (elem) {
                // 修改skin
                if ($(this).parent('dd').attr('data-skin')) {
                    $.cookie('lake-admin-skin', $(this).parent('dd').attr('data-skin'), {
                        expires: 10,
                    });
                    
                    thiz.change();
                }
            });
        }
    };
    
    return lakeAdminSkin;
});

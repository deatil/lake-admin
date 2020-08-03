/*!
 * lakeFullscreen.js v1.0.2
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
!(function(a){
    layui.define(['jquery'], function (exports) {
        var jquery = layui.$,
            layer = layui.layer;
        
        a(jquery, layer);
        
        exports('lakeFullscreen', {});
    });
})(function($, layer) {
    
    // 全屏
    $.fn.lakeFullscreen = function() {
        var fullScreen = {
            // 全屏
            full: function() {
                var docElm = document.documentElement;
                var rfs = docElm.requestFullScreen || docElm.webkitRequestFullScreen;
                
                if (typeof rfs != "undefined" && rfs) {
                    rfs.call(docElm);
                } 
                // ActiveXObject
                else if (typeof window.ActiveXObject != "undefined") {
                    var wscript = new ActiveXObject("WScript.Shell");
                    if (wscript != null) {
                        wscript.SendKeys("{F11}");
                    }
                }
                // W3C
                else if (docElm.requestFullscreen) {
                    docElm.requestFullscreen();
                }
                // FireFox
                else if (docElm.mozRequestFullScreen) {
                    docElm.mozRequestFullScreen();
                }
                // Chrome等
                else if (docElm.webkitRequestFullScreen) {
                    docElm.webkitRequestFullScreen();
                }
                // IE11
                else if (docElm.msRequestFullscreen) {
                    docElm.msRequestFullscreen();
                } 
                else if (docElm.oRequestFullscreen) {
                    docElm.oRequestFullscreen();
                } 
                else {
                    layer.msg('浏览器不支持全屏调用！');
                    return false;
                }
                
                $(this).removeClass('icon-fullscreen')
                    .addClass('icon-narrow');
                layer.msg('按Esc即可退出全屏');
            },
            
            // 退出全屏
            exit: function() {
                var docElm = document;
                var cfs = docElm.cancelFullScreen || docElm.webkitCancelFullScreen || docElm.exitFullScreen;
                
                if (typeof cfs != "undefined" && cfs) {
                    cfs.call(docElm);
                } 
                else if (typeof window.ActiveXObject != "undefined") {
                    var wscript = new ActiveXObject("WScript.Shell");
                    if (wscript != null) {
                        wscript.SendKeys("{F11}");
                    }
                } 
                else if (docElm.exitFullscreen) {
                    docElm.exitFullscreen();
                } 
                else if (docElm.msExitFullscreen) {
                    docElm.msExitFullscreen();
                } 
                else if (docElm.oRequestFullscreen) {
                    docElm.oCancelFullScreen();
                } 
                else if (docElm.mozCancelFullScreen) {
                    docElm.mozCancelFullScreen();
                } 
                else if (docElm.webkitCancelFullScreen) {
                    docElm.webkitCancelFullScreen();
                } 
                else {
                    layer.msg('浏览器不支持全屏调用！');
                    return false;
                }
                
                $(this).removeClass('icon-narrow')
                    .addClass('icon-fullscreen');
            }
        }
        
        this.each(function() {
            $(this).on('click', function () {
                var check = $(this).attr('data-check-screen');
                if (check && check == 'full') {
                    $(this).attr('data-check-screen', 'exit');
                    fullScreen.exit();
                } else {
                    $(this).attr('data-check-screen', 'full');
                    fullScreen.full();
                }
            });
            
            return this;
        });
        
    };
    
});

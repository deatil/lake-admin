/*!
 * lakeAdminFullscreen.js v1.0.2
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
!(function(a){
    layui.define(['jquery'], function (exports) {
        var jquery = layui.$,
            layer = layui.layer;
        
        a(jquery, layer);
        
        exports('lakeAdminFullscreen', {});
    });
})(function($, layer) {
    
    // 全屏
    $.fn.lakeAdminFullscreen = function() {
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
                    console.log('浏览器不支持全屏调用！');
                    return false;
                }
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
                    console.log('浏览器不支持全屏调用！');
                    return false;
                }
            },
            
            isFull: function() {
                return Math.abs(window.screen.height-window.document.documentElement.clientHeight) <= 17;
            },
            
            checkFull: function() {
                var isFull = document.fullscreenEnabled || window.fullScreen || document.webkitIsFullScreen || document.msFullscreenEnabled;
                //to fix : false || undefined == undefined
                if (isFull === undefined) {
                    isFull = false;
                }
                return isFull;
            }
        }
        
        var fullScreenAction = {
            full: function(thiz) {
                $(thiz).attr('data-check-screen', 'full');
                fullScreen.full();
            
                $(thiz).find('i').removeClass('icon-fullscreen')
                    .addClass('icon-narrow');
                layer.msg('按Esc即可退出全屏');
            }, 
            exit: function(thiz) {
                $(thiz).attr('data-check-screen', 'exit');
                fullScreen.exit();
            
                $(thiz).find('i').removeClass('icon-narrow')
                    .addClass('icon-fullscreen');
            }
        }
        
        var thiz = this;
        
        $(document).on('keydown', function(event) {
            var e = event || window.event;
            var k = e.keyCode || e.which;
            
            if (k === 122) {
                e.stopPropagation();
                e.preventDefault();
            }
        });
        
        $(document).on("keyup", function(event) {
            // var ctrlKey = event.ctrlKey;
            
            // ESC
            if (event.keyCode === 27) {
                fullScreenAction.exit(thiz);
            }
        });
        
        $(this).on('click', function () {
            var check = $(thiz).attr('data-check-screen');
            if (check && check == 'full') {
                fullScreenAction.exit(thiz);
            } else {
                fullScreenAction.full(thiz);
            }
        });
    };
    
});

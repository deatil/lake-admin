layui.define(["jquery"], function (exports) {
    var $ = layui.jquery;
    var hideClass = 'hide-print';  // 打印时隐藏
    var printingClass = 'printing';  // 正在打印

    var printer = {
        // 打印当前页面
        print: function (param) {
            window.focus();  // 让当前窗口获取焦点
            if (!param) {
                param = {};
            }
            var hide = param.hide;  // 需要隐藏的元素
            var horizontal = param.horizontal;  // 纸张是否是横向
            var iePreview = param.iePreview;  // 兼容ie打印预览
            var close = param.close;  // 打印完是否关闭打印窗口
            var blank = param.blank;  // 是否打开新窗口
            // 设置参数默认值
            if (iePreview == undefined) {
                iePreview = true;
            }
            if (blank == undefined && window != top && iePreview && printer.isIE()) {
                blank = true;
            }
            if (close == undefined) {
                close = true;
                if (iePreview && blank && printer.isIE()) {
                    close = false;
                }
            }
            // 打印方向控制
            $('#page-print-set').remove();
            var htmlStr = '<div id="page-print-set">';
            htmlStr += '      <style type="text/css" media="print">';
            if (horizontal) {
                htmlStr += '     @page { size: landscape; }';
            } else {
                htmlStr += '     @page { size: portrait; }';
            }
            htmlStr += '      </style>';
            // 打印预览兼容ie
            if (iePreview && printer.isIE()) {
                htmlStr += '  <object id="WebBrowser" classid="clsid:8856F961-340A-11D0-A96B-00C04FD705A2" width="0" height="0"></object>';
                htmlStr += '  <script>WebBrowser.ExecWB(7, 1);';
                if (close) {
                    htmlStr += '  window.close();';
                }
                htmlStr += '  </script>';
            }
            htmlStr += '   </div>';
            // 打印
            printer.hideElem(hide);
            // 打印iframe兼容ie
            var pWindow, pDocument;
            if (blank) {
                // 创建打印窗口
                pWindow = window.open('', '_blank');
                pDocument = pWindow.document;
                pWindow.focus();  // 让打印窗口获取焦点
                // 写入内容到打印窗口
                var htmlOld = document.getElementsByTagName('html')[0].innerHTML;
                htmlOld += htmlStr;
                pDocument.open();
                pDocument.write(htmlOld);
                pDocument.close();
                pWindow.onload = function () {
                    (iePreview && printer.isIE()) || pWindow.print();
                    if (blank && close && (!(iePreview && printer.isIE()))) {
                        pWindow.close();
                    }
                    printer.showElem(hide);
                };
            } else {
                pWindow = window;
                $('body').append(htmlStr);
                (iePreview && printer.isIE()) || pWindow.print();
                printer.showElem(hide);
            }
        },
        // 打印html字符串
        printHtml: function (param) {
            if (!param) {
                param = {};
            }
            var html = param.html;  // 打印的html内容
            var blank = param.blank;  // 是否打开新窗口
            var print = param.print;  // 是否自动调用打印
            var close = param.close;  // 打印完是否关闭打印窗口
            var horizontal = param.horizontal;  // 纸张是否是横向
            var iePreview = param.iePreview;  // 兼容ie打印预览
            // 设置参数默认值
            if (print == undefined) {
                print = true;
            }
            if (iePreview == undefined) {
                iePreview = true;
            }
            if (printer.isIE() && blank == undefined) {
                blank = true;
            }
            if (close == undefined) {
                close = true;
                if (iePreview && blank && printer.isIE()) {
                    close = false;
                }
            }
            // 创建打印窗口
            var pWindow, pDocument;
            if (blank) {
                pWindow = window.open('', '_blank');
                pDocument = pWindow.document;
            } else {
                var printFrame = document.getElementById('printFrame');
                if (!printFrame) {
                    $('body').append('<iframe id="printFrame" style="display: none;"></iframe>');
                    printFrame = document.getElementById('printFrame');
                }
                pWindow = printFrame.contentWindow;
                pDocument = printFrame.contentDocument || printFrame.contentWindow.document;
            }
            pWindow.focus();  // 让打印窗口获取焦点
            // 写入内容到打印窗口
            if (html) {
                // 加入公共css
                html += ('<style>' + printer.getCommonCss(true) + '</style>');
                // 打印方向控制
                html += '<style type="text/css" media="print">';
                if (horizontal) {
                    html += '@page { size: landscape; }';
                } else {
                    html += '@page { size: portrait; }';
                }
                html += '</style>';
                // 打印预览兼容ie
                if (iePreview && printer.isIE()) {
                    html += '<object id="WebBrowser" classid="clsid:8856F961-340A-11D0-A96B-00C04FD705A2" width="0" height="0"></object>';
                    html += '<script>WebBrowser.ExecWB(7, 1);';
                    if (close) {
                        html += 'window.close();';
                    }
                    html += '</script>';
                }
                // 写入
                pDocument.open();
                pDocument.write(html);
                pDocument.close();
            }
            // 打印
            if (print) {
                pWindow.onload = function () {
                    (iePreview && printer.isIE()) || pWindow.print();
                    if (blank && close && (!(iePreview && printer.isIE()))) {
                        pWindow.close();
                    }
                };
            }
            return pWindow;
        },
        // 分页打印
        printPage: function (param) {
            if (!param) {
                param = {};
            }
            var htmls = param.htmls;  // 打印的内容
            var style = param.style;  // 打印的样式
            var horizontal = param.horizontal;  // 纸张是否是横向
            var padding = param.padding;  // 页边距
            var blank = param.blank;  // 是否打开新窗口
            var print = param.print;  // 是否自动调用打印
            var close = param.close;  // 打印完是否关闭打印窗口
            var isDebug = param.debug;  // 调试模式
            var iePreview = param.iePreview;  // 兼容ie打印预览
            // 设置参数默认值
            if (print == undefined) {
                print = true;
            }
            if (iePreview == undefined) {
                iePreview = true;
            }
            if (printer.isIE() && blank == undefined) {
                blank = true;
            }
            if (close == undefined) {
                close = true;
                if (iePreview && blank && printer.isIE()) {
                    close = false;
                }
            }
            if (printer.isEdge() || printer.isFirefox()) {
                padding = undefined;
            }
            // 创建打印窗口
            var pWindow, pDocument;
            if (blank) {
                pWindow = window.open('', '_blank');
                pDocument = pWindow.document;
            } else {
                var printFrame = document.getElementById('printFrame');
                if (!printFrame) {
                    $('body').append('<iframe id="printFrame" style="display: none;"></iframe>');
                    printFrame = document.getElementById('printFrame');
                }
                pWindow = printFrame.contentWindow;
                pDocument = printFrame.contentDocument || printFrame.contentWindow.document;
            }
            pWindow.focus();  // 让打印窗口获取焦点
            // 拼接打印内容
            var htmlStr = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>打印窗口</title>';
            style && (htmlStr += style);  // 写入自定义css
            // 控制分页的css
            htmlStr += '<style>';
            htmlStr += 'body {';
            htmlStr += '    margin: 0 !important;';
            htmlStr += '} ';
            // 自定义边距竖屏样式
            htmlStr += '.print-page .print-page-item {';
            htmlStr += '    width: 793px !important;';
            htmlStr += '    height: 1122px !important;';
            htmlStr += '    padding: ' + (padding ? padding : '55px') + ' !important;';
            htmlStr += '    box-sizing: border-box !important;';
            htmlStr += '    overflow: hidden !important;';
            htmlStr += '    border: none !important;';
            htmlStr += '} ';
            // 自定义边距横屏样式
            htmlStr += '.print-page.page-horizontal .print-page-item {';
            htmlStr += '    width: 1122px !important;';
            htmlStr += '    height: 792px !important;';
            htmlStr += '} ';
            // 调试模式样式
            htmlStr += '.print-page.page-debug .print-page-item {';
            htmlStr += '    border: 1px solid red !important;';
            htmlStr += '} ';
            // 谷歌默认边距竖屏样式
            htmlStr += '.print-page.padding-default .print-page-item {';
            htmlStr += '    width: 713px !important;';
            htmlStr += '    height: 1042px !important;';
            htmlStr += '    padding: 0 !important;';
            htmlStr += '} ';
            // 谷歌默认边距横屏样式
            htmlStr += '.print-page.padding-default.page-horizontal .print-page-item {';
            htmlStr += '    width: 1042px !important;';
            htmlStr += '    height: 712px !important;';
            htmlStr += '} ';
            // ie默认边距竖屏样式
            htmlStr += '.print-page.padding-default.page-ie .print-page-item {';
            htmlStr += '    width: 645px !important;';
            htmlStr += '    height: 977px !important;';
            htmlStr += '} ';
            // ie默认边距横屏样式
            htmlStr += '.print-page.padding-default.page-ie.page-horizontal .print-page-item {';
            htmlStr += '    width: 978px !important;';
            htmlStr += '    height: 648px !important;';
            htmlStr += '} ';
            // firefox默认边距竖屏样式
            htmlStr += '.print-page.padding-default.page-firefox .print-page-item {';
            htmlStr += '    width: 720px !important;';
            htmlStr += '    height: 955px !important;';
            htmlStr += '} ';
            // firefox默认边距横屏样式
            htmlStr += '.print-page.padding-default.page-firefox.page-horizontal .print-page-item {';
            htmlStr += '    width: 955px !important;';
            htmlStr += '    height: 720px !important;';
            htmlStr += '} ';
            htmlStr += printer.getCommonCss(true);  // 加入公共css
            htmlStr += '</style>';
            // 控制打印方向
            htmlStr += '<style type="text/css" media="print">';
            if (horizontal) {
                htmlStr += '@page { size: landscape; }';
            } else {
                htmlStr += '@page { size: portrait; }';
            }
            htmlStr += '</style>';
            htmlStr += '</head><body>';
            // 拼接分页内容
            if (htmls && (htmls instanceof Array)) {
                var pageClass = horizontal ? ' page-horizontal' : '';  // 横向样式
                pageClass += padding == undefined ? ' padding-default' : '';  // 谷歌默认边距
                pageClass += isDebug ? ' page-debug' : '';  // 调试模式
                if (printer.isIE() || printer.isEdge()) {
                    pageClass += ' page-ie';  // ie默认边距
                } else if (printer.isFirefox()) {
                    pageClass += ' page-firefox';  // firefox默认边距
                }
                htmlStr += '<div class="print-page' + pageClass + '">';
                for (var i = 0; i < htmls.length; i++) {
                    htmlStr += '<div class="print-page-item">';
                    htmlStr += htmls[i];
                    htmlStr += '</div>';
                }
                htmlStr += '</div>';
            }
            // 兼容ie打印预览
            if (iePreview && printer.isIE()) {
                htmlStr += '<object id="WebBrowser" classid="clsid:8856F961-340A-11D0-A96B-00C04FD705A2" width="0" height="0"></object>';
                htmlStr += '<script>WebBrowser.ExecWB(7, 1);';
                if (close) {
                    htmlStr += 'window.close();';
                }
                htmlStr += '</script>';
            }
            htmlStr += '</body></html>';
            // 写入打印内容到打印窗口
            pDocument.open();
            pDocument.write(htmlStr);
            pDocument.close();
            // 打印
            if (print) {
                pWindow.onload = function () {
                    (iePreview && printer.isIE()) || pWindow.print();
                    if (blank && close && (!(iePreview && printer.isIE()))) {
                        pWindow.close();
                    }
                };
            }
            return pWindow;
        },
        // 隐藏元素
        hideElem: function (elems) {
            $('.' + hideClass).addClass(printingClass);
            if (!elems) {
                return;
            }
            if (elems instanceof Array) {
                for (var i = 0; i < elems.length; i++) {
                    $(elems[i]).addClass(printingClass);
                }
            } else {
                $(elems).addClass(printingClass);
            }
        },
        // 取消隐藏
        showElem: function (elems) {
            $('.' + hideClass).removeClass(printingClass);
            if (!elems) {
                return;
            }
            if (elems instanceof Array) {
                for (var i = 0; i < elems.length; i++) {
                    $(elems[i]).removeClass(printingClass);
                }
            } else {
                $(elems).removeClass(printingClass);
            }
        },
        // 打印公共样式
        getCommonCss: function (isPrinting) {
            var cssStr = ('.' + hideClass + '.' + printingClass + ' {');
            cssStr += '        display: none !important;';
            cssStr += '   }';
            // 表格样式
            cssStr += '   .print-table {';
            cssStr += '        border: none;';
            cssStr += '        border-collapse: collapse;';
            cssStr += '        width: 100%;';
            cssStr += '   }';
            cssStr += '   .print-table td, .print-table th {';
            cssStr += '        color: #333;';
            cssStr += '        padding: 9px 15px;';
            cssStr += '        word-break: break-all;';
            cssStr += '        border: 1px solid #666;';
            cssStr += '   }';
            //
            if (isPrinting) {
                cssStr += ('.' + hideClass + ' {');
                cssStr += '     display: none !important;';
                cssStr += '}';
            }
            return cssStr;
        },
        // 拼接html
        makeHtml: function (param) {
            var title = param.title;
            var style = param.style;
            var body = param.body;
            if (title == undefined) {
                title = '打印窗口';
            }
            var htmlStr = '<!DOCTYPE html><html lang="en">';
            htmlStr += '    <head><meta charset="UTF-8">';
            htmlStr += ('        <title>' + title + '</title>');
            style && (htmlStr += style);
            htmlStr += '   </head>';
            htmlStr += '   <body>';
            body && (htmlStr += body);
            htmlStr += '   </body>';
            htmlStr += '   </html>';
            return htmlStr;
        },
        // 判断是否是ie
        isIE: function () {
            return (!!window.ActiveXObject || "ActiveXObject" in window);
        },
        // 判断是否是Edge
        isEdge: function () {
            return navigator.userAgent.indexOf("Edge") != -1;
        },
        // 判断是否是Firefox
        isFirefox: function () {
            return navigator.userAgent.indexOf("Firefox") != -1;
        }
    };

    $('head').append('<style>' + printer.getCommonCss() + '</style>');
    exports("printer", printer);
});
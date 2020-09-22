/*!
 * fieldlist.js v1.0.3
 * https://github.com/deatil/lake-admin
 * 
 * Apache License 2.0 © Deatil
 */
!(function(a){
    layui.define(['jquery', "laytpl", "jqueryDragsort"], function (exports) {
        var laytpl = layui.laytpl,
            jquery = layui.$;
            
        a(jquery, laytpl);
        
        exports('fieldlist', {});
    });
})(function($, laytpl) {
    
    $.fn.fieldlist = function(options) {
    
        var cssStyle = '\
<style type="text/css" class="lake-admin-fieldlist-css">\
.fieldlist {\
  margin-top: 5px;\
}\
.fieldlist .fieldlist-head span {\
  width: 110px;\
  display: inline-block;\
  font-weight: bold;\
  font-size: 13px;\
}\
.fieldlist dd {\
  display: block;\
  margin: 5px 0;\
}\
.fieldlist dd input {\
  display: inline-block;\
  width: 300px;\
}\
.fieldlist dd input:first-child {\
  width: 105px;\
}\
.fieldlist dd ins {\
  width: 110px;\
  display: inline-block;\
  text-decoration: none;\
}\
.fieldlist .layui-btn+.layui-btn {\
    margin-left: 0 !important;\
}\
.fieldlist .btn-append {\
    padding: 0 6px;\
    font-size: 13px;\
}\
.fieldlist .fieldlist-btns {\
    padding: 6px 0;\
}\
.layui-fieldlist.fieldlist dd {\
    margin: 10px 0 !important;\
}\
.layui-fieldlist.fieldlist dd:first-child {\
    font-size: 15px !important;\
}\
.layui-fieldlist.fieldlist .fieldlist-head span {\
    width: 220px !important;\
}\
.layui-fieldlist.fieldlist dd ins {\
    width: 220px !important;\
}\
.layui-fieldlist.fieldlist dd input:first-child {\
    width: 215px !important;\
}\
@media screen and (max-width: 450px) {\
    .fieldlist .fieldlist-head span {\
        width: 100% !important;\
    }\
    .layui-fieldlist.fieldlist dd input:first-child {\
        width: 100% !important;\
    }\
    .layui-fieldlist.fieldlist dd.fieldlist-item ins {\
        width: 100% !important;\
        margin-bottom: 5px !important;\
    }\
}\
</style>';
        if ($(".lake-admin-fieldlist-css").length <= 0) {
            $("head").append(cssStyle);
        }
        
        var opts = $.extend({}, $.fn.fieldlist.defaults, options);
        
        this.each(function() {
            var thiz = this;
            
            var el = $(this).data("el") || opts.el;
            var main = $(this).data("main") || opts.main;
            var template = $(this).data("template") || opts.template;
            var textarea = $(this).data("textarea") || opts.textarea;
            var dataType = $(this).data("datatype") || opts.dataType;
            var tagName = $(this).data("tag") || opts.tag;
            
            var mainTpl = '<dl class="fieldlist">\
                <dd class="fieldlist-head">\
                    <span>字段</span>\
                    <span>内容</span>\
                </dd>\
                <dd class="fieldlist-btns">\
                    <a href="javascript:;" class="layui-btn layui-btn-sm layui-btn-success btn-append">\
                        <i class="iconfont icon-add"></i> 添加\
                    </a>\
                </dd>\
            </dl>';
            
            var fieldlistTpl = '<dd class="fieldlist-item">\
                <ins><input type="text" class="layui-input" data-name="{{d.name}}[{{d.index}}][key]" value="{{d.row.key?d.row.key:""}}" placeholder="填写字段"/></ins>\
                <ins><input type="text" class="layui-input" data-name="{{d.name}}[{{d.index}}][value]" value="{{d.row.value?d.row.value:""}}" placeholder="填写内容"/></ins>\
                <span class="layui-btn layui-btn-sm layui-btn-danger btn-remove"><i class="iconfont icon-close1"></i></span>\
                <span class="layui-btn layui-btn-sm layui-btn-primary btn-dragsort"><i class="iconfont icon-yidong"></i></span>\
            </dd>';
            
            if (main) {
                main = (typeof main === 'object') ? main : $(main);
                mainTpl = main.html();
            }
            
            var fieldlistClass = 'lake-admin-fieldlist-' + (new Date()).valueOf();
            mainTpl = $(mainTpl).addClass(fieldlistClass).prop("outerHTML");
            
            if (el) {
                el = (typeof el === 'object') ? el : $(el);
                el.html(mainTpl);
            } else {
                $(mainTpl).insertBefore($(thiz));
            }
            
            var tpl = '';
            if (template) {
                template = (typeof template === 'object') ? template : $(template);
                tpl = template.html();
            } else {
                tpl = fieldlistTpl;
            }
            
            if (textarea) {
                textarea = (typeof textarea === 'object') ? textarea : $(textarea);
            } else {
                textarea = $(thiz);
            }
            
            var container = $('.' + fieldlistClass);

            // 刷新隐藏textarea的值
            var refresh = function () {
                var data = {};
                $("input,select,textarea", container).each(function () {
                    var name = $(this).attr('data-name');
                    var value = $(this).prop('value');
                    
                    var reg = /\[(\w+)\]\[(\w+)\]$/g;
                    var match = reg.exec(name);
                    if (!match) {
                        return true;
                    }
                    match[1] = "x" + parseInt(match[1]);
                    if (typeof data[match[1]] == 'undefined') {
                        data[match[1]] = {};
                    }
                    data[match[1]][match[2]] = value;
                });
                var result = (dataType == 'list') ? [] : {};
                $.each(data, function (i, j) {
                    if (j) {
                        if (dataType != 'list') {
                            if (j.key != '') {
                                result[j.key] = j.value;
                            }
                        } else {
                            result.push(j);
                        }
                    }
                });
                textarea.val(JSON.stringify(result));
            };
            
            // 监听文本框改变事件
            container.on('change keyup', "input,textarea,select", function () {
                refresh();
            });
            
            // 追加控制
            container.on("click", ".btn-append,.js-append", function (e, row) {
                var index = $(thiz).data("index");
                var name = textarea.attr("name");
                var template = $(thiz).data("template");
                var data = $(thiz).data();
                
                index = index ? parseInt(index) : 0;
                $(thiz).data("index", index + 1);
                var row = row ? row : {};
                var vars = {index: index, name: name, data: data, row: row};

                var html = laytpl(tpl || '').render(vars);
                $(html).insertBefore($(this).parent());
            });
            
            // 移除控制
            container.on("click", ".btn-remove,.js-remove", function () {
                $(this).closest(tagName).remove();
                refresh();
            });
            
            // 拖拽排序
            container.dragsort({
                itemSelector: tagName + '.fieldlist-item',
                dragSelector: ".btn-dragsort,.js-dragsort",
                dragEnd: function () {
                    refresh();
                },
                placeHolderTemplate: $("<" + tagName + " class='fieldlist-item' />"),
                scrollSpeed: 15
            });
            
            // 渲染数据
            var render = function () {
                if (textarea.val() == '') {
                    return true;
                }
                var json = {};
                try {
                    json = JSON.parse(textarea.val());
                } catch (e) {
                }
                $.each(json, function (i, j) {
                    $(".btn-append,.js-append", container).trigger('click', (dataType == 'list') ? j : {
                        key: i,
                        value: j
                    });
                });
            };
            render();
        });
        
        return this;
    };
    
    $.fn.fieldlist.defaults = {
        el:"",
        main: "",
        template: "",
        textarea: "",
        tag: "dd",
        dataType: "object", // object or list
    };
    
});

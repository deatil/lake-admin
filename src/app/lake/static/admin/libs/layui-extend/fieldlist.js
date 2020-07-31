!(function(a){
    layui.define(['jquery', "laytpl", "jquery_dragsort"], function (exports) {
        var laytpl = layui.laytpl,
            jquery = layui.$;
            
        a(jquery, laytpl);
        
        exports('fieldlist', {});
    });
})(function($, laytpl) {
    
    var cssStyle = '\
<style type="text/css" class="lake-admin-fieldlist">\
.fieldlist dd:first-child {\
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
</style>';
    if ($(".lake-admin-fieldlist").length <= 0) {
        $("body").append(cssStyle);
    }
    
    $.fn.fieldlist = function() {
        
        this.each(function(i, form) {

            form = typeof form === 'object' ? form : $(form);
        
            if ($(".fieldlist", form).size() <= 0) {
                return ;
            }
            
            var fieldlistTpl = '<dd class="form-inline">\
                <input type="text" name="{{name}}[{{index}}][key]" class="form-control" value="{{row.key}}" size="10" />\
                <input type="text" name="{{name}}[{{index}}][value]" class="form-control" value="{{row.value}}" />\
                <span class="btn btn-sm btn-danger btn-remove">\
                    <i class="fa fa-times"></i>\
                </span>\
                <span class="btn btn-sm btn-primary btn-dragsort">\
                    <i class="fa fa-arrows"></i>\
                </span>\
            </dd>';

            // 刷新隐藏textarea的值
            var refresh = function (container) {
                var data = {};
                var name = container.data("name");
                var textarea = $("textarea[name='" + name + "']", form);
                var template = container.data("template");
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
                var result = template ? [] : {};
                $.each(data, function (i, j) {
                    if (j) {
                        if (!template) {
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
            $(".fieldlist", form).on('change keyup', "input,textarea,select", function () {
                refresh($(this).closest("dl"));
            });
            
            // 追加控制
            $(".fieldlist", form).on("click", ".btn-append,.js-append", function (e, row) {
                var container = $(this).closest("dl");
                var index = container.data("index");
                var name = container.data("name");
                var template = container.data("template");
                var data = container.data();
                index = index ? parseInt(index) : 0;
                container.data("index", index + 1);
                var row = row ? row : {};
                var vars = {index: index, name: name, data: data, row: row};
                
                var tpl = '';
                if (template) {
                    tpl = $('#' + template).html();
                } else {
                    tpl = fieldlistTpl;
                }

                var html = laytpl(tpl || '').render(vars);
                $(html).insertBefore($(this).closest("dd"));
            });
            
            // 移除控制
            $(".fieldlist", form).on("click", ".btn-remove,.js-remove", function () {
                var container = $(this).closest("dl");
                $(this).closest("dd").remove();
                refresh(container);
            });
            
            // 拖拽排序
            $("dl.fieldlist", form).dragsort({
                itemSelector: 'dd',
                dragSelector: ".btn-dragsort,js-dragsort",
                dragEnd: function () {
                    refresh($(this).closest("dl"));
                },
                placeHolderTemplate: "<dd></dd>"
            });
            
            // 渲染数据
            $(".fieldlist", form).each(function () {
                var thiz = this;
                var container = $(this).closest("dl");
                var name = container.data("name");
                var textarea = $("textarea[name='" + name + "']", form);
                if (textarea.val() == '') {
                    return true;
                }
                var template = container.data("template");
                var json = {};
                try {
                    json = JSON.parse(textarea.val());
                } catch (e) {
                }
                $.each(json, function (i, j) {
                    $(".btn-append,.js-append", thiz).trigger('click', template ? j : {
                        key: i,
                        value: j
                    });
                });
            });
        });
        
        return this;
    }
});

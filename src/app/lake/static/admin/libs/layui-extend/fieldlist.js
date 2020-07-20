layui.define(['jquery', 'laytpl', 'jquery_dragsort'], function (exports) { //layui加载
	var fieldlist = function(form) {
		
		var layer = layui.layer,
			laytpl = layui.laytpl,
			$ = layui.$;
			
		var cssStyle = '\
<style type="text/css">\
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
		$("body").append(cssStyle);

		form = typeof form === 'object' ? form : $(form);
	
		if ($(".fieldlist", form).size() <= 0) {
			return ;
		}

		// 添加 dragsort
		$ = layui.jquery_dragsort($);
		
		var fieldlisttpl = '<dd class="form-inline"><input type="text" name="{{name}}[{{index}}][key]" class="form-control" value="{{row.key}}" size="10" /> <input type="text" name="{{name}}[{{index}}][value]" class="form-control" value="{{row.value}}" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span></dd>';

		//刷新隐藏textarea的值
		var refresh = function (name) {
			var data = {};
			var textarea = $("textarea[name='" + name + "']", form);
			var container = textarea.closest("dl");
			var template = container.data("template");
			$.each($("input,select,textarea", container).serializeArray(), function (i, j) {
				var reg = /\[(\w+)\]\[(\w+)\]$/g;
				var match = reg.exec(j.name);
				if (!match)
					return true;
				match[1] = "x" + parseInt(match[1]);
				if (typeof data[match[1]] == 'undefined') {
					data[match[1]] = {};
				}
				data[match[1]][match[2]] = j.value;
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
		//监听文本框改变事件
		$(document).on('change keyup', ".fieldlist input,.fieldlist textarea,.fieldlist select", function () {
			refresh($(this).closest("dl").data("name"));
		});
		//追加控制
		$(".fieldlist", form).on("click", ".btn-append,.append", function (e, row) {
			var container = $(this).closest("dl");
			var index = container.data("index");
			var name = container.data("name");
			var template = container.data("template");
			var data = container.data();
			index = index ? parseInt(index) : 0;
			container.attr("data-index", index + 1);
			var row = row ? row : {};
			var vars = {index: index, name: name, data: data, row: row};
			
			var tpl = '';
			if (template) {
				tpl = $('#' + template).html();
			} else {
				tpl = fieldlisttpl;
			}			

			var html = laytpl(tpl || '').render(vars);
			$(html).insertBefore($(this).closest("dd"));
		});
		//移除控制
		$(".fieldlist", form).on("click", "dd .btn-remove", function () {
			var container = $(this).closest("dl");
			$(this).closest("dd").remove();
			refresh(container.data("name"));
		});
		//拖拽排序
		$("dl.fieldlist", form).dragsort({
			itemSelector: 'dd',
			dragSelector: ".btn-dragsort",
			dragEnd: function () {
				refresh($(this).closest("dl").data("name"));
			},
			placeHolderTemplate: "<dd></dd>"
		});
		//渲染数据
		$(".fieldlist", form).each(function () {
			var container = this;
			var textarea = $("textarea[name='" + $(this).data("name") + "']", form);
			if (textarea.val() == '') {
				return true;
			}
			var template = $(this).data("template");
			var json = {};
			try {
				json = JSON.parse(textarea.val());
			} catch (e) {
			}
			$.each(json, function (i, j) {
				$(".btn-append,.append", container).trigger('click', template ? j : {
					key: i,
					value: j
				});
			});
		});
	}

	exports('fieldlist', fieldlist);
});

/* vali, global define */
(function (define) {
    define(['layer', 'form', 'jquery'], function (layer, form, $) {
        return (function () {
			/*! 验证 */
			var vali = function (form, callback, options) {
				return (new function (that) {
					that = this;
					// 表单元素
					this.tags = 'input,textarea,select';
					// 检测元素事件
					this.checkEvent = {change: true, blur: true, keyup: false};
					// 去除字符串两头的空格
					this.trim = function (str) {
						return str.replace(/(^\s*)|(\s*$)/g, '');
					};
					// 标签元素是否可见
					this.isVisible = function (ele) {
						return $(ele).is(':visible');
					};
					// 检测属性是否有定义
					this.hasProp = function (ele, prop) {
						if (typeof prop !== "string") return false;
						var attrProp = ele.getAttribute(prop);
						return (typeof attrProp !== 'undefined' && attrProp !== null && attrProp !== false);
					};
					// 判断表单元素是否为空
					this.isEmpty = function (ele, value) {
						var trim = this.trim(ele.value);
						value = value || ele.getAttribute('placeholder');
						return (trim === "" || trim === value);
					};
					// 正则验证表单元素
					this.isRegex = function (ele, regex, params) {
						var input = $(ele).val(), real = this.trim(input);
						regex = regex || ele.getAttribute('pattern');
						if (real === "" || !regex) return true;
						return new RegExp(regex, params || 'i').test(real);
					};
					// 检侧所的表单元素
					this.checkAllInput = function () {
						var isPass = true;
						$(form).find(this.tags).each(function () {
							if (that.checkInput(this) === false) return $(this).focus(), isPass = false;
						});
						return isPass;
					};
					// 检测表单单元
					this.checkInput = function (input) {
						var tag = input.tagName.toLowerCase(), need = this.hasProp(input, "required");
						var type = (input.getAttribute("type") || '').replace(/\W+/, "").toLowerCase();
						if (this.hasProp(input, 'data-auto-none')) return true;
						var ingoreTags = ['select'], ingoreType = ['radio', 'checkbox', 'submit', 'reset', 'image', 'file', 'hidden'];
						for (var i in ingoreTags) if (tag === ingoreTags[i]) return true;
						for (var i in ingoreType) if (type === ingoreType[i]) return true;
						if (need && this.isEmpty(input)) return this.remind(input);
						return this.isRegex(input) ? (this.hideError(input), true) : this.remind(input);
					};
					// 验证标志
					this.remind = function (input) {
						if (!this.isVisible(input)) return true;
						this.showError(input, input.getAttribute('title') || input.getAttribute('placeholder') || '输入错误');
						return false;
					};
					// 错误消息显示
					this.showError = function (ele, content) {
						$(ele).addClass('validate-error'), this.insertError(ele);
						$($(ele).data('input-info')).addClass('layui-anim layui-anim-fadein').css({width: 'auto'}).html(content);
					};
					// 错误消息消除
					this.hideError = function (ele) {
						$(ele).removeClass('validate-error'), this.insertError(ele);
						$($(ele).data('input-info')).removeClass('layui-anim-fadein').css({width: '30px'}).html('');
					};
					// 错误消息标签插入
					this.insertError = function (ele) {
						var $html = $('<span style="padding-right:12px;color:#a94442;position:absolute;right:0;font-size:12px;z-index:2;display:block;width:34px;text-align:center;pointer-events:none"></span>');
						$html.css({top: $(ele).position().top + 'px', paddingBottom: $(ele).css('paddingBottom'), lineHeight: $(ele).css('height')});
						$(ele).data('input-info') || $(ele).data('input-info', $html.insertAfter(ele));
					};
					// 表单验证入口
					this.check = function (form, callback) {
						$(form).attr("novalidate", "novalidate");
						$(form).find(that.tags).map(function () {
							this.bindEventMethod = function () {
								that.checkInput(this);
							};
							for (var e in that.checkEvent) if (that.checkEvent[e] === true) {
								$(this).off(e, this.bindEventMethod).on(e, this.bindEventMethod);
							}
						});
						$(form).bind("submit", function (event) {
							if (that.checkAllInput() && typeof callback === 'function') {
								if (typeof CKEDITOR === 'object' && typeof CKEDITOR.instances === 'object') {
									for (var i in CKEDITOR.instances) CKEDITOR.instances[i].updateElement();
								}
								callback.call(this, $(form).formToJson());
							}
							return event.preventDefault(), false;
						});
						$(form).find('[data-form-loaded]').map(function () {
							$(this).html(this.getAttribute('data-form-loaded') || this.innerHTML);
							$(this).removeAttr('data-form-loaded').removeClass('layui-disabled');
						});
						return $(form).data('validate', this);
					};
				}).check(form, callback, options);
			};

			/*! 自动监听规则内表单 */
			vali.listen = function () {
				$('form[data-auto]').map(function () {
					if ($(this).attr('data-listen') !== 'true') $(this).attr('data-listen', 'true').vali(function (data) {
						var call = $(this).attr('data-callback') || '_default_callback';
						var type = this.getAttribute('method') || 'POST', tips = this.getAttribute('data-tips') || undefined;
						var time = this.getAttribute('data-time') || undefined, href = this.getAttribute('action') || window.location.href;
						form.load(href, data, type, window[call] || undefined, true, tips, time);
					});
				});
			};
			
            return vali;
        })();
    });
}(typeof define === 'function' && define.amd ? define : function (deps, factory) {
    if (typeof module !== 'undefined' && module.exports) { //Node
        module.exports = factory(require('layer'), require('form'), require('jquery'));
    }
    else if (window.layui && layui.define){
        layui.define(['layer', 'form', 'jquery'], function (exports) { //layui加载
            exports('vali', factory(layui.layer, layui.form, layui.jquery));
        });
    }
    else {
        window.vali = factory(window.layer, window.form, window.jQuery);
    }
}));
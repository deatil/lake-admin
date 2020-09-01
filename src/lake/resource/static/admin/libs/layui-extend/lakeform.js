/* lakeform, global define */
(function (define) {
    define(['layer', 'jquery'], function (layer, $) {
        return (function () {
			/*! 表单自动化组件 */
			var lakeform = new function (that) {
				that = this;
				// 内容区选择器
				this.selecter = '.layui-layout-body, .lake-admin-container';
				// 刷新当前页面
				this.reload = function () {
					window.onhashchange.call(this);
				};
				// 内容区域动态加载后初始化
				this.reInit = function ($dom) {
					$.vali.listen(this);
					$dom = $dom || $(this.selecter);
					$dom.find('[required]').map(function ($parent) {
						if (($parent = $(this).parent()) && $parent.is('label')) {
							$parent.addClass('label-required-prev');
						} else {
							$parent.prevAll('label').addClass('label-required-next');
						}
					});
					$dom.find('input[data-date-range]').map(function () {
						this.setAttribute('autocomplete', 'off');
						laydate.render({
							type: this.getAttribute('data-date-range') || 'date',
							range: true, elem: this, done: function (value) {
								$(this.elem).val(value).trigger('change');
							}
						});
					});
					$dom.find('input[data-date-input]').map(function () {
						this.setAttribute('autocomplete', 'off');
						laydate.render({
							type: this.getAttribute('data-date-input') || 'date',
							range: false, elem: this, done: function (value) {
								$(this.elem).val(value).trigger('change');
							}
						});
					});
					$dom.find('[data-file]:not([data-inited])').map(function (index, elem, $this, field) {
						$this = $(elem), field = $this.attr('data-field') || 'file';
						if (!$this.data('input')) $this.data('input', $('[name="' + field + '"]').get(0));
						$this.uploadFile(function (url) {
							$($this.data('input')).val(url).trigger('change');
						});
					});
				};
				// 在内容区显示视图
				this.show = function (html) {
					$(this.selecter).html(html);
					this.reInit($(this.selecter));
					setTimeout(function () {
						that.reInit($(that.selecter));
					}, 500);
				};
				// 以HASH打开新网页
				this.href = function (url, obj) {
					if (url !== '#') {
						window.location.href = '#' + $.menu.parseUri(url, obj);
					} else if (obj && obj.getAttribute('data-menu-node')) {
						$('[data-menu-node^="' + obj.getAttribute('data-menu-node') + '-"][data-open!="#"]:first').trigger('click');
					}
				};
				// 异步加载的数据
				this.load = function (url, data, method, callback, loading, tips, time, headers) {
					var index = loading !== false ? $.msg.loading(tips) : 0;
					if (typeof data === 'object' && typeof data['_csrf_'] === 'string') {
						headers = headers || {};
						headers['User-Token-Csrf'] = data['_csrf_'];
						delete data['_csrf_'];
					}
					$.ajax({
						data: data || {}, type: method || 'GET', url: $.menu.parseUri(url), beforeSend: function (xhr) {
							if (typeof Pace === 'object') Pace.restart();
							if (typeof headers === 'object') for (var i in headers) xhr.setRequestHeader(i, headers[i]);
						}, error: function (XMLHttpRequest) {
							if (XMLHttpRequest.responseText.indexOf('exception') > -1) layer.open({
								title: XMLHttpRequest.status + ' - ' + XMLHttpRequest.statusText, type: 2,
								area: '800px', content: 'javascript:void(0)', success: function ($element, index) {
									try {
										layer.full(index);
										$element.find('iframe')[0].contentWindow.document.write(XMLHttpRequest.responseText);
										$element.find('.layui-layer-setwin').css({right: '35px', top: '28px'}).find('a').css({marginLeft: 0});
										$element.find('.layui-layer-title').css({color: 'red', height: '70px', lineHeight: '70px', fontSize: '22px', textAlign: 'center', fontWeight: 700});
									} catch (e) {
										layer.close(index);
									}
								}
							});
							if (parseInt(XMLHttpRequest.status) === 200) {
								this.success(XMLHttpRequest.responseText);
							} else {
								$.msg.tips('E' + XMLHttpRequest.status + ' - 服务器繁忙，请稍候再试！');
							}
						}, success: function (ret) {
							if (typeof callback === 'function' && callback.call(that, ret) === false) return false;
							return typeof ret === 'object' ? $.msg.auto(ret, time || ret.wait || undefined) : that.show(ret);
						}, complete: function () {
							$.msg.close(index);
						}
					});
				};
				// 加载HTML到目标位置
				this.open = function (url, data, callback, loading, tips) {
					this.load(url, data, 'get', function (ret) {
						return (typeof ret === 'object' ? $.msg.auto(ret) : that.show(ret)), false;
					}, loading, tips);
				};
				// 打开一个iframe窗口
				this.iframe = function (url, title, area) {
					return layer.open({title: title || '窗口', type: 2, area: area || ['800px', '580px'], fix: true, maxmin: false, content: url});
				};
				// 加载HTML到弹出层
				this.modal = function (url, data, title, callback, loading, tips) {
					this.load(url, data, 'GET', function (res, index) {
						if (typeof (res) === 'object') return $.msg.auto(res), false;
						index = layer.open({
							type: 1, btn: false, area: "800px", content: res, title: title || '', success: function (dom, index) {
								$(dom).find('[data-close]').off('click').on('click', function () {
									if ($(this).attr('data-confirm')) return $.msg.confirm($(this).attr('data-confirm'), function (_index) {
										layer.close(_index), layer.close(index);
									}), false;
									layer.close(index);
								});
								$.form.reInit($(dom));
							}
						});
						$.msg.idx.push(index);
						return (typeof callback === 'function') && callback.call(that);
					}, loading, tips);
				};
			};
			
            return lakeform;
        })();
    });
}(typeof define === 'function' && define.amd ? define : function (deps, factory) {
    if (typeof module !== 'undefined' && module.exports) { //Node
        module.exports = factory(require('layer'), require('jquery'));
    }
    else if (window.layui && layui.define){
        layui.define(['layer', 'jquery'], function (exports) { //layui加载
            exports('lakeform', factory(layui.layer, layui.jquery));
        });
    }
    else {
        window.lakeform = factory(window.layer, window.jQuery);
    }
}));
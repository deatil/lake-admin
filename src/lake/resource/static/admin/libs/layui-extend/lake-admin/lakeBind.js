/**
 * 快速绑定方法，部分代码来自于网络
 */
layui.define(['table', 'element', 'layer', 'form', 'notice', 'msg', 'lakeform', 'fieldlist'], function(exports) {
    var element = layui.element,
        table = layui.table,
        layer = layui.layer,
        $ = layui.jquery,
        form = layui.form,
        msg = layui.msg,
        lakeform = layui.lakeform,
        notice = layui.notice;
    
    window.$body = $('body');
    
    /*! 顶部鼠标移上显示 */
    var layer_tip;
    $(document).on('mouseenter', '[data-lake-tip]', function() {
        var title = $(this).attr("lay-title");
        if (title) {
            layer_tip = layer.tips(title, this, {
                tips: [1, '#009688'],
            });
        }
    });
    $(document).on('mouseleave', '[data-lake-tip]', function() {
        if (layer_tip) {
            layer.close(layer_tip);
        }
    });
    
    /*! 多参数表单 */
    $('.layui-form-fieldlist').fieldlist();
    
    /*! 注册 data-lake-load 事件行为 */
    $body.on('click', '[data-lake-load]', function () {
        var url = $(this).attr('data-lake-load'), tips = $(this).attr('data-lake-tips'), time = $(this).attr('data-lake-time');
        if ($(this).attr('data-lake-confirm')) return msg.confirm($(this).attr('data-lake-confirm'), function () {
            lakeform.load(url, {}, 'get', null, true, tips, time);
        });
        lakeform.load(url, {}, 'get', null, true, tips, time);
    });

    /*! 注册 data-lake-modal 事件行为 */
    $body.on('click', '[data-lake-modal]', function () {
        return lakeform.modal($(this).attr('data-lake-modal'), 'open_type=modal', $(this).attr('data-lake-title') || $(this).text() || '编辑');
    });

    /*! 注册 data-lake-open 事件行为 */
    $body.on('click', '[data-lake-open]', function () {
        lakeform.href($(this).attr('data-lake-open'), this);
    });

    /*! 注册 data-lake-dbclick 事件行为 */
    $body.on('dblclick', '[data-lake-dbclick]', function () {
        $(this).find(this.getAttribute('data-lake-dbclick') || '[data-lake-dbclick]').trigger('click');
    });

    /*! 注册 data-lake-reload 事件行为 */
    $body.on('click', '[data-lake-reload]', function () {
        lakeform.reload();
    });

    /*! 注册 data-lake-check 事件行为 */
    $body.on('click', '[data-lake-check-target]', function () {
        var checked = !!this.checked;
        $($(this).attr('data-lake-check-target')).map(function () {
            this.checked = checked;
            $(this).trigger('change');
        });
    });

    /*! 注册 data-lake-action 事件行为 */
    $body.on('click', '[data-lake-action]', function () {
        var $this = $(this), data = {}, time = $this.attr('data-lake-time'), action = $this.attr('data-lake-action');
        var loading = $this.attr('data-lake-loading'), method = $this.attr('data-lake-method') || 'post';
        var rule = $this.attr('data-lake-value') || (function (rule, ids) {
            $($this.attr('data-lake-target') || 'input[type=checkbox].list-check-box').map(function () {
                (this.checked) && ids.push(this.value);
            });
            return ids.length > 0 ? rule.replace('{key}', ids.join(',')) : '';
        }).call(this, $this.attr('data-lake-rule') || '', []) || '';
        if (rule.length < 1) return msg.tips('请选择需要更改的数据！');
        var rules = rule.split(';');
        for (var i in rules) {
            if (rules[i].length < 2) return msg.tips('异常的数据操作规则，请修改规则！');
            data[rules[i].split('#')[0]] = rules[i].split('#')[1];
        }
        data['_csrf_'] = $this.attr('data-lake-token') || $this.attr('data-lake-csrf') || '--';
        var load = loading !== 'false', tips = typeof loading === 'string' ? loading : undefined;
        if (!$this.attr('data-lake-confirm')) lakeform.load(action, data, method, false, load, tips, time);
        else msg.confirm($this.attr('data-lake-confirm'), function () {
            lakeform.load(action, data, method, false, load, tips, time);
        });
    });

    /*! 输入框失焦提交 */
    $body.on('blur', '[data-lake-action-blur]', function () {
        var data = {}, that = this, $this = $(this), action = $this.attr('data-lake-action-blur');
        var time = $this.attr('data-lake-time'), loading = $this.attr('data-lake-loading') || false;
        var load = loading !== 'false', tips = typeof loading === 'string' ? loading : undefined;
        var method = $this.attr('data-lake-method') || 'post', confirm = $this.attr('data-lake-confirm');
        var attrs = $this.attr('data-lake-value').replace('{value}', $this.val()).split(';');
        for (var i in attrs) {
            if (attrs[i].length < 2) return msg.tips('异常的数据操作规则，请修改规则！');
            data[attrs[i].split('#')[0]] = attrs[i].split('#')[1];
        }
        that.callback = function (ret) {
            $this.css('border', (ret && ret.code) ? '1px solid #e6e6e6' : '1px solid red');
            return false;
        };
        data['_csrf_'] = $this.attr('data-lake-token') || $this.attr('data-lake-csrf') || '--';
        if (!confirm) return lakeform.load(action, data, method, that.callback, load, tips, time);
        msg.confirm(confirm, function () {
            lakeform.load(action, data, method, that.callback, load, tips, time);
        });
    });

    /*! 注册 data-lake-href 事件行为 */
    $body.on('click', '[data-lake-href]', function (href) {
        href = $(this).attr('data-lake-href');
        if (href && href.indexOf('#') !== 0) window.location.href = href;
    });

    /*! 注册 data-lake-iframe 事件行为 */
    $body.on('click', '[data-lake-iframe]', function (index) {
        index = lakeform.iframe($(this).attr('data-lake-iframe'), $(this).attr('data-lake-title') || '窗口');
        $(this).attr('data-lake-index', index);
    });

    /*! 注册 data-lake-copy 事件行为 */
    $body.on('click', '[data-lake-copy]', function () {
        $.copyToClipboard(this.getAttribute('data-lake-copy'));
    });
    $.copyToClipboard = function (content, input) {
        input = document.createElement('textarea');
        input.style.position = 'absolute', input.style.left = '-100000px';
        input.style.width = '1px', input.style.height = '1px', input.innerText = content;
        document.body.appendChild(input), input.select(), setTimeout(function () {
            document.execCommand('Copy') ? msg.tips('复制成功') : msg.tips('复制失败，请使用鼠标操作复制！');
            document.body.removeChild(input);
        }, 100);
    };

    /*! 注册 data-lake-tips-text 事件行为 */
    $body.on('mouseenter', '[data-lake-tips-text]', function () {
        $(this).attr('index', layer.tips($(this).attr('data-lake-tips-text'), this, {tips: [$(this).attr('data-lake-tips-type') || 3, '#78BA32']}));
    }).on('mouseleave', '[data-lake-tips-text]', function () {
        layer.close($(this).attr('index'));
    });

    /*! 注册 data-lake-tips-image 事件行为 */
    $body.on('click', '[data-lake-tips-image]', function () {
        $.previewImage(this.getAttribute('data-lake-tips-image') || this.src, this.getAttribute('data-lake-width'));
    });
    $.previewImage = function (src, area) {
        var img = new Image(), index = msg.loading();
        img.style.background = '#fff', img.style.display = 'none';
        img.style.height = 'auto', img.style.width = area || '480px';
        document.body.appendChild(img), img.onerror = function () {
            msg.close(index);
        }, img.onload = function () {
            layer.open({
                type: 1, shadeClose: true, success: img.onerror, content: $(img), title: false,
                area: area || '480px', closeBtn: 1, skin: 'layui-layer-nobg', end: function () {
                    document.body.removeChild(img);
                }
            });
        };
        img.src = src;
    };

    /*! 注册 data-lake-phone-view 事件行为 */
    $body.on('click', '[data-lake-phone-view]', function () {
        $.previewPhonePage(this.getAttribute('data-lake-phone-view') || this.href);
    });
    $.previewPhonePage = function (href, title) {
        var tpl = '<div><div class="mobile-preview pull-left"><div class="mobile-header">_TITLE_</div><div class="mobile-body"><iframe id="phone-preview" src="_URL_" frameborder="0" marginheight="0" marginwidth="0"></iframe></div></div></div>';
        layer.style(layer.open({type: true, scrollbar: false, area: ['320px', '600px'], title: false, closeBtn: true, shadeClose: false, skin: 'layui-layer-nobg', content: $(tpl.replace('_TITLE_', title || '公众号').replace('_URL_', href)).html(),}), {boxShadow: 'none'});
    };

    /*! 表单编辑返回操作 */
    $body.on('click', '[data-lake-history-back]', function (title) {
        title = this.getAttribute('data-lake-history-back') || '确定要返回上一页吗？';
        msg.confirm(title, function (index) {
            history.back();
            msg.close(index);
        })
    });

    /*! 表单元素失去焦点处理 */
    $body.on('blur', '[data-lake-blur-number]', function (fiexd) {
        fiexd = this.getAttribute('data-lake-blur-number') || 0;
        this.value = (parseFloat(this.value) || 0).toFixed(fiexd);
    });
    
    exports('lakeBind', {});
});
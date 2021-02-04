/* msg, global define */
(function (define) {
    define(['layer', 'jquery'], function (layer, $) {
        return (function () {
            /*! 消息组件实例 */
            var msg = new function () {
                var self = this;
                this.shade = [0.02, '#000'];
                this.dialogIndexs = [];
                // 关闭消息框
                this.close = function (index) {
                    return layer.close(index);
                };
                // 弹出警告消息框
                this.alert = function (msg, callback) {
                    var index = layer.alert(msg, {end: callback, scrollbar: false});
                    return this.dialogIndexs.push(index), index;
                };
                // 确认对话框
                this.confirm = function (msg, ok, no) {
                    var index = layer.confirm(msg, {title: '操作确认', btn: ['确认', '取消']}, function () {
                        typeof ok === 'function' && ok.call(this);
                        self.close(index);
                    }, function () {
                        typeof no === 'function' && no.call(this);
                        self.close(index);
                    });
                    return index;
                };
                // 显示成功类型的消息
                this.success = function (msg, time, callback) {
                    var index = layer.msg(msg, {icon: 1, shade: this.shade, scrollbar: false, end: callback, time: (time || 2) * 1000, shadeClose: true});
                    return this.dialogIndexs.push(index), index;
                };
                // 显示失败类型的消息
                this.error = function (msg, time, callback) {
                    var index = layer.msg(msg, {icon: 2, shade: this.shade, scrollbar: false, time: (time || 3) * 1000, end: callback, shadeClose: true});
                    return this.dialogIndexs.push(index), index;
                };
                // 状态消息提示
                this.tips = function (msg, time, callback) {
                    var index = layer.msg(msg, {time: (time || 3) * 1000, shade: this.shade, end: callback, shadeClose: true});
                    return this.dialogIndexs.push(index), index;
                };
                // 显示正在加载中的提示
                this.loading = function (msg, callback) {
                    var index = msg ? layer.msg(msg, {icon: 16, scrollbar: false, shade: this.shade, time: 0, end: callback}) : layer.load(2, {time: 0, scrollbar: false, shade: this.shade, end: callback});
                    return this.dialogIndexs.push(index), index;
                };
                // 自动处理显示Think返回的Json数据
                this.auto = function (data, time) {
                    return (parseInt(data.code) === 1) ? self.success(data.msg, time, function () {
                        !!data.url ? (window.location.href = data.url) : $.form.reload();
                        for (var i in self.dialogIndexs) {
                            layer.close(self.dialogIndexs[i]);
                        }
                        self.dialogIndexs = [];
                    }) : self.error(data.msg, 3, function () {
                        !!data.url && (window.location.href = data.url);
                    });
                };
                
                /**
                 * 抛出一个异常错误信息
                 * @param {String} msg
                 */
                this.throwError = function(msg) {
                    throw new Error(msg);
                    return;
                };
            };
            
            return msg;
        })();
    });
}(typeof define === 'function' && define.amd ? define : function (deps, factory) {
    if (typeof module !== 'undefined' && module.exports) { //Node
        module.exports = factory(require('layer'), require('jquery'));
    }
    else if (window.layui && layui.define){
        layui.define(['layer', 'jquery'], function (exports) { //layui加载
            exports('msg', factory(layui.layer, layui.jquery));
        });
    }
    else {
        window.msg = factory(window.layer, window.jQuery);
    }
}));
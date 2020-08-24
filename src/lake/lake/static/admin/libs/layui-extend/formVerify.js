/**
 * 表单扩展模块
 */
layui.define(['form'], function (exports) {
    var $ = layui.jquery;
    var form = layui.form;
    var verifyText = {
        phoneX: '请输入正确的手机号',
        emailX: '邮箱格式不正确',
        urlX: '链接格式不正确',
        numberX: '只能填写数字',
        dateX: '日期格式不正确',
        identityX: '请输入正确的身份证号',
        psw: '密码必须5到12位，且不能出现空格',
        equalTo: '两次输入不一致',
        digits: '只能输入整数',
        digitsP: '只能输入正整数',
        digitsN: '只能输入负整数',
        digitsPZ: '只能输入正整数和0',
        digitsNZ: '只能输入负整数和0',
        minlength: '最少输入{minlength}个字符',
        maxlength: '最多输入{maxlength}个字符',
        min: '值不能小于{min}',
        max: '值不能小于{max}'
    };

    /** 扩展验证规则 */
    var verifyList = {
        /* 手机号 */
        phoneX: function (value, item) {
            var reg = /^1\d{10}$/;
            if (value && !reg.test(value)) {
                return verifyText.phoneX;
            }
        },
        /* 邮箱 */
        emailX: function (value, item) {
            var reg = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (value && !reg.test(value)) {
                return verifyText.emailX;
            }
        },
        /* 网址 */
        urlX: function (value, item) {
            var reg = /(^#)|(^http(s*):\/\/[^\s]+\.[^\s]+)/;
            if (value && !reg.test(value)) {
                return verifyText.urlX;
            }
        },
        /* 数字 */
        numberX: function (value, item) {
            if (value && isNaN(value)) {
                return verifyText.numberX;
            }
        },
        /* 日期 */
        dateX: function (value, item) {
            var reg = /^(\d{4})[-\/](\d{1}|0\d{1}|1[0-2])([-\/](\d{1}|0\d{1}|[1-2][0-9]|3[0-1]))*$/;
            if (value && !reg.test(value)) {
                return verifyText.dateX;
            }
        },
        /* 身份证 */
        identityX: function (value, item) {
            var reg = /(^\d{15}$)|(^\d{17}(x|X|\d)$)/;
            if (value && !reg.test(value)) {
                return verifyText.identityX;
            }
        },
        /* 密码 */
        psw: function (value, item) {
            if (value && !/^[\S]{5,12}$/.test(value)) {
                return verifyText.psw
            }
        },
        /* 重复 */
        equalTo: function (value, item) {
            if (value != $($(item).attr('lay-equalTo')).val()) {
                var text = $(item).attr('lay-equalToText');
                return text ? text : verifyText.equalTo;
            }
        },
        /* 整数 */
        digits: function (value, item) {
            if (value && !(typeof value === 'number' && value % 1 === 0)) {
                return verifyText.digits;
            }
        },
        /* 正整数 */
        digitsP: function (value, item) {
            var reg = /^[1-9]\d*$/;
            if (value && !reg.test(value)) {
                return verifyText.digitsP;
            }
        },
        /* 正整数 */
        digitsP: function (value, item) {
            var reg = /^[1-9]\d*$/;
            if (value && !reg.test(value)) {
                return verifyText.digitsP;
            }
        },
        /* 正整数 */
        digitsP: function (value, item) {
            var reg = /^[1-9]\d*$/;
            if (value && !reg.test(value)) {
                return verifyText.digitsP;
            }
        },
        /* 负整数 */
        digitsN: function (value, item) {
            var reg = /^-[1-9]\d*$/;
            if (value && !reg.test(value)) {
                return verifyText.digitsN;
            }
        },
        /* 非负整数 */
        digitsPZ: function (value, item) {
            var reg = /^\d+$/;
            if (value && !reg.test(value)) {
                return verifyText.digitsPZ;
            }
        },
        /* 非正整数 */
        digitsNZ: function (value, item) {
            var reg = /^-[1-9]\d*|0/;
            if (value && !reg.test(value)) {
                return verifyText.digitsNZ;
            }
        },
        /* h5 */
        h5: function (value, item) {
            if (value) {
                var minlength = $(item).attr('minlength');
                var maxlength = $(item).attr('maxlength');
                var min = $(item).attr('min');
                var max = $(item).attr('max');
                if (minlength && value.length < minlength) {
                    return verifyText.minlength.replace(/{minlength}/g, minlength);
                }
                if (maxlength && value.length > maxlength) {
                    return verifyText.maxlength.replace(/{maxlength}/g, maxlength);
                }
                if (min && value * 1 < min * 1) {
                    return verifyText.min.replace(/{min}/g, min);
                }
                if (max && value * 1 > max * 1) {
                    return verifyText.max.replace(/{max}/g, max);
                }
            }
        }
    };

    var formVerify = {
        init: function () {
            form.verify(verifyList);
        },
        /* 赋值表单，解决top.layui.form.val无效的问题 */
        formVal: function (filter, object) {
            $('.layui-form[lay-filter="' + filter + '"]').each(function () {
                var $item = $(this);
                for (var f in object) {
                    var $elem = $item.find('[name="' + f + '"]');
                    if ($elem.length > 0) {
                        var type = $elem[0].type;
                        if (type == 'checkbox') {  // 如果为复选框
                            $elem[0].checked = value;
                        } else if (type == 'radio') { // 如果为单选框
                            $elem.each(function () {
                                if (this.value == object[f]) {
                                    this.checked = true;
                                }
                            });
                        } else { //其它类型的表单
                            $elem.val(object[f]);
                        }
                    }
                }
            });
            form.render(null, filter);
        }
    };

    formVerify.init();
    exports('formVerify', formVerify);
});
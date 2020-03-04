;(function ($) {
    /**
     * Author: lz
     *
     * @param {Object} options
     * url[string]:                 必须，上传地址
     * data[json|formData]:         必须，数据，格式不固定
     * extra[]:                     可选，额外参数，可用于回调中，格式不固定
     * callback[function]:          可选，上传后的回调函数
     */
    window.Hantepay = function (options) {
        //合并对象
        var data = $.extend(true, {
            //地址（必须）
            url: null,
            //数据（必须）
            data: null,
            //额外参数
            extra: null,
            //回调函数名
            callback: null
        }, options);
        if (data.url.length <= 0 || data.data.length <= 0) {
            alert('参数不完整，请传入url、data');
            return;
        }
        __request(data);
    };
    __request = function (data) {
        $.ajax({
            url: data.url,
            type: 'POST',
            data: data.data,
            cache: false,
            dataType:'text',
            timeout: 10000,
            success: function (info) {
                data.callback(info, data.extra);
            },
            error: function () {
                alert('支付失败');
            }
        });
    };
})(jQuery);
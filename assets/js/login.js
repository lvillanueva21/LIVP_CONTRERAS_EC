(function (window, $) {
    'use strict';

    function togglePass(selector, trigger) {
        var input = $(selector);
        if (input.length === 0) {
            return;
        }

        var isPass = input.attr('type') === 'password';
        input.attr('type', isPass ? 'text' : 'password');

        var icon = $(trigger).find('i');
        icon.toggleClass('fa-eye', !isPass);
        icon.toggleClass('fa-eye-slash', isPass);
    }

    $(function () {
        $(document).on('click', '[data-toggle-pass]', function () {
            var selector = $(this).attr('data-toggle-pass');
            togglePass(selector, this);
        });
    });
})(window, window.jQuery);

/**
 * Select2 Russian translation.
 *
 * @author  Uriy Efremochkin <efremochkin@uriy.me>
 */
(function ($) {
    "use strict";

    $.extend($.fn.select2.defaults, {
        formatNoMatches: function () { return "���������� �� �������"; },
        formatInputTooShort: function (input, min) { return "����������, ������� ���" + character(min - input.length); },
        formatInputTooLong: function (input, max) { return "����������, ������� ��" + character(input.length - max) + " ������"; },
        formatSelectionTooBig: function (limit) { return "�� ������ ������� �� ����� " + limit + " �������" + (limit%10 == 1 && limit%100 != 11 ? "�" : "��"); },
        formatLoadMore: function (pageNumber) { return "�������� �������"; },
        formatSearching: function () { return "�����"; }
    });

    function character (n) {
        return " " + n + " ������" + (n%10 < 5 && n%10 > 0 && (n%100 < 5 || n%100 > 20) ? n%10 > 1 ? "a" : "" : "��");
    }
})(jQuery);

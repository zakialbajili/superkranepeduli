(function ($) {
    $.extend({
        toDictionary: function (query) {
            var parms = {};
            var items = query.split("&"); // split
            for (var i = 0; i < items.length; i++) {
                var values = items[i].split("=");
                var key = decodeURIComponent(values.shift());
                var value = values.join("=")
                parms[key] = decodeURIComponent(value);
            }
            return (parms);
        }
    })
})(jQuery);

(function ($) {
    $.extend({
        toArrayDictionary: function (query) {
            var parms = {};
            var items = query; // split
            for (var i = 0; i < items.length; i++) {
                var value = items[i]['value'];
                var key = items[i]['name'];;
                //var value = values.join("=")
                parms[key] = decodeURIComponent(value);
            }
            return (parms);
        }
    })
})(jQuery);

(function ($) {
    $.fn.serializeFormJSON = function () {
        var o = [];
        $(this).find('tr').each(function () {
            var elements = $(this).find('input, textarea, select')
            // if (elements.size() > 0) {
            if (elements.length > 0) {
                var serialized = $(this).find('input, textarea, select').serialize();
                var item = $.toDictionary(serialized);
                o.push(item);
            }
        });
        return o;
    };
})(jQuery);

(function ($) {
    $.fn.serializeArrayFormJSON = function () {
        var o = [];
        // $(this).find('tr').each(function() {
        //     var elements = $(this).find('input, textarea, select');
        //     // if (elements.size() > 0) {
        //     if (elements.length > 0) {
        //         var serialized = $(this).find('input, textarea, select, label').serializeArray();
        //         var item = $.toArrayDictionary(serialized);
        //         o.push(item);
        //     }
        // });
        var elements = $(this).find('input, textarea, select');
        // if (elements.size() > 0) {
        if (elements.length > 0) {
            var serialized = $(this).find('input, textarea, select, label').serializeArray();
            var item = $.toArrayDictionary(serialized);
            o.push(item);
        }
        return o;
    };
    $.fn.serializeArrayTableJSON = function () {
        var o = [];
        $(this).find('tr').each(function () {
            var elements = $(this).find('input, textarea, select');
            // if (elements.size() > 0) {
            var item = {};
            if (elements.length > 0) {
                var serialized = $(this).find('input, textarea, select').serializeArray();
                item = $.toArrayDictionary(serialized);
            }

            var labelelements = $(this).find('label');
            if (labelelements.length > 0) {
                $(this).find('label').each(function () {
                    item[$(this).attr('id')] = $(this).text();
                });
            }
            o.push(item);
        });
        return o;
    };

    $.fn.JsonFromTable = function () {
        var tblhead = $(this).find('thead')
        var tblbody = $(this).find('tbody')
        var tblbodyCount = $(this).find('tbody>tr').length;
        var header = [];
        var JObjectArray = [];
        $.each($(tblhead).find('tr>th'), function (i, j) {
            header.push($(j).text())
        })
        $.each($(tblbody).find('tr'), function (key, value) {
            var jObject = {};
            for (var x = 0; x < header.length; x++) {
                jObject[header[x]] = $(this).find('td').eq(x).text()
            }
            JObjectArray.push(jObject)
        });
        var jsonObject = {};
        jsonObject["count"] = tblbodyCount
        jsonObject["value"] = JObjectArray;
        return jsonObject;
    };

})(jQuery);


const firebaseConfig = {
    apiKey: "AIzaSyAlTTlH-OtQzgbQmsfNewZBPjLKDiBTtIA",
    authDomain: "superkrane-apps.firebaseapp.com",
    projectId: "superkrane-apps",
    storageBucket: "superkrane-apps.appspot.com",
    messagingSenderId: "104363365082",
    appId: "1:104363365082:web:f14d89937b5e4b56e50be3"
};


// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();


messaging.onMessage(function (payload) {
    const title = payload.notification.title;
    const options = {
        body: payload.notification.body,
        icon: '/firebase-logo.png',
    };
    new Notification(title, options);
    toastr.info('Anda Mendapatkan 1 Notifikasi Baru');
    getUnreadNotif();
});

$(document).ready(function () {
    $(".select2").select2();
    $(".datepicker").datepicker({
        dateFormat: 'yy-mm-dd'
        , changeMonth: true
        , changeYear: true
        , showButtonPanel: true
    });

    // $('.numbermask').maskMoney({prefix:'Rp ', allowNegative: true, thousands:'.', decimal:','});

    // numbermasknoprefixdecimal();

    $('.daterange').daterangepicker({ timePicker: false, locale: { format: 'YYYY-MM-DD' } })

    $(".datepicker").attr("autocomplete", "off");

    $("input.numbermask").on("keydown", function (e) {
        // allow function keys and decimal separators
        if (
            // backspace, delete, tab, escape, enter, comma and .
            $.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 188, 190]) !== -1 ||
            // Ctrl/cmd+A, Ctrl/cmd+C, Ctrl/cmd+X
            ($.inArray(e.keyCode, [65, 67, 88]) !== -1 && (e.ctrlKey === true || e.metaKey === true)) ||
            // home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {

            return;
        }
        // block any non-number
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    startFCM();
    getUnreadNotif();
});

function numbermask() {
    $('.numbermask').maskMoney('mask');
}

function toTitleCase(str) {
    return str.toLowerCase().replace(/(^|\s)\w/g, (match) => match.toUpperCase());
}

const toSnakeCase = (str) => {
    return str.toLowerCase().replace(/\s+/g, '_');
}

function lpad(str, max) {
    str = str.toString();
    return str.length < max ? lpad("0" + str, max) : str;
}

function numbermasknoprefixdecimal() {
    $('.numbermasknoprefixdecimal').maskMoney({ prefix: '', allowNegative: true, thousands: '.', decimal: ',' });
}
/*****Ready function end*****/
function swal_data(swall_type, title_text, message_text, confirm_text, cancel_text) {
    var swall_data = {
        title: title_text
        , text: message_text
        , type: swall_type
        , showCancelButton: true
        , confirmButtonColor: "#e69a2a"
        , confirmButtonText: confirm_text
        , cancelButtonText: cancel_text
        , closeOnConfirm: false
        , closeOnCancel: false
    }
    return swall_data;
}

(function (exports) {
    function valOrFunction(val, ctx, args) {
        if (typeof val == "function") {
            return val.apply(ctx, args);
        }
        else {
            return val;
        }
    }

    function InvalidInputHelper(input, options) {
        input.setCustomValidity(valOrFunction(options.defaultText, window, [input]));

        function changeOrInput() {
            if (input.value == "") {
                input.setCustomValidity(valOrFunction(options.emptyText, window, [input]));
            }
            else {
                input.setCustomValidity("");
            }
        }

        function invalid() {
            if (input.value == "") {
                input.setCustomValidity(valOrFunction(options.emptyText, window, [input]));
            }
            else {
                input.setCustomValidity(valOrFunction(options.invalidText, window, [input]));
            }
        }
        input.addEventListener("change", changeOrInput);
        input.addEventListener("input", changeOrInput);
        input.addEventListener("invalid", invalid);
    }
    exports.InvalidInputHelper = InvalidInputHelper;
})(window);

function getSelectedText(elementId) {
    var elt = document.getElementById(elementId);
    if (elt.selectedIndex == -1) return null;
    return elt.options[elt.selectedIndex].text;
}

function toggle_visibility(id) {
    var e = document.getElementById(id);
    if (e.style.display == 'block') e.style.display = 'none';
    else e.style.display = 'block';
}

function validation(ctl) {
    var name = $("#" + ctl).val();
    var parent = $("#" + ctl).parent();
    if (name == '' || name == null) {
        $("#" + ctl + "_error").html(' This field is mandatory. ');
        parent.addClass('has-error');
        $("#" + ctl).focus();
        return false;
    }
    else {
        $("#" + ctl + "_error").html('');
        parent.removeClass('has-error');
        return true;
    }
};
function validationidn(ctl) {
    var name = $("#" + ctl).val();
    var parent = $("#" + ctl).parent();
    if (name == '' || name == null) {
        $("#" + ctl + "_error").html('Kolom ini harus diisi. ');
        parent.addClass('has-error');
        $("#" + ctl).focus();
        return false;
    }
    else {
        $("#" + ctl + "_error").html('');
        parent.removeClass('has-error');
        return true;
    }
};

function show_loading(msg_text, ele = "") {
    if (ele == "") {
        $.busyLoadFull("show", {
            background: "rgba(0, 51, 101, 0.83)"
            , fontawesome: "fa fa-spinner fa-spin fa-3x fa-fw"
            , text: msg_text
        });

    } else {
        $("#" + ele).busyLoad("show", {
            background: "rgba(0, 51, 101, 0.83)"
            , fontawesome: "fa fa-spinner fa-spin fa-3x fa-fw"
            , text: msg_text
        });
    }
}

function hide_loading(ele = "") {
    if (ele == "") {
        $.busyLoadFull('hide', {
            animation: "fade"
        });
    } else {
        $("#" + ele).busyLoad('hide', {
            animation: "fade"
        });
    }
}

function datatable_get_row_data(datab, obj) {
    var rowSelector;
    var li = datab.closest('li');
    if (li.length) {
        rowSelector = obj.cell(li).index().row;
    }
    else {
        rowSelector = datab.closest('tr');
    }
    return obj.row(rowSelector).data();
}

function formatDate(date) {
    var d = new Date(date)
        , month = '' + (d.getMonth() + 1)
        , day = '' + d.getDate()
        , year = d.getFullYear();
    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;
    return [year, month, day].join('-');
}

function replaceAll(str, term, replacement) {
    return str.replace(new RegExp(escapeRegExp(term), 'g'), replacement);
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function formatCurrency(value) {
    var num = 'Rp ' + addCommas(value);
    return num;
}

function addCommas(number) {
    number = number.toFixed(2);
    var parts = number.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    return parts.join(",");
}


function check_files(obj, ext, maxsize) {
    var fileExtensionAllowed = ext;
    var maxFileSize = 1024 * maxsize;
    var filename = obj.val();
    var re = /(?:\.([^.]+))?$/;
    var extension = re.exec(filename)[1];
    if (obj[0].files[0].size / 1000 > maxFileSize) {
        swal("Warning", "File not be greater than " + maxsize + " MB", "warning");
        obj.val("");
    }
    else {
        if ($.inArray(extension, fileExtensionAllowed) == -1) {
            swal("Warning", "Format not allowed", "warning");
            obj.val("");
        }
    }
}

function check_files_callback(obj, ext, maxsize) {
    var res = true;
    var fileExtensionAllowed = ext;
    var maxFileSize = 1024 * maxsize;
    var filename = obj.val();
    var re = /(?:\.([^.]+))?$/;
    var extension = re.exec(filename)[1];
    if (obj[0].files[0].size / 1000 > maxFileSize) {
        swal("Warning", "File not be greater than " + maxsize + " MB", "warning");
        obj.val("");
        res = false;
    }
    else {
        if ($.inArray(extension, fileExtensionAllowed) == -1) {
            swal("Warning", "Format not allowed", "warning");
            obj.val("");
            res = false;
        }
    }
    return res;
}

function initnumber(element) {
    element.attr("autocomplete", "off");
    element.on('input', function () {
        // Get the value from the input
        let inputValue = $(this).val();

        // Remove any non-digit characters from the input value
        // let numericValue = inputValue.replace(/\D/g, '');
        let numericValue = inputValue.replace(/[^\d,]+/g, '');

        // // Format the numeric value with delimiters
        // let formattedValue = Number(numericValue).toLocaleString('id-ID');
        // // if (isNaN(formattedValue)){
        // //     $(this).val(numericValue);
        // // }else{
        // //     $(this).val(formattedValue);
        // // }
        // $(this).val(formattedValue);

        var parts = numericValue.toString().split(",");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        $(this).val(parts.join(","));

    });
}


function clearnumbermask(val) {
    val = replaceAll(val, 'Rp', '');
    val = replaceAll(val, '.', '');
    val = replaceAll(val, ',', '.');
    val = replaceAll(val, '+', '');
    val = replaceAll(val, ' ', '');
    val = val || 0
    return parseFloat(val);
}

function isValidDateyyyymmdd(dateString) {
    var regEx = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateString.match(regEx)) return false;  // Invalid format
    var d = new Date(dateString);
    var dNum = d.getTime();
    if (!dNum && dNum !== 0) return false; // NaN value, Invalid date
    return d.toISOString().slice(0, 10) === dateString;
}

function calcWorkingHours(scan_masuk, scan_keluar, istirahat_normal, istirahat_lembur, date) {
    if (scan_keluar.val() <= scan_masuk.val()) {
        scan_masuk = new Date(formatDate(date) + " " + scan_masuk.val());
        scan_keluar = new Date(formatDate(date) + " " + scan_keluar.val());
        scan_keluar.setDate(scan_keluar.getDate() + 1);
        if (istirahat_normal.val()) {
            var jam = istirahat_normal.val().substring(0, 2);
            var menit = istirahat_normal.val().substring(3);
            var menit = parseInt(menit) / 60;
            var total = parseInt(jam) + menit;
            istirahat_normal = parseFloat(total);

        } else {
            istirahat_normal = 0;
        }
        if (istirahat_lembur.val()) {
            var jam = istirahat_lembur.val().substring(0, 2);
            var menit = istirahat_lembur.val().substring(3);
            var menit = parseInt(menit) / 60;
            var total = parseInt(jam) + menit;
            istirahat_lembur = parseFloat(total);
        } else {
            istirahat_lembur = 0;
        }

        var istirahat1 = istirahat_normal * 60 * 60 * 1000;
        var istirahat2 = istirahat_lembur * 60 * 60 * 1000;
        var diff_time = new Date(scan_keluar - scan_masuk);
        var jumlahjamkerja = new Date((diff_time - istirahat1 - istirahat2));
        return jumlahjamkerja;
    } else {
        scan_masuk = new Date(formatDate(date) + " " + scan_masuk.val());
        scan_keluar = new Date(formatDate(date) + " " + scan_keluar.val());
        if (istirahat_normal.val()) {
            var jam = istirahat_normal.val().substring(0, 2);
            var menit = istirahat_normal.val().substring(3);
            var menit = parseInt(menit) / 60;
            var total = parseInt(jam) + menit;
            istirahat_normal = parseFloat(total);

        } else {
            istirahat_normal = 0;
        }
        if (istirahat_lembur.val()) {
            var jam = istirahat_lembur.val().substring(0, 2);
            var menit = istirahat_lembur.val().substring(3);
            var menit = parseInt(menit) / 60;
            var total = parseInt(jam) + menit;
            istirahat_lembur = parseFloat(total);
        } else {
            istirahat_lembur = 0;
        }
        var istirahat1 = istirahat_normal * 60 * 60 * 1000;
        var istirahat2 = istirahat_lembur * 60 * 60 * 1000;
        var diff_time = new Date(scan_keluar - scan_masuk);
        var jumlahjamkerja = new Date((diff_time - istirahat1 - istirahat2));
        return jumlahjamkerja;
    }
}
function calcOvertime(scan_masuk, scan_keluar, istirahat_normal, istirahat_lembur, date, lokasi, is_holiday, isWeekendPool, isWeekendProject, isStaff) {
    var jumlahjamkerja = calcWorkingHours(scan_masuk, scan_keluar, istirahat_normal, istirahat_lembur, date)
    var jamkerjareg = 8 * 60 * 60 * 1000;
    if (lokasi.match("Pool")) {
        if (is_holiday == "LIBURNAS") {
            var x = new Date(jumlahjamkerja);
            currentHours = x.getUTCHours();
            currentHours = ("0" + currentHours).slice(-2);
            currentMinutes = x.getUTCMinutes();
            currentMinutes = ("0" + currentMinutes).slice(-2);
            var lembur = (currentHours + ':' + currentMinutes);
        } else {
            if (isWeekendPool == true) {
                if (isStaff == "1") {
                    var x = new Date(jumlahjamkerja);
                    currentHours = x.getUTCHours();
                    currentHours = ("0" + currentHours).slice(-2);
                    currentMinutes = x.getUTCMinutes();
                    currentMinutes = ("0" + currentMinutes).slice(-2);
                    var lembur = (currentHours + ':' + currentMinutes);
                } else {
                    var x = new Date(jumlahjamkerja * 2);
                    currentHours = x.getUTCHours();
                    currentHours = ("0" + currentHours).slice(-2);
                    currentMinutes = x.getUTCMinutes();
                    currentMinutes = ("0" + currentMinutes).slice(-2);

                }
            } else {
                var x = new Date(jumlahjamkerja - jamkerjareg);
                currentHours = x.getUTCHours();
                currentHours = ("0" + currentHours).slice(-2);
                currentMinutes = x.getUTCMinutes();
                currentMinutes = ("0" + currentMinutes).slice(-2);
                var g = (currentHours + ':' + currentMinutes);
                if (g == "00:00") {
                    lembur = "";
                } else {
                    if (x.getUTCHours() > 16) {
                        lembur = "";
                    } else {
                        lembur = g;
                    }
                }
            }
        }
    } else if (lokasi.match("Project")) {
        //Project
        if ($('#fk_tipeamandemem_id').val()) {
            var x = new Date(jumlahjamkerja - jamkerjareg);
            currentHours = x.getUTCHours();
            currentHours = ("0" + currentHours).slice(-2);
            currentMinutes = x.getUTCMinutes();
            currentMinutes = ("0" + currentMinutes).slice(-2);
            var g = (currentHours + ':' + currentMinutes);
            if (g == "00:00") {
                lembur = "";
            } else {
                if (x.getUTCHours() > 16) {
                    lembur = "";
                } else {
                    lembur = g;
                }
            }
        } else {
            if (isWeekendProject == true || is_holiday == "LIBURNAS") {
                var x = new Date(jumlahjamkerja);
                currentHours = x.getUTCHours();
                currentHours = ("0" + currentHours).slice(-2);
                currentMinutes = x.getUTCMinutes();
                currentMinutes = ("0" + currentMinutes).slice(-2);
                var lembur = (currentHours + ':' + currentMinutes);
            } else {
                var x = new Date(jumlahjamkerja - jamkerjareg);
                currentHours = x.getUTCHours();
                currentHours = ("0" + currentHours).slice(-2);
                currentMinutes = x.getUTCMinutes();
                currentMinutes = ("0" + currentMinutes).slice(-2);
                var g = (currentHours + ':' + currentMinutes);
                if (g == "00:00") {
                    lembur = "";
                } else {
                    if (x.getUTCHours() > 16) {
                        lembur = "";
                    } else {
                        lembur = g;
                    }
                }
            }
        }
    }

    return lembur;


}
function dataTableBoilerPlate(id, route, data, order = [[0, 'asc']], columnDefs = []) {
    let table = $(`#${id}`);
    if ($.fn.DataTable.isDataTable(`#${id}`)) {
        return table.DataTable();
    }
    let dt = table.DataTable({
        processing: true,
        iDisplayLength: 10,
        responsive: false,
        scrollX: true,
        scrollY: 350,
        scrollCollapse: true,
        serverSide: true,
        autoWidth: false,
        columnDefs: columnDefs,
        ajax: {
            url: route,
            type: "POST",
            data: data
        },
        order: order
    });
    let timer;
    // Add debounce to prevent many request by search trigger
    $(`#${id}_filter input`)
        .off('.DT')
        .on('keyup', function () {
            clearTimeout(timer);
            let value = this.value;
            timer = setTimeout(function () {
                dt.search(value).draw();
            }, 1000);
        });
    return dt;
}

/**
 * Boilerplate untuk membuat Chart.js chart yang di-load via AJAX.
 * Otomatis manage instance chart — destroy sebelumnya jika ada, render yang baru setelah AJAX success.
 *
 * @param {string|Object}   opts.canvasId          ID elemen <canvas> (tanpa #)
 * @param {string}          opts.type              Tipe chart: 'bar', 'doughnut', 'pie', 'line', etc.
 * @param {string}          opts.url               URL endpoint AJAX yg mengembalikan JSON { labels, data, colors? }
 * @param {Object}          opts.options           (optional) Opsi Chart.js tambahan, akan di-merge dgn default
 * @param {Object}          opts.datasets          (optional) Kustom datasets untuk chart selain doughnut/pie — default [{ label, data, backgroundColor, ... }]
 * @param {Function}        opts.onBeforeLoad      (optional) Callback sebelum AJAX, menerima jQuery deferred
 * @param {Function}        opts.onAfterLoad       (optional) Callback setelah chart digambar, menerima (chartInstance)
 * @param {Function}        opts.transformResponse (optional) Callback untuk transform response JSON sebelum digambar
 * @param {Object}          opts.ajaxParams        (optional) Parameter tambahan untuk $.ajax ({ data, headers, ... })
 * @returns {Chart|null} Instance chart, atau null jika gagal.
 *
 * @example
 * // Simple doughnut
 * chartBoilerPlate({ canvasId: 'chartStatus', type: 'doughnut', url: '/admin/dashboard/chartstatus' });
 *
 * @example
 * // Bar chart with year filter callback
 * chartBoilerPlate({
 *     canvasId: 'chartBulan',
 *     type: 'bar',
 *     url: '/admin/dashboard/chartcountreport',
 *     ajaxParams: { data: { tahun: 2026 } },
 *     options: {
 *         scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
 *         plugins: { legend: { display: false } }
 *     }
 * });
 */
var _chartInstances = {};

function chartBoilerPlate(opts) {
    if (!opts || !opts.canvasId || !opts.type || !opts.url) {
        console.error('chartBoilerPlate: canvasId, type, dan url wajib diisi.');
        return null;
    }
    var canvas = document.getElementById(opts.canvasId);
    if (!canvas) {
        console.error('chartBoilerPlate: Canvas #' + opts.canvasId + ' tidak ditemukan.');
        return null;
    }
    var ctx = canvas.getContext('2d');

    // Destroy existing instance
    if (_chartInstances[opts.canvasId]) {
        _chartInstances[opts.canvasId].destroy();
        _chartInstances[opts.canvasId] = null;
    }

    // Default options per type
    var defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
    };
    // Merge user options over defaults
    var mergedOptions = $.extend(true, {}, defaultOptions, opts.options || {});

    var ajaxParams = $.extend(true, {
        url: opts.url,
        type: 'GET',
        dataType: 'json',
    }, opts.ajaxParams || {});

    // Optional: custom dataset callback for types other than doughnut/pie
    if (typeof opts.onBeforeLoad === 'function') {
        opts.onBeforeLoad(ajaxParams);
    }

    $.ajax(ajaxParams).done(function (res) {
        // Optional: transform raw response
        if (typeof opts.transformResponse === 'function') {
            res = opts.transformResponse(res);
        }

        var chartData;
        if (opts.datasets) {
            // Fully custom datasets — use as-is
            chartData = opts.datasets;
        } else if (opts.type === 'doughnut' || opts.type === 'pie') {
            chartData = {
                labels: res.labels || [],
                datasets: [{
                    data: res.data || [],
                    backgroundColor: res.colors || undefined,
                    borderWidth: 2,
                }]
            };
        } else {
            // Bar / line / radar — one dataset from { labels, data }
            chartData = {
                labels: res.labels || [],
                datasets: [{
                    label: (opts.options && opts.options.datasetLabel) ? opts.options.datasetLabel : 'Data',
                    data: res.data || [],
                    backgroundColor: res.colors || undefined,
                    borderColor: res.borderColors || undefined,
                    borderWidth: 1,
                    borderRadius: opts.type === 'bar' ? 4 : 0,
                }]
            };
        }

        _chartInstances[opts.canvasId] = new Chart(ctx, {
            type: opts.type,
            data: chartData,
            options: mergedOptions
        });

        // Store encryptedParams on chart instance untuk onClick redirect
        if (res.encryptedParams) {
            _chartInstances[opts.canvasId]._encryptedParams = res.encryptedParams;
        }

        if (typeof opts.onAfterLoad === 'function') {
            opts.onAfterLoad(_chartInstances[opts.canvasId]);
        }
    }).fail(function (jqXHR, textStatus) {
        console.error('chartBoilerPlate: Gagal load chart #' + opts.canvasId, textStatus);
    });

    // Return helper to reload later
    return {
        reload: function (newOpts) {
            if (newOpts) {
                chartBoilerPlate($.extend({}, opts, newOpts));
            } else {
                chartBoilerPlate(opts);
            }
        }
    };
}
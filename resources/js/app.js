/**
 * Input mask helpers called from Alpine x-on:input handlers.
 * No external plugin needed — plain DOM manipulation.
 */
window.maskCpf = function (el) {
    const pos = el.selectionStart;
    const prev = el.value;
    let d = el.value.replace(/\D/g, '').slice(0, 11);
    if (d.length > 9) d = d.slice(0, 3) + '.' + d.slice(3, 6) + '.' + d.slice(6, 9) + '-' + d.slice(9);
    else if (d.length > 6) d = d.slice(0, 3) + '.' + d.slice(3, 6) + '.' + d.slice(6);
    else if (d.length > 3) d = d.slice(0, 3) + '.' + d.slice(3);
    el.value = d;
    const diff = d.length - prev.length;
    const newPos = Math.max(0, pos + diff);
    el.setSelectionRange(newPos, newPos);
};

window.maskPhone = function (el) {
    const pos = el.selectionStart;
    const prev = el.value;
    let d = el.value.replace(/\D/g, '').slice(0, 11);
    if (d.length > 10) d = '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
    else if (d.length > 6) d = '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
    else if (d.length > 2) d = '(' + d.slice(0, 2) + ') ' + d.slice(2);
    else if (d.length > 0) d = '(' + d;
    el.value = d;
    const diff = d.length - prev.length;
    const newPos = Math.max(0, pos + diff);
    el.setSelectionRange(newPos, newPos);
};

window.maskDate = function (el) {
    const pos = el.selectionStart;
    const prev = el.value;
    let d = el.value.replace(/\D/g, '').slice(0, 8);
    if (d.length > 4) d = d.slice(0, 2) + '/' + d.slice(2, 4) + '/' + d.slice(4);
    else if (d.length > 2) d = d.slice(0, 2) + '/' + d.slice(2);
    el.value = d;
    const diff = d.length - prev.length;
    const newPos = Math.max(0, pos + diff);
    el.setSelectionRange(newPos, newPos);
};

window.maskCep = function (el) {
    const pos = el.selectionStart;
    const prev = el.value;
    let d = el.value.replace(/\D/g, '').slice(0, 8);
    if (d.length > 5) d = d.slice(0, 5) + '-' + d.slice(5);
    el.value = d;
    const diff = d.length - prev.length;
    const newPos = Math.max(0, pos + diff);
    el.setSelectionRange(newPos, newPos);
};

window.maskCurrency = function (el) {
    const pos = el.selectionStart;
    const raw = el.value.replace(/\D/g, '');
    if (!raw) {
        el.value = '';
        return;
    }
    const num = (parseInt(raw, 10) / 100).toFixed(2);
    const [intPart, decPart] = num.split('.');
    const formatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + decPart;
    el.value = formatted;
    // Keep cursor near the end
    const newPos = el.value.length - (el.value.length - pos > 3 ? 0 : 0);
    el.setSelectionRange(newPos, newPos);
};

window.maskLicensePlate = function (el) {
    const pos = el.selectionStart;
    const prev = el.value;
    const raw = el.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 7);
    let d;
    if (raw.length <= 3) {
        d = raw;
    } else if (raw.length >= 5 && /[A-Z]/.test(raw[4])) {
        // Mercosul: AAA#A## (no separator)
        d = raw;
    } else {
        // Old format: AAA-####
        d = raw.slice(0, 3) + '-' + raw.slice(3);
    }
    el.value = d;
    const diff = d.length - prev.length;
    const newPos = Math.max(0, pos + diff);
    el.setSelectionRange(newPos, newPos);
};

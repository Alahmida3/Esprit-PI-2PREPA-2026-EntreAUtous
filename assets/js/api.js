/**
 * api.js — Fonctions AJAX communes (front + back)
 * Dépend de : const API_URL = '...' défini avant ce script
 */
'use strict';

/**
 * Requête GET → controller?action=xxx&param=val
 */
async function apiGet(action, params = {}) {
    try {
        const qs = new URLSearchParams({ action, ...params }).toString();
        const res = await fetch(`${API_URL}?${qs}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return await res.json();
    } catch (e) {
        console.error('[apiGet]', action, e);
        return { success: false, message: 'Erreur réseau : ' + e.message };
    }
}

/**
 * Requête POST → controller?action=xxx  body=FormData
 */
async function apiPost(action, data = {}) {
    try {
        const fd = new FormData();
        Object.entries(data).forEach(([k, v]) => fd.append(k, v));
        const res = await fetch(`${API_URL}?action=${encodeURIComponent(action)}`, {
            method: 'POST',
            body: fd,
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return await res.json();
    } catch (e) {
        console.error('[apiPost]', action, e);
        return { success: false, message: 'Erreur réseau : ' + e.message };
    }
}

/**
 * Toast notification
 * @param {string} msg
 * @param {'success'|'error'|'info'} type
 */
function toast(msg, type = 'info') {
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', info: 'bi-info-circle-fill' };
    const container = document.getElementById('toast-container');
    if (!container) return;
    const el = document.createElement('div');
    el.className = `toast-item toast-${type}`;
    el.innerHTML = `<i class="bi ${icons[type] || icons.info} toast-icon"></i><span>${msg}</span>`;
    container.appendChild(el);
    setTimeout(() => {
        el.classList.add('toast-hide');
        setTimeout(() => el.remove(), 450);
    }, 3200);
}
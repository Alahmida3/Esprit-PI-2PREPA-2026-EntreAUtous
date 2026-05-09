/**
 * tracking.js — Entre AuTout
 * Inclure dans toutes les pages front avant </body>
 *   <script src="../../assets/front/js/tracking.js"></script>
 */
(function () {
    'use strict';

    var ENDPOINT_TRACK   = '/autout/controller/TrackingController.php';
    var ENDPOINT_JOURNEY = '/autout/controller/JourneyController.php';

    var PAGE = (function () {
        var parts = window.location.pathname.split('/').filter(Boolean);
        return (parts[parts.length - 1] || 'home').replace('.php', '');
    })();

    var startTime = Date.now();
    var sent      = false;

    function sendTrack(duration) {
        if (sent) return;
        sent = true;
        var payload = JSON.stringify({ action: 'track', page: PAGE, duration: Math.round(duration) });
        if (navigator.sendBeacon) {
            navigator.sendBeacon(ENDPOINT_TRACK, new Blob([payload], { type: 'application/json' }));
        } else {
            fetch(ENDPOINT_TRACK, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: payload, keepalive: true }).catch(function () {});
        }
        try {
            var hist = JSON.parse(localStorage.getItem('at_hist') || '[]');
            hist.unshift({ page: PAGE, dur: Math.round(duration), ts: Date.now() });
            localStorage.setItem('at_hist', JSON.stringify(hist.slice(0, 60)));
        } catch (e) {}
    }

    window.addEventListener('beforeunload', function () {
        sendTrack((Date.now() - startTime) / 1000);
    });

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            sendTrack((Date.now() - startTime) / 1000);
        } else {
            sent      = false;
            startTime = Date.now();
        }
    });

    function markStep(stepKey, cb) {
        fetch(ENDPOINT_JOURNEY, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'mark_step', step: stepKey })
        })
        .then(function (r) { return r.json(); })
        .then(function (d) { if (cb) cb(d); })
        .catch(function () {});
    }

    window.AT_Tracker = {
        getPage:    function () { return PAGE; },
        getElapsed: function () { return Math.round((Date.now() - startTime) / 1000); },
        flush:      function () { sendTrack((Date.now() - startTime) / 1000); },
        markStep:   markStep,
        getHistory: function () {
            try { return JSON.parse(localStorage.getItem('at_hist') || '[]'); } catch (e) { return []; }
        }
    };

})();
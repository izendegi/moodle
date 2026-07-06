define([], function () {
    window.requirejs.config({
        paths: {
            "interact": M.cfg.wwwroot + "/blocks/smowl/lib/interact.min"
        },
        shim: {
            'interact': { exports: 'fullCalendar' }
        }
    });
});
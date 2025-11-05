define(
    ['dhtmlx-gantt'],
    function (dhx) {
        "use strict";
        const gantt = dhx.gantt;

        /** @see https://docs.dhtmlx.com/gantt/desktop__guides.html */

        gantt.init('production_orders_gantt');
        gantt.parse({
            tasks: [
                { id: 1, text: "Project #2", start_date: "01-04-2025", duration: 18,
                    progress: 0.4/*, open: true*/ },
                { id: 2, text: "Task #1", start_date: "02-04-2025", duration: 8,
                    progress: 0.6/*, parent: 1*/ },
                { id: 3, text: "Task #2", start_date: "11-04-2025", duration: 8,
                    progress: 0.6/*, parent: 1*/ }
            ]/*,
            links: [
                { id: 1, source: 1, target: 2, type: "1" },
                { id: 2, source: 2, target: 3, type: "0" }
            ]*/
        });
    }
);

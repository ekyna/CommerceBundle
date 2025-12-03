define(
    ['dhtmlx-gantt', 'routing'],
    function (dhx, Router) {
        "use strict";

        const gantt = dhx.gantt;

        /** @see https://docs.dhtmlx.com/gantt/desktop__guides.html */

        gantt.config.columns = [
            {name: "text", label: "Task name", width: "*", tree: true},
            {name: "start_date", label: "Start time", align: "center"},
            {name: "duration", label: "Duration", align: "center"},
            //{ name: "add",        label: "",           width: 44 }
        ];
        gantt.config.date_format = "%Y-%m-%d %H:%i";
        // Disable editing features
        gantt.config.drag_links = false;
        gantt.config.drag_progress = false;

        // Lightbox / editor
        function configureLightbox(gantt) {
            // Buttons
            // https://docs.dhtmlx.com/gantt/desktop__custom_button.html
            gantt.config.buttons_right = ["read_button"];
            gantt.locale.labels.read_button = "Details";
            gantt.attachEvent("onLightboxButton", function(button_id, node, e){
                if(button_id === "read_button") {
                    let id = gantt.getState().lightbox;
                    window.location.href = Router.generate('admin_ekyna_commerce_production_order_read', {
                        productionOrderId: id,
                    });
                }
            });

            // Form
            gantt.config.lightbox.sections = [
                //{name:"description", height:38, map_to:"text", type:"textarea",focus:true},
                //{name:"priority", height:22, map_to:"priority",type:"select",options:opts},
                {name:"template", height:16, type:"template", map_to:"details"},
                {name:"time", height:72, type:"duration", map_to:"auto"},
            ];
            gantt.locale.labels.section_template = "Details";
            gantt.attachEvent("onBeforeLightbox", function(id) {
                let task = gantt.getTask(id);
                task.details =
                    //"<span id='title1'>Holders: </span>"+ task.users +
                    "<span id='title2'>Progress: </span>"+ task.progress*100 +" %";
                return true;
            });
        }

        // Mousewheel / zoom
        function configureZoom(gantt) {
            /* view-source:https://docs.dhtmlx.com/gantt/samples/03_scales/14_scale_zoom_by_wheelmouse.html */
            var hourToStr = gantt.date.date_to_str("%H:%i");
            var hourRangeFormat = function (step) {
                return function (date) {
                    var intervalEnd = new Date(gantt.date.add(date, step, "hour") - 1)
                    return hourToStr(date) + " - " + hourToStr(intervalEnd);
                };
            };

            gantt.config.min_column_width = 80;
            var zoomConfig = {
                minColumnWidth: 80,
                maxColumnWidth: 150,
                levels: [
                    [
                        {unit: "month", format: "%M %Y", step: 1},
                        {
                            unit: "week", step: 1, format: function (date) {
                                let dateToStr = gantt.date.date_to_str("%d %M");
                                let endDate = gantt.date.add(date, 7 - date.getDay(), "day");
                                let weekNum = gantt.date.date_to_str("%W")(date);
                                return "Week #" + weekNum + ", " + dateToStr(date) + " - " + dateToStr(endDate);
                            }
                        }
                    ],
                    [
                        {unit: "month", format: "%M %Y", step: 1},
                        {unit: "day", format: "%d %M", step: 1}
                    ],
                    [
                        {unit: "day", format: "%d %M", step: 1},
                        {unit: "hour", format: hourRangeFormat(12), step: 12}
                    ],
                    [
                        {unit: "day", format: "%d %M", step: 1},
                        {unit: "hour", format: hourRangeFormat(6), step: 6}
                    ],
                    [
                        {unit: "day", format: "%d %M", step: 1},
                        {unit: "hour", format: "%H:%i", step: 1}
                    ]
                ],
                // startDate: new Date(2023, 02, 27),
                // endDate: new Date(2023, 03, 20),
                useKey: "ctrlKey",
                trigger: "wheel",
                element: function () {
                    return gantt.$root.querySelector(".gantt_task");
                }
            }

            gantt.ext.zoom.init(zoomConfig);
        }

        // Configure API
        function configureDataProcessor(gantt) {
            const dp = gantt.createDataProcessor({
                task: {
                    create: (data) => {},
                    update: (data, id) => gantt.ajax.post({
                        headers: {
                            "Content-Type": "application/json"
                        },
                        url: Router.generate('ekyna_commerce_api_production_order_update', {id: id}),
                        data: JSON.stringify(data)
                    }),
                    delete: (id) => {}
                },
                link: {
                    create: (data) => {},
                    update: (data, id) => {},
                    delete: (id) => {}
                }
            });

            dp.init(gantt);
        }

        configureLightbox(gantt);
        configureZoom(gantt);
        configureDataProcessor(gantt);

        gantt.init('production_orders_gantt');

        gantt.load(Router.generate('ekyna_commerce_api_production_order_list'));

        /*gantt.parse({
            tasks: [
                { id: 1, text: "Project #2", start_date: "01-04-2025", duration: 18,
                    progress: 0.4/!*, open: true*!/ },
                { id: 2, text: "Task #1", start_date: "02-04-2025", duration: 8,
                    progress: 0.6/!*, parent: 1*!/ },
                { id: 3, text: "Task #2", start_date: "11-04-2025", duration: 8,
                    progress: 0.6/!*, parent: 1*!/ }
            ]/!*,
            links: [
                { id: 1, source: 1, target: 2, type: "1" },
                { id: 2, source: 2, target: 3, type: "0" }
            ]*!/
        });*/
    }
);

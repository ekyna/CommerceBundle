define("ekyna-commerce/templates", ["twig"], function(Twig) {
var templates = {};
templates["widget.html.twig"] = Twig.twig({ data: [{"type":"raw","value":"<"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"tag","match":["tag"]}]},{"type":"raw","value":" class=\"dropdown"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"class","match":["class"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":" "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"class","match":["class"]}]}]}},{"type":"raw","value":"\" id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"id","match":["id"]}]},{"type":"raw","value":"\">\n    <a href=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"href","match":["href"]}]},{"type":"raw","value":"\" title=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"title","match":["title"]}]},{"type":"raw","value":"\" class=\"dropdown-toggle\"\n       data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">\n        "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"icon","match":["icon"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"<span class=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"icon","match":["icon"]}]},{"type":"raw","value":"\"></span>"}]}},{"type":"raw","value":"        "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"label","match":["label"]},{"type":"Twig.expression.type.filter","value":"raw","match":["|raw","raw"]}]},{"type":"raw","value":"\n    </a>\n    <div class=\"dropdown-menu dropdown-menu-right\"></div>\n</"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"tag","match":["tag"]}]},{"type":"raw","value":">"}] });
templates["stock_unit_rows.html.twig"] = Twig.twig({ data: [{"type":"logic","token":{"type":"Twig.logic.type.spaceless","match":["spaceless"],"output":[{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"stock_unit","expression":[{"type":"Twig.expression.type.variable","value":"stock_units","match":["stock_units"]}],"output":[{"type":"raw","value":"    "},{"type":"logic","token":{"type":"Twig.logic.type.set","key":"stock_unit_id","expression":[{"type":"Twig.expression.type.variable","value":"prefix","match":["prefix"]},{"type":"Twig.expression.type.string","value":"_"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"},{"type":"Twig.expression.type.variable","value":"loop","match":["loop"]},{"type":"Twig.expression.type.key.period","key":"index0"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]}},{"type":"raw","value":"    <tbody>\n    <tr id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]}]},{"type":"raw","value":"\">\n        <td id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_state"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"state_badge"},{"type":"Twig.expression.type.filter","value":"raw","match":["|raw","raw"]}]},{"type":"raw","value":"</td>\n        <td id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_geocodes"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"geocodes"}]},{"type":"raw","value":"</td>\n        <td id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_orderedQuantity"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\" class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"ordered"}]},{"type":"raw","value":"</td>\n        <td id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_receivedQuantity"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\" class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"received"}]},{"type":"raw","value":"</td>\n        <td id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_adjustedQuantity"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\" class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"adjusted"}]},{"type":"raw","value":"</td>\n        <td id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_soldQuantity"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\" class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"sold"}]},{"type":"raw","value":"</td>\n        <td id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_shippedQuantity"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\" class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"shipped"}]},{"type":"raw","value":"</td>\n        <td id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_estimatedDateOfArrival"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"eda"},{"type":"Twig.expression.type.filter","value":"raw","match":["|raw","raw"]}]},{"type":"raw","value":"</td>\n        <td id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_netPrice"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\" class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"net_price"}]},{"type":"raw","value":"</td>\n        <td class=\"actions\">\n        "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"action","expression":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"actions"}],"output":[{"type":"raw","value":"            <a href=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"href"}]},{"type":"raw","value":"\" class=\"btn btn-xs btn-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"theme"}]},{"type":"raw","value":"\""},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"modal"}],"output":[{"type":"raw","value":" data-stock-unit-modal data-rel=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]}]},{"type":"raw","value":"\""}]}},{"type":"raw","value":">\n                "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"label"},{"type":"Twig.expression.type.filter","value":"raw","match":["|raw","raw"]}]},{"type":"raw","value":"\n            </a>\n        "}]}},{"type":"raw","value":"        "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"adjustments"},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"            <a href=\"javascript: void(0)\"\n               class=\"btn btn-xs btn-default\"\n               data-toggle-details=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_adjustments"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\">\n                <i class=\"fa fa-info-circle\"></i>\n            </a>\n        "}]}},{"type":"raw","value":"        "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"assignments"},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"            <a href=\"javascript: void(0)\"\n               class=\"btn btn-xs btn-default\"\n               data-toggle-details=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_assignments"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\">\n                <i class=\"fa fa-tasks\"></i>\n            </a>\n        "}]}},{"type":"raw","value":"        </td>\n    </tr>\n    </tbody>\n    "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"adjustments"},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"    <tbody id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_adjustments"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\" style=\"display: none\">\n    "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"adjustment","expression":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"adjustments"}],"output":[{"type":"raw","value":"        <tr>\n            <td colspan=\"4\" class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"adjustment","match":["adjustment"]},{"type":"Twig.expression.type.key.period","key":"type_badge"},{"type":"Twig.expression.type.filter","value":"raw","match":["|raw","raw"]}]},{"type":"raw","value":"</td>\n            <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"adjustment","match":["adjustment"]},{"type":"Twig.expression.type.key.period","key":"quantity"}]},{"type":"raw","value":"</td>\n            <td colspan=\"2\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"adjustment","match":["adjustment"]},{"type":"Twig.expression.type.key.period","key":"reason_label"}]},{"type":"raw","value":"</td>\n            <td colspan=\"2\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"adjustment","match":["adjustment"]},{"type":"Twig.expression.type.key.period","key":"note"}]},{"type":"raw","value":"</td>\n            <td class=\"actions\">\n            "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"action","expression":[{"type":"Twig.expression.type.variable","value":"adjustment","match":["adjustment"]},{"type":"Twig.expression.type.key.period","key":"actions"}],"output":[{"type":"raw","value":"                <a href=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"href"}]},{"type":"raw","value":"\" class=\"btn btn-xs btn-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"theme"}]},{"type":"raw","value":"\""},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"modal"}],"output":[{"type":"raw","value":" data-stock-unit-modal data-rel=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]}]},{"type":"raw","value":"\""}]}},{"type":"raw","value":">\n                    "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"label"},{"type":"Twig.expression.type.filter","value":"raw","match":["|raw","raw"]}]},{"type":"raw","value":"\n                </a>\n            "}]}},{"type":"raw","value":"            </td>\n        </tr>\n    "}]}},{"type":"raw","value":"    </tbody>\n    "}]}},{"type":"raw","value":"    "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"assignments"},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"    <tbody id=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]},{"type":"Twig.expression.type.string","value":"_assignments"},{"type":"Twig.expression.type.operator.binary","value":"~","precidence":6,"associativity":"leftToRight","operator":"~"}]},{"type":"raw","value":"\" style=\"display: none\">\n    "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"assignment","expression":[{"type":"Twig.expression.type.variable","value":"stock_unit","match":["stock_unit"]},{"type":"Twig.expression.type.key.period","key":"assignments"}],"output":[{"type":"raw","value":"        <tr "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"assignment","match":["assignment"]},{"type":"Twig.expression.type.key.period","key":"order_id"},{"type":"Twig.expression.type.test","filter":"defined"}],"output":[{"type":"raw","value":"data-summary=\""},{"type":"output","stack":[{"type":"Twig.expression.type.object.start","value":"{","match":["{"]},{"type":"Twig.expression.type.operator.binary","value":":","precidence":16,"associativity":"rightToLeft","operator":":","key":"route"},{"type":"Twig.expression.type.string","value":"ekyna_commerce_order_admin_summary"},{"type":"Twig.expression.type.comma"},{"type":"Twig.expression.type.operator.binary","value":":","precidence":16,"associativity":"rightToLeft","operator":":","key":"parameters"},{"type":"Twig.expression.type.object.start","value":"{","match":["{"]},{"type":"Twig.expression.type.operator.binary","value":":","precidence":16,"associativity":"rightToLeft","operator":":","key":"orderId"},{"type":"Twig.expression.type.variable","value":"assignment","match":["assignment"]},{"type":"Twig.expression.type.key.period","key":"order_id"},{"type":"Twig.expression.type.object.start","value":"{","match":["{"]},{"type":"Twig.expression.type.object.start","value":"{","match":["{"]}]},{"type":"raw","value":"|json_encode }}\""}]}},{"type":"raw","value":">\n            <td colspan=\"5\"></td>\n            <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"assignment","match":["assignment"]},{"type":"Twig.expression.type.key.period","key":"sold"}]},{"type":"raw","value":"</td>\n            <td class=\"text-right\">"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"assignment","match":["assignment"]},{"type":"Twig.expression.type.key.period","key":"shipped"}]},{"type":"raw","value":"</td>\n            <td colspan=\"2\"></td>\n            <td class=\"actions\">\n            "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"action","expression":[{"type":"Twig.expression.type.variable","value":"assignment","match":["assignment"]},{"type":"Twig.expression.type.key.period","key":"actions"}],"output":[{"type":"raw","value":"                <a href=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"href"}]},{"type":"raw","value":"\" class=\"btn btn-xs btn-"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"theme"}]},{"type":"raw","value":"\""},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"modal"}],"output":[{"type":"raw","value":" data-stock-unit-modal data-rel=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"stock_unit_id","match":["stock_unit_id"]}]},{"type":"raw","value":"\""}]}},{"type":"raw","value":">\n                    "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"action","match":["action"]},{"type":"Twig.expression.type.key.period","key":"label"},{"type":"Twig.expression.type.filter","value":"raw","match":["|raw","raw"]}]},{"type":"raw","value":"\n                </a>\n            "}]}},{"type":"raw","value":"            </td>\n        </tr>\n    "}]}},{"type":"raw","value":"    </tbody>\n    "}]}}]}},{"type":"logic","token":{"type":"Twig.logic.type.else","match":["else"],"output":[{"type":"raw","value":"    <tr>\n        <td colspan=\"10\" class=\"text-center\">\n            <em>No stock unit available</em>\n        </td>\n    </tr>\n"}]}}]}}] });
templates["relay_point.html.twig"] = Twig.twig({ data: [{"type":"logic","token":{"type":"Twig.logic.type.spaceless","match":["spaceless"],"output":[{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"company","match":["company"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"<strong><em>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"company","match":["company"]}]},{"type":"raw","value":"</em></strong><br>"}]}},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"street","match":["street"]}]},{"type":"raw","value":"\n"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"complement","match":["complement"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"<br>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"complement","match":["complement"]}]}]}},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"supplement","match":["supplement"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"<br>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"supplement","match":["supplement"]}]}]}},{"type":"raw","value":"<br>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"postal_code","match":["postal_code"]}]},{"type":"raw","value":" "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"city","match":["city"]}]},{"type":"raw","value":"\n<br>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"country","match":["country"]}]},{"type":"raw","value":"\n"},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"phone","match":["phone"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"<br>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"phone","match":["phone"]}]}]}},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"mobile","match":["mobile"]},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"<br>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"mobile","match":["mobile"]}]}]}}]}}] });
templates["pick_relay_point.html.twig"] = Twig.twig({ data: [{"type":"raw","value":"<form class=\"form-horizontal commerce-pick-relay-point\" onsubmit=\"return false;\">\n    <div class=\"rp-result\">\n        <div class=\"rp-list-wrap\">\n            <div class=\"rp-list\"></div>\n        </div>\n        <div class=\"rp-map\">\n            <span class=\"fa fa-map-marker\"></span>\n        </div>\n    </div>\n    <div class=\"rp-form\">\n        <div class=\"row\">\n            <div class=\"col-md-5\">\n                <div class=\"form-group form-group-sm\">\n                    <label for=\"rp_street\" class=\"col-md-2 control-label required\">Rue</label>\n                    <div class=\"col-md-10\">\n                        <input name=\"street\" type=\"text\" class=\"form-control\" id=\"rp_street\" placeholder=\"Rue\" required>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-3\">\n                <div class=\"form-group form-group-sm\">\n                    <label for=\"rp_postal_code\" class=\"col-md-6 control-label required\">Code postal</label>\n                    <div class=\"col-md-6\">\n                        <input name=\"postalCode\" type=\"text\" class=\"form-control\" id=\"rp_postal_code\" placeholder=\"Code postal\" required>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-4\">\n                <div class=\"form-group form-group-sm\">\n                    <label for=\"rp_city\" class=\"col-md-2 control-label required\">Ville</label>\n                    <div class=\"col-md-10\">\n                        <input name=\"city\" type=\"text\" class=\"form-control\" id=\"rp_city\" placeholder=\"Ville\" required>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</form>\n"}] });
templates["relay_point_list.html.twig"] = Twig.twig({ data: [{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"relay_points","match":["relay_points"]},{"type":"Twig.expression.type.test","filter":"defined"}],"output":[{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"rp","expression":[{"type":"Twig.expression.type.variable","value":"relay_points","match":["relay_points"]}],"output":[{"type":"raw","value":"    <div class=\"rp-point\">\n        <label for=\"rp_"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"loop","match":["loop"]},{"type":"Twig.expression.type.key.period","key":"index0"}]},{"type":"raw","value":"\">\n            <input type=\"radio\" name=\"rp_point\" id=\"rp_"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"loop","match":["loop"]},{"type":"Twig.expression.type.key.period","key":"index0"}]},{"type":"raw","value":"\" value=\""},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rp","match":["rp"]},{"type":"Twig.expression.type.key.period","key":"number"}]},{"type":"raw","value":"\">\n            <strong>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rp","match":["rp"]},{"type":"Twig.expression.type.key.period","key":"company"}]},{"type":"raw","value":"</strong><br>\n            "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rp","match":["rp"]},{"type":"Twig.expression.type.key.period","key":"street"}]},{"type":"raw","value":"<br>\n            "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rp","match":["rp"]},{"type":"Twig.expression.type.key.period","key":"postal_code"}]},{"type":"raw","value":" "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rp","match":["rp"]},{"type":"Twig.expression.type.key.period","key":"city"}]},{"type":"raw","value":"\n        </label>\n        "},{"type":"logic","token":{"type":"Twig.logic.type.if","stack":[{"type":"Twig.expression.type.variable","value":"rp","match":["rp"]},{"type":"Twig.expression.type.key.period","key":"opening_hours"},{"type":"Twig.expression.type.test","filter":"empty","modifier":"not"}],"output":[{"type":"raw","value":"        <div class=\"rp-details\">\n            <table>\n                "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"oh","expression":[{"type":"Twig.expression.type.variable","value":"rp","match":["rp"]},{"type":"Twig.expression.type.key.period","key":"opening_hours"}],"output":[{"type":"raw","value":"                <tr>\n                    <td>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"oh","match":["oh"]},{"type":"Twig.expression.type.key.period","key":"day"}]},{"type":"raw","value":"</td>\n                    "},{"type":"logic","token":{"type":"Twig.logic.type.for","key_var":null,"value_var":"rg","expression":[{"type":"Twig.expression.type.variable","value":"oh","match":["oh"]},{"type":"Twig.expression.type.key.period","key":"ranges"},{"type":"Twig.expression.type.filter","value":"slice","match":["|slice","slice"],"params":[{"type":"Twig.expression.type.parameter.start","value":"(","match":["("]},{"type":"Twig.expression.type.number","value":0,"match":["0",null]},{"type":"Twig.expression.type.comma"},{"type":"Twig.expression.type.number","value":2,"match":["2",null]},{"type":"Twig.expression.type.parameter.end","value":")","match":[")"],"expression":false}]}],"output":[{"type":"raw","value":"                        <td>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rg","match":["rg"]},{"type":"Twig.expression.type.key.period","key":"from"}]},{"type":"raw","value":" - "},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"rg","match":["rg"]},{"type":"Twig.expression.type.key.period","key":"to"}]},{"type":"raw","value":"</td>\n                    "}]}},{"type":"raw","value":"                </tr>\n                "}]}},{"type":"raw","value":"            </table>\n        </div>\n        "}]}},{"type":"raw","value":"    </div>\n"}]}}]}},{"type":"logic","token":{"type":"Twig.logic.type.elseif","stack":[{"type":"Twig.expression.type.variable","value":"error","match":["error"]},{"type":"Twig.expression.type.test","filter":"defined"}],"output":[{"type":"raw","value":"    <div class=\"alert alert-danger\">\n        <p>"},{"type":"output","stack":[{"type":"Twig.expression.type.variable","value":"error","match":["error"]}]},{"type":"raw","value":"</p>\n    </div>\n"}]}}] });
return templates;
});

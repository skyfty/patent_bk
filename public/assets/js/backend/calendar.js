define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template','fullcalendar-lang','scheduler'], function ($, undefined, Backend, Table, Form, Template,undefined, FullCalendar) {

    var Controller = {
        index: function () {
            var branch_model_id = Backend.api.query("branch_model_id");
            if (!branch_model_id) {
                branch_model_id = Config.admin_branch_model_id!= null?Config.admin_branch_model_id:0;;
            }

            $('#calendar').fullCalendar({
                height: document.body.offsetHeight - 90,
                defaultView: 'agendaDay',
                editable: false,
                selectable: false,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaDay,agendaTwoDay,agendaWeek,month,listWeek'
                },
                views: {
                    agendaTwoDay: {
                        type: 'agenda',
                        duration: { days: 2 },
                        groupByResource: true
                    }
                },
                navLinks: true,
                allDaySlot: false,
                resources: {
                    url: '/calendar/rooms',
                    data:'branch_model_id=' + branch_model_id,
                },
                events: {
                    url: '/calendar/index',
                    data:function(){
                        return {
                            branch_model_id:$("#branch_model_id").size() > 0 ? $("#branch_model_id").val() : branch_model_id,
                        };
                    }
                },
                eventClick: function (calEvent, jsEvent, view) {
                    if ($.inArray("fc-full-seats", calEvent.className) != -1) {
                        Toastr.error("这一天的此教室已经满员了");
                        return;
                    }
                    $.ajax({
                        url: "calendar/index",
                        data: {
                            "ids":calEvent.id
                        }
                    }).then(function(ret){
                        var params = "";
                        if (ret && ret.length ==1) {
                            var data = ret[0]['course'];
                            if (data.status == "finished") {
                                Backend.api.open("/provider/listevaluate?course_model_id=" + data['id'], "授课报告");
                                return;
                            } else {
                                params += "&package_model_id=" + data['package_model_id'];
                                params += "&appoint_promotion_model_id=" + data['appoint_promotion_model_id'];
                                params += "&appoint_time=" + data['appoint_time'];
                                params += "&appoint_course=" + data['appoint_course'];
                                params += "&classroom_model_id=" + data['classroom_model_id'];
                                params += "&branch_model_id=" + data['branch_model_id'];
                                params += "&staff_model_id=" + data['staff_model_id'];
                                params += "&period_model_id=" + data['period_model_id'];
                            }
                        } else {
                            params += "&branch_model_id=" + branch_model_id;
                            params += "&appoint_time=" + calEvent['appoint_time'];
                            params += "&appoint_course=" + calEvent['appoint_course'];
                            params += "&classroom_model_id=" + calEvent['classroom_model_id'];
                            params += "&period_model_id=" + calEvent['period_model_id'];
                        }
                        Backend.api.open("/provider/add?" + params, "新授课订单", {
                            callback: function (res) {
                                $('#calendar').fullCalendar('refetchEvents');
                            }
                        });
                    });
                },
                eventAfterAllRender: function (view) {
                    if ($(".fc-all-button.fc-state-active,.fc-my-button.fc-state-active").size() == 0) {
                        $(".fc-all-button").addClass("fc-state-active");
                    }
                }
            });

            $(document).on("change", "#branch_model_id", function () {
                var url = "/calendar/index?branch_model_id=" + $("#branch_model_id").val();
                window.location.href= url;
            });
            window.setTimeout(function(){
                $(".fc-toolbar .fc-left").append(Template("branch-select-tmpl",{}));
                $("#branch_model_id option[value='"+branch_model_id+"']").prop("selected", "selected");
                if (Config.staff) $('#form_branch').hide();
            }, 200);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
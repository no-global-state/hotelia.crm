
var locale = $("html").attr('lang');
var nav = new DayPilot.Navigator("nav");

nav.locale = locale;
nav.selectMode = "month";
nav.showMonths = 3;
nav.skipMonths = 3;
nav.onTimeRangeSelected = function(args) {
    loadTimeline(args.start);
    loadEvents();
};

nav.init();

var dp = new DayPilot.Scheduler("dp", {
    locale: locale
});

dp.rowHeaderWidthAutoFit = false;
dp.allowMultiRange = true;
dp.allowEventOverlap = false;
dp.days = dp.startDate.daysInMonth();

loadTimeline(DayPilot.Date.today().firstDayOfMonth());

dp.eventDeleteHandling = "Update";
dp.timeHeaders = [
    { 
        groupBy: "Month",
        format: "MMMM yyyy" 
    },
    {
        groupBy: "Day",
        format: "d"
    }
];

dp.eventHeight = 40;
dp.bubble = new DayPilot.Bubble({});
dp.rowHeaderColumns = [
    {
        title: "Room",
        width: 80
    },
    {
        title: "Capacity", 
        width: 80
    },
    {
        title: "Status", 
        width: 80
    }
];

dp.onBeforeResHeaderRender = function(args) {
    var persons = function(count) {
        return count + " person" + (count > 1 ? "s" : "");
    };

    args.resource.columns[0].html = persons(args.resource.persons);
    args.resource.columns[1].html = args.resource.status;

    switch (args.resource.status) {
        case 2:
            args.resource.cssClass = "status_dirty";
        break;

        case 1:
            args.resource.cssClass = "status_cleanup";
        break;
    }

    args.resource.areas = [{
        top:3,
        right:4,
        height:14,
        width:14,
        action:"JavaScript",
        js: function(r) {
            var modal = new DayPilot.Modal();
            var url = '/crm/scheduler/edit/?' + $.param({ id : r.id });

            modal.onClosed = function(args) {
                loadResources();
            };

            modal.showUrl(url);
        },
        v:"Hover",
        css:"icon icon-edit",
    }];
};

// http://api.daypilot.org/daypilot-scheduler-oneventmoved/
dp.onEventMoved = function (args) {
    $.post("/crm/scheduler/move", {
        id: args.e.id(),
        arrival: args.newStart.toString(),
        departure: args.newEnd.toString(),
        room_id: args.newResource
    },
    function(data) {
        dp.message(data.message);
    });
};

// http://api.daypilot.org/daypilot-scheduler-oneventresized/
dp.onEventResized = function (args) {
    $.post("/crm/scheduler/resize", {
        id: args.e.id(),
        arrival: args.newStart.toString(),
        departure: args.newEnd.toString()
    },
    function(response){
        dp.message("Resized.");
    });
};

dp.onEventDeleted = function(args) {
    $.post("/crm/scheduler/delete", {
        id: args.e.id()
    },
    function() {
        dp.message("Deleted.");
    });
};

// event creating
dp.onTimeRangeSelected = function (args) {
    // Create URL for modal
    var url = '/crm/scheduler/add/?' + $.param({
        arrival: args.start.value,
        departure: args.end.value,
        room_id: args.resource
    });

    var modal = new DayPilot.Modal();

    modal.closed = function() {
        dp.clearSelection();
        // reload all events
        var data = this.result;

        if (data && data.result === "OK") {
            loadEvents();
        }
    };

    modal.showUrl(url);
};

dp.onEventClick = function(args) {
    var modal = new DayPilot.Modal();
    var url = '/crm/scheduler/edit/?' + $.param({ id : args.e.id() });

    modal.closed = function() {
        // reload all events
        var data = this.result;
        if (data && data.result === "OK") {
            loadEvents();
        }
    };

    modal.showUrl(url);
};

dp.onBeforeCellRender = function(args) {
    var dayOfWeek = args.cell.start.getDayOfWeek();

    if (dayOfWeek === 6 || dayOfWeek === 0) {
        args.cell.backColor = "#f8f8f8";
    }
};

dp.onBeforeEventRender = function(args) {
    var start = new DayPilot.Date(args.e.start);
    var end = new DayPilot.Date(args.e.end);
    var today = DayPilot.Date.today();
    var now = new DayPilot.Date();

    args.e.html = args.e.text + " (" + start.toString("M/d/yyyy") + " - " + end.toString("M/d/yyyy") + ")";

    switch (args.e.status) {
        case "New":
            var in2days = today.addDays(1);

            if (start < in2days) {
                args.e.barColor = 'red';
                args.e.toolTip = 'Expired (not confirmed in time)';
            } else {
                args.e.barColor = 'orange';
                args.e.toolTip = 'New';
            }

        break;

        case "Confirmed":
            var arrivalDeadline = today.addHours(18);

            // must arrive before 6 pm
            if (start < today || (start.getDatePart() === today.getDatePart() && now > arrivalDeadline)) {
                args.e.barColor = "#f41616";  // red
                args.e.toolTip = 'Late arrival';
            } else {
                args.e.barColor = "green";
                args.e.toolTip = "Confirmed";
            }

        break;

        case 'Arrived': // arrived
            var checkoutDeadline = today.addHours(10);

            if (end < today || (end.getDatePart() === today.getDatePart() && now > checkoutDeadline)) { // must checkout before 10 am
                args.e.barColor = "#f41616";  // red
                args.e.toolTip = "Late checkout";
            }
            else
            {
                args.e.barColor = "#1691f4";  // blue
                args.e.toolTip = "Arrived";
            }
            break;
        case 'CheckedOut': // checked out
            args.e.barColor = "gray";
            args.e.toolTip = "Checked out";
            break;
        default:
            args.e.toolTip = "Unexpected state";
            break;
    }

    args.e.html = args.e.html + "<br /><span style='color:gray'>" + args.e.toolTip + "</span>";

    var paid = args.e.paid;
    var paidColor = "#aaaaaa";

    args.e.areas = [
        { bottom: 10, right: 4, html: "<div style='color:" + paidColor + "; font-size: 8pt;'>Paid: " + paid + "%</div>", v: "Visible"},
        { left: 4, bottom: 8, right: 4, height: 2, html: "<div style='background-color:" + paidColor + "; height: 100%; width:" + paid + "%'></div>", v: "Visible" }
    ];

};

dp.init();

loadResources();
loadEvents();

function loadTimeline(date) {
    dp.scale = "Manual";
    dp.timeline = [];
    var start = date.getDatePart().addHours(12);

    for (var i = 0; i < dp.days; i++) {
        dp.timeline.push({start: start.addDays(i), end: start.addDays(i+1)});
    }

    dp.update();
}

function loadEvents() {
    var start = dp.visibleStart();
    var end = dp.visibleEnd();

    $.post("/crm/scheduler/get-events", { arrival: start.toString(), departure: end.toString() }, function(data) {
        dp.events.list = data;
        dp.update();
    });
}

function loadResources() {
    $.post("/crm/scheduler/get-rooms", { type_id: $("#type_id").val() }, function(data) {
        dp.resources = data;
        dp.update();
    });
}

$(document).ready(function() {
    $("#filter").change(function() {
        loadResources();
    });

    $("#autocellwidth").click(function() {
        dp.cellWidth = 40;  // reset for "Fixed" mode
        dp.cellWidthSpec = $(this).is(":checked") ? "Auto" : "Fixed";
        dp.update();
    });

    $("#timerange").change(function() {
        switch (this.value) {
            case "week":
                dp.days = 7;
                nav.selectMode = "Week";
                nav.select(nav.selectionDay);
            break;

            case "month":
                dp.days = dp.startDate.daysInMonth();
                nav.selectMode = "Month";
                nav.select(nav.selectionDay);
            break;
        }
    });
});


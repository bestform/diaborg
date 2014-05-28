function drawCircles(svg, data, x, y) {
    var dataCirclesGroup = svg.append('svg:g');

    var circles = dataCirclesGroup.selectAll('.data-point')
        .data(data);

    circles
        .enter()
        .append('svg:circle')
        .attr('class', 'dot')
        .attr('fill', function () {
            return "#4390df";
        })
        .attr('cx', function (d) {
            return x(d["date"]);
        })
        .attr('cy', function (d) {
            return y(d["value"]);
        })
        .attr('r', function () {
            return 6;
        })
        .on("mouseover", function (d) {
            d3.select(this)
                .attr("r", 13)
                .attr("class", "dot-selected");
            $(".row-"+d["key"]).addClass('markRow');
        })
        .on("mouseout", function (d) {
            d3.select(this)
                .attr("r", 6)
                .attr("class", "dot")
            ;
            $(".row-"+d["key"]).removeClass('markRow');
        });
}


function drawInsulin(svg, meta, x, y, h) {
    var insulinGroup = svg.append('svg:g');

    var boxes = insulinGroup.selectAll('.data-point')
        .data(meta);

    boxes
        .enter()
        .append('rect')
        .attr("class", "insulin")
        .attr('x', function (d) {
            return x(d["date"])-10;
        })
        .attr('y', function (d) {
            return y(d['insulin']);
        })
        .attr('width', function () {
            return 20;
        })
        .attr('height', function (d) {
            return h(d['insulin']);
        })

        .on("mouseover", function (d) {
            d3.select(this)
                .attr("class", "insulin-selected");
            $(".row-"+d["key"]).addClass('markRow');
        })
        .on("mouseout", function (d) {
            d3.select(this)
                .attr("class", "insulin");
            $(".row-"+d["key"]).removeClass('markRow');
        })

    ;
}

function drawBE(svg, meta, x, y, h) {
    var beGroup = svg.append('svg:g');

    var boxes = beGroup.selectAll('.data-point')
        .data(meta);

    boxes
        .enter()
        .append('rect')
        .attr("class", "be")
        .attr('x', function (d) {
            return x(d["date"])-30;
        })
        .attr('y', function (d) {
            return y(d['BE']);
        })
        .attr('width', function () {
            return 20;
        })
        .attr('height', function (d) {
            return h(d['BE']);
        })
        .on("mouseover", function (d) {
            d3.select(this)
                .attr("class", "be-selected");
            $(".row-"+d["key"]).addClass('markRow');
        })
        .on("mouseout", function (d) {
            d3.select(this)
                .attr("class", "be");
            $(".row-"+d["key"]).removeClass('markRow');
        })
    ;
}

function drawAxis(svg, margin, height, width, xAxis, yAxis) {
    svg.append("rect")
        .attr("x", -1 * margin.left)
        .attr("y", 0)
        .attr("width", margin.left)
        .attr("height", height)
        .attr("class", "axisbackground");

    svg.append("rect")
        .attr("x", width)
        .attr("y", 0)
        .attr("width", margin.right)
        .attr("height", height)
        .attr("class", "axisbackground");

    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

    svg.append("g")
        .attr("class", "y axis")
        .call(yAxis);
}
function drawPath(svg, data, line) {
    svg.append("path")
        .datum(data)
        .attr("class", "line")
        .attr("d", line);
}
function drawBackground(svg, x, daystart, y, dayend, width, height) {
    svg.append('rect')
        .attr("x", x(daystart) + 2)
        .attr("y", y("170"))
        .attr("width", x(dayend) - x(daystart))
        .attr("height", y("100") - y("200"))
        .attr("class", "areagood");



    var make_x_axis = function() {
      return d3.svg.axis()
          .scale(x)
          .orient("bottom")
          .ticks(10)
    }

    var make_y_axis = function() {
      return d3.svg.axis()
          .scale(y)
          .orient("left")
          .ticks(10)
    }

    svg.append("g")
        .attr("class", "grid")
        .attr("transform", "translate(0," + height + ")")
        .attr("opacity", "0.3")
        .call(make_x_axis()
            .tickSize(-height, 0, 0)
            .tickFormat("")
        )

    svg.append("g")
        .attr("class", "grid")
        .attr("opacity", "0.3")
        .call(make_y_axis()
            .tickSize(-width, 0, 0)
            .tickFormat("")
        )


}
var drawGraph = function(selector, values, insulin, be){
    var margin = {top: 20, right: 20, bottom: 30, left: 50},
        width = 900 - margin.left - margin.right,
        height = 300 - margin.top - margin.bottom;

    //var parseDate = d3.time.format("%H:%M").parse;

    var x = d3.time.scale()
        .range([0, width]);

    var y = d3.scale.linear()
        .range([height, 0]);

    var insy = function(d){
        return height - Number(d) * 10;
    };
    var insh = function(d){
        return height - insy(d);
    };
    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom")
        .tickFormat(function(d){
            return new Date(d).format("HH:MM");
        })
        .ticks(10);


    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("left")
        .ticks(10);

    var line = d3.svg.line()
        .interpolate('monotone')
        .x(function(d) { return x(d.date); })
        .y(function(d) { return y(d.value); });

    var svg = d3.select(selector)
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");


    values.forEach(function(d) {
        d.date = d.date*1000;
        d.close = +d.value;
    });

    insulin.forEach(function(d) {
        d.date = d.date*1000;
        d.insulin = d.insulin.replace(',', '.');
    });
    be.forEach(function(d) {
        d.date = d.date*1000;
        d.BE = d.BE.replace(',', '.');
    });

    var yextend = d3.extent(values, function(d) { return d.close; });
    var minValue = Math.min(Number(yextend[0]), Number(yextend[1]));
    var maxValue = Math.max(Number(yextend[0]), Number(yextend[1]));
    yextend = [Math.min(50, minValue - 30), Math.max(250, maxValue + 30)];

    var daystart = (values[0]['daystart'] * 1000);
    var dayend = (values[0]['dayend'] * 1000);
    x.domain([daystart, dayend]);
    y.domain(yextend);

    drawBackground(svg, x, daystart, y, dayend, width, height);
    drawInsulin(svg, insulin, x, insy, insh);
    drawBE(svg, be, x, insy, insh);
    drawPath(svg, values, line);

    drawCircles(svg, values, x, y);

    drawAxis(svg, margin, height, width, xAxis, yAxis);
}
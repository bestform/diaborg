var drawGraph = function(selector, values){
    var margin = {top: 20, right: 20, bottom: 30, left: 50},
        width = 800 - margin.left - margin.right,
        height = 300 - margin.top - margin.bottom;

    var parseDate = d3.time.format("%H:%M").parse;

    var x = d3.time.scale()
        .range([0, width]);

    var y = d3.scale.linear()
        .range([height, 0]);

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom");

    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("left");

    var line = d3.svg.line()
        .x(function(d) { return x(d.date); })
        .y(function(d) { return y(d.value); });

    var svg = d3.select(selector)
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");


    var data = values;
//    [
//        {"date":"20:20", "value":"100"},
//        {"date":"21:00", "value":"200"},
//        {"date":"22:00", "value":"300"},
//        {"date":"23:00", "value":"200"}
//    ];

    data.forEach(function(d) {
        d.date = parseDate(d.date);
        d.close = +d.value;
    });

    var yextend = d3.extent(data, function(d) { return d.value; });
    yextend = [Math.min(50, yextend[0]), Math.max(300, yextend[1])];

    x.domain([parseDate("00:01"), parseDate("23:59")]);
    y.domain(yextend);

    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

    svg.append("g")
        .attr("class", "y axis")
        .call(yAxis);

    svg.append("path")
        .datum(data)
        .attr("class", "line")
        .attr("d", line);
}
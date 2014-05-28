var drawGraph = function(selector, values){
    var margin = {top: 20, right: 20, bottom: 30, left: 50},
        width = 800 - margin.left - margin.right,
        height = 300 - margin.top - margin.bottom;

    //var parseDate = d3.time.format("%H:%M").parse;

    var x = d3.time.scale()
        .range([0, width]);

    var y = d3.scale.linear()
        .range([height, 0]);

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom")
        .tickFormat(function(d){
            return new Date(d).format("HH:MM");
        });


    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("left")
        .ticks(5);

    var line = d3.svg.line()
        .interpolate('monotone')
        .x(function(d) { return x(d.date); })
        .y(function(d) { return y(d.value); });

    var svg = d3.select(selector)
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");


    var data = values;

    data.forEach(function(d) {
        d.date = d.date*1000;
        d.close = +d.value;
    });

    var yextend = d3.extent(data, function(d) { return d.value; });
    var minValue = Math.min(Number(yextend[0]), Number(yextend[1]));
    var maxValue = Math.max(Number(yextend[0]), Number(yextend[1]));
    yextend = [Math.min(50, minValue - 30), Math.max(300, maxValue + 30)];

    var daystart = (data[0]['daystart'] * 1000);
    var dayend = (data[0]['dayend'] * 1000);
    x.domain([daystart, dayend]);
    y.domain(yextend);

    svg.append('rect')
        .attr("x", x(daystart)+2)
        .attr("y", y("170"))
        .attr("width", x(dayend) - x(daystart))
        .attr("height", y("100") - y("200"))
        .attr("class", "areagood");

    svg.append("path")
        .datum(data)
        .attr("class", "line")
        .attr("d", line);

    var dataCirclesGroup = svg.append('svg:g');

    var circles = dataCirclesGroup.selectAll('.data-point')
        .data(data);

    circles
        .enter()
        .append('svg:circle')
        .attr('class', 'dot')
        .attr('fill', function() { return "#4390df"; })
        .attr('cx', function(d) { return x(d["date"]); })
        .attr('cy', function(d) { return y(d["value"]); })
        .attr('r', function() { return 6; })
        .on("mouseover", function(d) {
            d3.select(this)
                .attr("r", 13)
                .attr("class", "dot-selected");
        })
        .on("mouseout", function(d) {
            d3.select(this)
                .attr("r", 6)
                .attr("class", "dot")
            ;
        });


    svg.append("rect")
        .attr("x", -1 * margin.left)
        .attr("y", 0)
        .attr("width", margin.left)
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
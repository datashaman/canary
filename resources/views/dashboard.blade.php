<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title></title>

        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
            $(function () {
                var chart;
                var options = {
                    vAxis: {
                        minValue: 0
                    }
                };

                function createChart()
                {
                    chart = new google.visualization.ColumnChart(document.getElementById('chart'));
                }

                function refreshData()
                {
                    var jsonData = $.ajax({
                        url: '/samples',
                        data: {
                            interval: 60
                        },
                        dataType: 'json',
                        async: false
                    }).responseText;

                    var data = new google.visualization.DataTable(jsonData);
                    chart.draw(data, options);
                }

                function drawChart()
                {
                    createChart();
                    refreshData();
                    setInterval(refreshData, 60000);
                }

                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);
            });
        </script>
    </head>
    <body>
        <div id="chart"></div>
    </body>
</html>

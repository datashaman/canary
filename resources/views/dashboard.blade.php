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

                function refreshData()
                {
                    $.ajax({
                        url: '/samples',
                        data: {
                            interval: 60
                        },
                        dataType: 'json'
                    }).done(function (data, status, xhr) {
                        chart.draw(
                            new google.visualization.DataTable(data),
                            options
                        );
                        setTimeout(refreshData, 15000);
                    });
                }

                function createChart()
                {
                    chart = new google.visualization.ColumnChart(document.getElementById('chart'));
                    refreshData();
                }

                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(createChart);
            });
        </script>
    </head>
    <body>
        <div id="chart"></div>
    </body>
</html>

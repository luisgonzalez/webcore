function GVizView(targetDiv, vizType, options)
{
    this.targetDiv = targetDiv;
    this.options = options;
    this.vizType = vizType;
    this.data = null;
    this.columns = null;
    
    this.setData = function(data) {
        this.data = data;
    };
    
    this.setColumns = function(columns) {
        this.columns = columns;
    };
    
    this.draw = function()
    {
        google.load('visualization', '1', {'packages':[this.vizType]});
        var instance = this;
        google.setOnLoadCallback(function() {
            
            var columns = eval('(' + instance.columns + ')');
            var rows = eval('(' + instance.data + ')');
            
            var dataSource = new google.visualization.DataTable();
            for (var colIx = 0; colIx < columns.length; colIx++)
            {
                dataSource.addColumn(columns[colIx].type, columns[colIx].id);
            }
            
            dataSource.addRows(rows);
            
            // Instantiate and draw our chart, passing in some options.
            var chart = null;
            var div = document.getElementById(instance.targetDiv);
            switch(instance.vizType)
            {
                case 'imagesparkline':
                    chart = new google.visualization.ImageSparkLine(div);
                    break;
                case 'areachart':
                    chart = new google.visualization.AreaChart(div);
                    break;
                case 'piechart':
                    chart = new google.visualization.PieChart(div);
                    break;
                case 'columnchart':
                    chart = new google.visualization.ColumnChart(div);
                    break;
                case 'linechart':
                    chart = new google.visualization.LineChart(div);
                    break;
                default:
                    chart = new google.visualization.BarChart(div);
                    break;
            }
            chart.draw(dataSource, instance.options);
        });
    };
}
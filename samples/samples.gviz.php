<?php
/**
 * @category Google: The GViz Extension
 * @tutorial Create appealing charts and graphs.
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 */

require_once "ext/google/webcore.google.visualization.php";

class GoogleVizSampleWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        $data = new IndexedCollection();
        $x1   = array(
            'title' => 'PHP For Apes',
            'sales' => 100
        );
        $x2   = array(
            'title' => '.NET For Apes',
            'sales' => 130
        );
        $x3   = array(
            'title' => 'WebCore For Apes',
            'sales' => 10
        );
        $x    = array(
            new KeyedCollectionWrapper($x1, false),
            new KeyedCollectionWrapper($x2, false),
            new KeyedCollectionWrapper($x3, false)
        );
        
        $data->addRange($x);
        
        $viz = new GVizControl('myChart', 'My pie chart', GVizControl::TYPE_PIECHART);
        $viz->dataBind($data);
        $view = new GVizView($viz);
        
        $this->view  = $view;
        $this->model = $viz;
        
    }
    
    public function handleRequest()
    {
        // no postback supported
    }
    
    public static function createInstance()
    {
        return new GoogleVizSampleWidget();
    }
}

$sample = new GoogleVizSampleWidget();
?>
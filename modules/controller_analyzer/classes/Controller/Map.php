<?php
// modules/controller_analyzer/classes/Controller/Map.php
class Controller_Map extends Controller {
    
    public function action_index()
    {
        $format = $this->request->param('format', 'html');
        
        $map = Helper_ControllerScan::get_map();
        
        switch ($format) {
            case 'json':
                $this->response->headers('Content-Type', 'application/json');
                $this->response->body(json_encode($map, JSON_PRETTY_PRINT));
                break;
                
            case 'xml':
                $this->response->headers('Content-Type', 'application/xml');
                $this->response->body($this->array_to_xml($map));
                break;
                
            default:
                $view = View::factory('map/index')
                    ->set('map', $map);
                $this->response->body($view);
        }
    }
    
    protected function array_to_xml($array, $root = 'controllers')
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><{$root}></{$root}>");
        $this->add_xml_node($xml, $array);
        return $xml->asXML();
    }
    
    protected function add_xml_node(&$xml, $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                $subnode = $xml->addChild($key);
                $this->add_xml_node($subnode, $value);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }
}
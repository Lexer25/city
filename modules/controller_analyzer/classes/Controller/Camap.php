<?php
// modules/controller_analyzer/classes/Controller/Camap.php
class Controller_Camap extends Controller {
    
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
                $view = View::factory('analyzer/map')
                    ->set('map', $map)
                    ->set('title', 'Controller Map');
                $this->response->body($view);
        }
    }
    
    // ... остальные методы
}
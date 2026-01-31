<?php
class Controller_Analyzer extends Controller_Template {
    
    public $template = 'template';
    
    public function before()
    {
        parent::before();
        $this->template->title = 'Controller Analyzer';
        $this->template->content = '';
    }
    
    public function action_index()
    {
        $stats = ControllerScanner::get_statistics();
     echo Debug::vars('16', $stats);exit;   
        $content = View::factory('analyzer/index')
            ->set('stats', $stats);
            
        $this->template->content = $content;
    }
    
    public function action_map()
    {
        $format = $this->request->query('format');
        $map = ControllerScanner::get_map();
        
        if ($format === 'json') {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode($map, JSON_PRETTY_PRINT));
            return;
        }
        
        if ($format === 'xml') {
            $this->response->headers('Content-Type', 'application/xml');
            $xml = new SimpleXMLElement('<controllers/>');
            foreach ($map as $name => $data) {
                $controller = $xml->addChild('controller');
                $controller->addAttribute('name', $name);
                // ... добавьте остальные данные
            }
            $this->response->body($xml->asXML());
            return;
        }
        
        $content = View::factory('analyzer/map')
            ->set('map', $map);
            
        $this->template->content = $content;
        $this->template->title = 'Controller Map';
    }
}
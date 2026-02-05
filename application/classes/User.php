<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Class User - информация о текущем авторизованном пользователе.
 */
class User 
{
    public $id_pep;
    public $id_org;
    public $id_orgctrl;
    public $id_devgroup;
    public $login;
    public $role;       
    public $id_buro;  
    public $flag;
    public $bp;
    public $id_role;
    public $count_buro;
    public $count_access;
    
    public function __construct($default = array())
    {
        $_config = Kohana::$config->load('auth');
        $_session = Session::instance($_config['session_type']);
        $session_data = $_session->get($_config['session_key'], $default);
        
        $this->id_pep = Arr::get($session_data, 'ID_PEP');
        $this->id_org = Arr::get($session_data, 'ID_ORG');
        $this->id_orgctrl = Arr::get($session_data, 'ID_ORGCTRL');
        $this->id_devgroup = Arr::get($session_data, 'ID_DEVGROUP');
        $this->login = Arr::get($session_data, 'LOGIN');
        $this->flag = Arr::get($session_data, 'FLAG');
        
        // Оригинальная роль из сессии
        $this->role = Arr::get($session_data, 'ROLE');
        
        //$this->buro_role = Buro::getUserRole($this->id_pep);
        $this->id_role = Buro::getUserRoleId($this->id_pep);

        $this->id_buro = Buro::getUserBuroId($this->id_pep);
        
        $this->bp = $this->getBuroNameForUser();
        $buro = new Buro();
        $this->count_buro = $buro->getCountBuro($this->id_pep);
        $this->count_access = $buro->getCountAccess($this->id_buro);
    }
    
    /**
     * Получает бюро для текущего пользователя
     */
    protected function getBuroNameForUser()
    {     
        $buro = new Buro();
        //return $buro->get_id_buro_forUser($this->id_pep);
    }
    
    /**
     * Список организаций, которыми может управлять текущий пользователь
     */
    public function getChildOrg()
    {
        $sql = 'SELECT id_org FROM organization_getchild(1, '.$this->id_orgctrl.')';
        try {
            return DB::query(Database::SELECT, $sql)
                ->execute(Database::instance('fb'))
                ->as_array();
        } catch (Exception $e) {
            Log::instance()->add(Log::DEBUG, $e->getMessage());
            return array();
        }
    }
}
<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Mancard extends Controller_Template {
    
    public function before()
    {
        parent::before();
        $session = Session::instance();
        $_SESSION['menu_active'] = 'mancard';
        $this->set_full_width(true);
    }
    
    /**
     * Главная страница - дерево организаций + список сотрудников
     */
    public function action_index()
    {
        $org_id = (int) $this->request->param('id', 1);
        
        // Получаем дерево организаций
        $org_tree = Model::factory('Mancard')->getOrganizationTree();
        
        // Получаем сотрудников выбранной организации
        $people_list = Model::factory('Mancard')->getPeopleByOrganization($org_id);
        
        // Получаем информацию о текущей организации
        $current_org = Model::factory('Mancard')->getOrganization($org_id);
        
        $content = View::factory('mancard/index', array(
            'org_tree' => $org_tree,
            'people_list' => $people_list,
            'current_org' => $current_org,
            'current_org_id' => $org_id,
        ));
        
        $this->template->content = $content;
    }
    
    /**
     * AJAX: Получить сотрудников организации
     */
    public function action_get_people()
    {
        $this->auto_render = false;
        $org_id = (int) $this->request->param('id', 1);
        
        $people = Model::factory('Mancard')->getPeopleByOrganization($org_id);
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $people
        )));
    }
    
    /**
     * AJAX: Добавить организацию
     */
    public function action_add_organization()
    {
        $this->auto_render = false;
        $post = $this->request->post();
        
        $name = trim(Arr::get($post, 'name', ''));
        $parent_id = (int) Arr::get($post, 'parent_id', 1);
        
        if (empty($name)) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Название организации не может быть пустым'
            )));
            return;
        }
        
        try {
            $id = Model::factory('Mancard')->addOrganization($name, $parent_id);
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Организация добавлена',
                'id' => $id,
                'name' => $name
            )));
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
    
    /**
     * AJAX: Удалить организацию
     */
    public function action_delete_organization()
    {
        $this->auto_render = false;
        $org_id = (int) $this->request->param('id', 0);
        
        if ($org_id <= 1) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Нельзя удалить корневую организацию'
            )));
            return;
        }
        
        try {
            Model::factory('Mancard')->deleteOrganization($org_id);
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Организация удалена'
            )));
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
    
    /**
     * AJAX: Переименовать организацию
     */
    public function action_rename_organization()
    {
        $this->auto_render = false;
        $post = $this->request->post();
        
        $org_id = (int) Arr::get($post, 'id', 0);
        $name = trim(Arr::get($post, 'name', ''));
        
        if ($org_id <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Неверный ID организации'
            )));
            return;
        }
        
        if (empty($name)) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Название не может быть пустым'
            )));
            return;
        }
        
        try {
            Model::factory('Mancard')->renameOrganization($org_id, $name);
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Название обновлено'
            )));
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
    
    /**
     * AJAX: Переместить организацию
     */
    public function action_move_organization()
    {
        $this->auto_render = false;
        $post = $this->request->post();
        
        $org_id = (int) Arr::get($post, 'org_id', 0);
        $new_parent_id = (int) Arr::get($post, 'new_parent_id', 1);
        
        if ($org_id <= 1) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Нельзя перемещать корневую организацию'
            )));
            return;
        }
        
        try {
            Model::factory('Mancard')->moveOrganization($org_id, $new_parent_id);
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Организация перемещена'
            )));
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
    
    /**
     * AJAX: Добавить сотрудника
     */
    public function action_add_person()
    {
        $this->auto_render = false;
        $post = $this->request->post();
        
        $data = array(
            'surname' => trim(Arr::get($post, 'surname', '')),
            'name' => trim(Arr::get($post, 'name', '')),
            'patronymic' => trim(Arr::get($post, 'patronymic', '')),
            'id_org' => (int) Arr::get($post, 'id_org', 1),
            'tabnum' => trim(Arr::get($post, 'tabnum', '')),
            'login' => trim(Arr::get($post, 'login', '')),
            'post' => trim(Arr::get($post, 'post', '')),
            'phonehome' => trim(Arr::get($post, 'phonehome', '')),
            'phonecellular' => trim(Arr::get($post, 'phonecellular', '')),
            'phonework' => trim(Arr::get($post, 'phonework', '')),
            'datebirth' => Arr::get($post, 'datebirth', null),
            'placebirth' => trim(Arr::get($post, 'placebirth', '')),
            'placelife' => trim(Arr::get($post, 'placelife', '')),
            'placereg' => trim(Arr::get($post, 'placereg', '')),
            'numdoc' => trim(Arr::get($post, 'numdoc', '')),
            'datedoc' => Arr::get($post, 'datedoc', null),
            'placedoc' => trim(Arr::get($post, 'placedoc', '')),
            'note' => trim(Arr::get($post, 'note', '')),
            'sysnote' => trim(Arr::get($post, 'sysnote', '')),
            'active' => (int) Arr::get($post, 'active', 1),
        );
        
        if (empty($data['surname']) || empty($data['name'])) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Фамилия и Имя обязательны для заполнения'
            )));
            return;
        }
        
        try {
            $id = Model::factory('Mancard')->addPerson($data);
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Сотрудник добавлен',
                'id' => $id
            )));
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
    
    /**
     * AJAX: Обновить сотрудника
     */
    public function action_update_person()
    {
        $this->auto_render = false;
        $post = $this->request->post();
        
        $id_pep = (int) Arr::get($post, 'id_pep', 0);
        
        if ($id_pep <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Неверный ID сотрудника'
            )));
            return;
        }
        
        $data = array(
            'surname' => trim(Arr::get($post, 'surname', '')),
            'name' => trim(Arr::get($post, 'name', '')),
            'patronymic' => trim(Arr::get($post, 'patronymic', '')),
            'id_org' => (int) Arr::get($post, 'id_org', 1),
            'tabnum' => trim(Arr::get($post, 'tabnum', '')),
            'login' => trim(Arr::get($post, 'login', '')),
            'post' => trim(Arr::get($post, 'post', '')),
            'phonehome' => trim(Arr::get($post, 'phonehome', '')),
            'phonecellular' => trim(Arr::get($post, 'phonecellular', '')),
            'phonework' => trim(Arr::get($post, 'phonework', '')),
            'datebirth' => Arr::get($post, 'datebirth', null),
            'placebirth' => trim(Arr::get($post, 'placebirth', '')),
            'placelife' => trim(Arr::get($post, 'placelife', '')),
            'placereg' => trim(Arr::get($post, 'placereg', '')),
            'numdoc' => trim(Arr::get($post, 'numdoc', '')),
            'datedoc' => Arr::get($post, 'datedoc', null),
            'placedoc' => trim(Arr::get($post, 'placedoc', '')),
            'note' => trim(Arr::get($post, 'note', '')),
            'sysnote' => trim(Arr::get($post, 'sysnote', '')),
            'active' => (int) Arr::get($post, 'active', 1),
        );
        
        if (empty($data['surname']) || empty($data['name'])) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Фамилия и Имя обязательны для заполнения'
            )));
            return;
        }
        
        try {
            Model::factory('Mancard')->updatePerson($id_pep, $data);
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Данные обновлены'
            )));
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
    
    /**
     * AJAX: Удалить сотрудника
     */
    public function action_delete_person()
    {
        $this->auto_render = false;
        $id_pep = (int) $this->request->param('id', 0);
        
        if ($id_pep <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Неверный ID сотрудника'
            )));
            return;
        }
        
        try {
            Model::factory('Mancard')->deletePerson($id_pep);
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Сотрудник удален'
            )));
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
    
    /**
     * AJAX: Массовое перемещение сотрудников
     */
    public function action_move_people()
    {
        $this->auto_render = false;
        $post = $this->request->post();
        
        $person_ids = Arr::get($post, 'person_ids', array());
        $target_org_id = (int) Arr::get($post, 'target_org_id', 0);
        
        if (empty($person_ids)) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Не выбраны сотрудники'
            )));
            return;
        }
        
        if ($target_org_id <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Не выбрана целевая организация'
            )));
            return;
        }
        
        $person_ids = array_map('intval', $person_ids);
        $person_ids = array_filter($person_ids);
        
        if (empty($person_ids)) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Неверные ID сотрудников'
            )));
            return;
        }
        
        try {
            $count = Model::factory('Mancard')->movePeople($person_ids, $target_org_id);
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Перемещено ' . $count . ' сотрудников'
            )));
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
    
    /**
     * AJAX: Получить данные сотрудника для редактирования
     */
    public function action_get_person()
    {
        $this->auto_render = false;
        $id_pep = (int) $this->request->param('id', 0);
        
        if ($id_pep <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Неверный ID сотрудника'
            )));
            return;
        }
        
        $person = Model::factory('Mancard')->getPerson($id_pep);
        
        if (empty($person)) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Сотрудник не найден'
            )));
            return;
        }
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $person
        )));
    }
    
    /**
     * AJAX: Получить все организации для выпадающего списка
     */
    public function action_get_organizations()
    {
        $this->auto_render = false;
        
        $orgs = Model::factory('Mancard')->getAllOrganizations();
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $orgs
        )));
    }
    
    /**
     * Страница массового перемещения
     */
    public function action_move()
    {
        $_SESSION['menu_active'] = 'mancard';
        
        $org_tree = Model::factory('Mancard')->getOrganizationTree();
        $organizations = Model::factory('Mancard')->getAllOrganizations();
        $all_people = Model::factory('Mancard')->getAllPeopleWithOrgs();
        
        $content = View::factory('mancard/move', array(
            'org_tree' => $org_tree,
            'organizations' => $organizations,
            'all_people' => $all_people,
        ));
        
        $this->template->content = $content;
    }
    
    /**
     * AJAX: Получить дерево организаций для панели перемещения
     */
    public function action_get_move_tree()
    {
        $this->auto_render = false;
        
        $org_tree = Model::factory('Mancard')->getOrganizationTree();
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $org_tree
        )));
    }
    
    /**
     * AJAX: Получить сотрудников для панели перемещения
     */
    public function action_get_move_people()
    {
        $this->auto_render = false;
        $org_id = (int) $this->request->param('id', 0);
        
        if ($org_id <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Не указана организация'
            )));
            return;
        }
        
        $people = Model::factory('Mancard')->getPeopleByOrganization($org_id);
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $people
        )));
    }
    
    /**
     * AJAX: Массовое перемещение (для новой панели)
     */
    public function action_move_items()
    {
        $this->auto_render = false;
        $post = $this->request->post();
        
        $source_org_id = (int) Arr::get($post, 'source_org_id', 0);
        $target_org_id = (int) Arr::get($post, 'target_org_id', 0);
        $move_people = Arr::get($post, 'move_people', array());
        $move_orgs = Arr::get($post, 'move_orgs', array());
        
        if ($source_org_id <= 0 || $target_org_id <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Не выбраны организации'
            )));
            return;
        }
        
        if ($source_org_id == $target_org_id) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Нельзя перемещать в ту же организацию'
            )));
            return;
        }
        
        try {
            $result = array(
                'people_moved' => 0,
                'orgs_moved' => 0,
                'errors' => array()
            );
            
            if (!empty($move_people)) {
                $move_people = array_map('intval', $move_people);
                $move_people = array_filter($move_people);
                
                if (!empty($move_people)) {
                    $count = Model::factory('Mancard')->movePeople($move_people, $target_org_id);
                    $result['people_moved'] = $count;
                }
            }
            
            if (!empty($move_orgs)) {
                $move_orgs = array_map('intval', $move_orgs);
                $move_orgs = array_filter($move_orgs);
                
                foreach ($move_orgs as $org_id) {
                    if ($org_id == 1) {
                        $result['errors'][] = 'Нельзя перемещать корневую организацию (ID: 1)';
                        continue;
                    }
                    try {
                        Model::factory('Mancard')->moveOrganization($org_id, $target_org_id);
                        $result['orgs_moved']++;
                    } catch (Exception $e) {
                        $result['errors'][] = $e->getMessage() . ' (ID: ' . $org_id . ')';
                    }
                }
            }
            
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Перемещено: ' . $result['people_moved'] . ' сотрудников, ' . $result['orgs_moved'] . ' организаций',
                'data' => $result
            )));
            
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
    
    /**
     * AJAX: Получить структуру организации (один уровень)
     */
    public function action_get_org_structure()
    {
        $this->auto_render = false;
        $org_id = (int) $this->request->param('id', 1);
        
        $structure = Model::factory('Mancard')->getOrgStructureLevel($org_id);
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $structure
        )));
    }
    
    /**
     * AJAX: Получить структуру организации с картами (один уровень)
     */
    public function action_get_org_structure_cards()
    {
        $this->auto_render = false;
        $org_id = (int) $this->request->param('id', 1);
        
        $structure = Model::factory('Mancard')->getOrgStructureLevelWithCards($org_id);
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $structure
        )));
    }
    
    /**
     * AJAX: Получить карты сотрудника
     */
    public function action_get_person_cards()
    {
        $this->auto_render = false;
        $id_pep = (int) $this->request->param('id', 0);
        
        if ($id_pep <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Неверный ID сотрудника'
            )));
            return;
        }
        
        $cards = Model::factory('Mancard')->getPersonCards($id_pep);
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $cards
        )));
    }
    
    /**
     * AJAX: Получить все категории доступа
     */
    public function action_get_access_names()
    {
        $this->auto_render = false;
        
        $access_names = Model::factory('Mancard')->getAllAccessNames();
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $access_names
        )));
    }
    
    /**
     * AJAX: Получить категории доступа для организации или сотрудника
     */
    public function action_get_entity_access()
    {
        $this->auto_render = false;
        $type = $this->request->param('type', '');
        $id = (int) $this->request->param('id', 0);
        
        if (empty($type) || $id <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Неверные параметры'
            )));
            return;
        }
        
        $result = array();
        $org_access = array();
        $person_access = array();
        
        if ($type === 'org') {
            $result = Model::factory('Mancard')->getOrgAccessNames($id);
        } elseif ($type === 'person') {
            $result = Model::factory('Mancard')->getPersonAccessNames($id);
            $org_id = Model::factory('Mancard')->getPersonOrganization($id);
            if ($org_id) {
                $org_access = Model::factory('Mancard')->getOrgAccessNames($org_id);
            }
        }
        
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'data' => $result,
            'org_access' => $org_access,
            'person_access' => $person_access
        )));
    }
    
    /**
     * AJAX: Обновить категории доступа
     */
    public function action_update_access()
    {
        $this->auto_render = false;
        $post = $this->request->post();
        
        $type = Arr::get($post, 'type', '');
        $id = (int) Arr::get($post, 'id', 0);
        $access_ids = Arr::get($post, 'access_ids', array());
        
        if (empty($type) || $id <= 0) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => 'Неверные параметры'
            )));
            return;
        }
        
        $access_ids = array_map('intval', $access_ids);
        $access_ids = array_filter($access_ids);
        
        try {
            if ($type === 'org') {
                Model::factory('Mancard')->updateOrgAccessNames($id, $access_ids);
            } elseif ($type === 'person') {
                Model::factory('Mancard')->updatePersonAccessNames($id, $access_ids);
            } else {
                $this->response->body(json_encode(array(
                    'success' => false,
                    'message' => 'Неверный тип'
                )));
                return;
            }
            
            $this->response->body(json_encode(array(
                'success' => true,
                'message' => 'Категории доступа обновлены'
            )));
        } catch (Exception $e) {
            $this->response->body(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
}
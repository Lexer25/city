<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Mancard extends Model {
    
    /**
     * Получить дерево организаций
     */
    public function getOrganizationTree()
    {
        $sql = 'SELECT 
                    ID_ORG, 
                    NAME, 
                    ID_PARENT, 
                    FLAG,
                    (SELECT COUNT(*) FROM PEOPLE WHERE ID_ORG = o.ID_ORG AND "ACTIVE" = 1) AS PEOPLE_COUNT
                FROM ORGANIZATION o
                WHERE ID_DB = 1
                ORDER BY ID_PARENT, NAME';
        
        $query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
            ->execute(Database::instance('fb'))
            ->as_array();
        
        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_ORG' => $row['ID_ORG'],
                'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
                'ID_PARENT' => $row['ID_PARENT'],
                'FLAG' => $row['FLAG'],
                'PEOPLE_COUNT' => $row['PEOPLE_COUNT'],
            );
        }
        
        return $result;
    }
    
    /**
     * Получить все организации для выпадающего списка (плоский список)
     */
    public function getAllOrganizations()
    {
        $sql = 'SELECT ID_ORG, NAME, ID_PARENT 
                FROM ORGANIZATION 
                WHERE ID_DB = 1 
                ORDER BY ID_PARENT, NAME';
        
        $query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
            ->execute(Database::instance('fb'))
            ->as_array();
        
        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_ORG' => $row['ID_ORG'],
                'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
                'ID_PARENT' => $row['ID_PARENT'],
            );
        }
        
        return $result;
    }
    
    /**
     * Получить информацию об организации
     */
    public function getOrganization($id_org)
    {
        $sql = 'SELECT ID_ORG, NAME, ID_PARENT, FLAG 
                FROM ORGANIZATION 
                WHERE ID_ORG = ' . (int)$id_org;
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        if (empty($query)) {
            return null;
        }
        
        $row = $query[0];
        return array(
            'ID_ORG' => $row['ID_ORG'],
            'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
            'ID_PARENT' => $row['ID_PARENT'],
            'FLAG' => $row['FLAG'],
        );
    }
    
    /**
     * Добавить организацию
     */
    public function addOrganization($name, $parent_id = 1)
    {
        $name_win = iconv('UTF-8', 'windows-1251', $name);
        $parent_id = (int)$parent_id;
        
        // Получаем максимальный ID_ORG
        $sql = 'SELECT MAX(ID_ORG) AS MAX_ID FROM ORGANIZATION';
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'));
        $max_id = (int)$query->get('MAX_ID');
        $new_id = $max_id + 1;
        
        $sql = 'INSERT INTO ORGANIZATION (
                    ID_ORG, ID_DB, NAME, ID_PARENT, FLAG, ID_DEF_ACCESSNAME, DIVCODE, TIME_STAMP
                ) VALUES (
                    ' . $new_id . ', 1, \'' . $name_win . '\', ' . $parent_id . ', 0, NULL, \'' . $new_id . '\', CURRENT_TIMESTAMP
                )';
        
        DB::query(Database::INSERT, $sql)
            ->execute(Database::instance('fb'));
        
        return $new_id;
    }
    
    /**
     * Переименовать организацию
     */
    public function renameOrganization($id_org, $new_name)
    {
        $id_org = (int)$id_org;
        $new_name_win = iconv('UTF-8', 'windows-1251', $new_name);
        
        $sql = 'UPDATE ORGANIZATION 
                SET NAME = \'' . $new_name_win . '\', TIME_STAMP = CURRENT_TIMESTAMP 
                WHERE ID_ORG = ' . $id_org;
        
        DB::query(Database::UPDATE, $sql)
            ->execute(Database::instance('fb'));
    }
    
    /**
     * Удалить организацию (и всех сотрудников в ней)
     */
    public function deleteOrganization($id_org)
    {
        $id_org = (int)$id_org;
        
        // Проверяем, есть ли подчиненные организации
        $sql = 'SELECT COUNT(*) AS CNT FROM ORGANIZATION WHERE ID_PARENT = ' . $id_org;
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'));
        
        if ((int)$query->get('CNT') > 0) {
            throw new Exception('Нельзя удалить организацию, у которой есть подчиненные');
        }
        
        // Удаляем сотрудников
        $sql = 'DELETE FROM PEOPLE WHERE ID_ORG = ' . $id_org;
        DB::query(Database::DELETE, $sql)
            ->execute(Database::instance('fb'));
        
        // Удаляем организацию
        $sql = 'DELETE FROM ORGANIZATION WHERE ID_ORG = ' . $id_org;
        DB::query(Database::DELETE, $sql)
            ->execute(Database::instance('fb'));
    }
    
    /**
     * Переместить организацию
     */
    public function moveOrganization($id_org, $new_parent_id)
    {
        $id_org = (int)$id_org;
        $new_parent_id = (int)$new_parent_id;
        
        // Проверяем, не пытаемся ли переместить в саму себя
        if ($id_org == $new_parent_id) {
            throw new Exception('Нельзя переместить организацию в саму себя');
        }
        
        // Проверяем цикличность (нельзя переместить в подчиненную организацию)
        $sql = 'SELECT ID_ORG FROM ORGANIZATION_GETPARENT(' . $id_org . ', ' . $new_parent_id . ')';
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        if (!empty($query)) {
            throw new Exception('Нельзя переместить организацию в подчиненную');
        }
        
        $sql = 'UPDATE ORGANIZATION 
                SET ID_PARENT = ' . $new_parent_id . ', TIME_STAMP = CURRENT_TIMESTAMP 
                WHERE ID_ORG = ' . $id_org;
        
        DB::query(Database::UPDATE, $sql)
            ->execute(Database::instance('fb'));
    }
    
    /**
     * Получить сотрудников организации
     */
    public function getPeopleByOrganization($id_org)
    {
        $id_org = (int)$id_org;
        
        $sql = 'SELECT 
                    p.ID_PEP,
                    p.SURNAME,
                    p.NAME,
                    p.PATRONYMIC,
                    p.POST,
                    p.PHONEWORK,
                    p.PHONECELLULAR,
                    p."ACTIVE",
                    p.NOTE,
                    p.TABNUM,
                    p.LOGIN,
                    p.ID_ORG,
                    o.NAME AS ORG_NAME,
                    (SELECT MAX(ID_CARD) FROM CARD WHERE ID_PEP = p.ID_PEP) AS ID_CARD
                FROM PEOPLE p
                JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
                WHERE p.ID_ORG = ' . $id_org . '
                AND p.ID_ORG NOT IN (2, 3)
                ORDER BY p.SURNAME, p.NAME, p.PATRONYMIC';
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_PEP' => $row['ID_PEP'],
                'SURNAME' => iconv('windows-1251', 'UTF-8', $row['SURNAME']),
                'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
                'PATRONYMIC' => iconv('windows-1251', 'UTF-8', $row['PATRONYMIC']),
                'POST' => iconv('windows-1251', 'UTF-8', $row['POST']),
                'PHONEWORK' => $row['PHONEWORK'],
                'PHONECELLULAR' => $row['PHONECELLULAR'],
                'ACTIVE' => $row['ACTIVE'],
                'NOTE' => iconv('windows-1251', 'UTF-8', $row['NOTE']),
                'TABNUM' => $row['TABNUM'],
                'LOGIN' => $row['LOGIN'],
                'ID_ORG' => $row['ID_ORG'],
                'ORG_NAME' => iconv('windows-1251', 'UTF-8', $row['ORG_NAME']),
                'ID_CARD' => $row['ID_CARD'],
            );
        }
        
        return $result;
    }
    
    /**
     * Получить данные сотрудника
     */
    public function getPerson($id_pep)
    {
        $id_pep = (int)$id_pep;
        
        $sql = 'SELECT 
                    p.ID_PEP,
                    p.SURNAME,
                    p.NAME,
                    p.PATRONYMIC,
                    p.ID_ORG,
                    p.TABNUM,
                    p.LOGIN,
                    p.POST,
                    p.PHONEHOME,
                    p.PHONECELLULAR,
                    p.PHONEWORK,
                    p.DATEBIRTH,
                    p.PLACEBIRTH,
                    p.PLACELIFE,
                    p.PLACEREG,
                    p.NUMDOC,
                    p.DATEDOC,
                    p.PLACEDOC,
                    p."ACTIVE",
                    p.NOTE,
                    p.SYSNOTE,
                    o.NAME AS ORG_NAME
                FROM PEOPLE p
                JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
                WHERE p.ID_PEP = ' . $id_pep;
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        if (empty($query)) {
            return null;
        }
        
        $row = $query[0];
        return array(
            'ID_PEP' => $row['ID_PEP'],
            'SURNAME' => iconv('windows-1251', 'UTF-8', $row['SURNAME']),
            'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
            'PATRONYMIC' => iconv('windows-1251', 'UTF-8', $row['PATRONYMIC']),
            'ID_ORG' => $row['ID_ORG'],
            'ORG_NAME' => iconv('windows-1251', 'UTF-8', $row['ORG_NAME']),
            'TABNUM' => $row['TABNUM'],
            'LOGIN' => $row['LOGIN'],
            'POST' => iconv('windows-1251', 'UTF-8', $row['POST']),
            'PHONEHOME' => $row['PHONEHOME'],
            'PHONECELLULAR' => $row['PHONECELLULAR'],
            'PHONEWORK' => $row['PHONEWORK'],
            'DATEBIRTH' => $row['DATEBIRTH'],
            'PLACEBIRTH' => iconv('windows-1251', 'UTF-8', $row['PLACEBIRTH']),
            'PLACELIFE' => iconv('windows-1251', 'UTF-8', $row['PLACELIFE']),
            'PLACEREG' => iconv('windows-1251', 'UTF-8', $row['PLACEREG']),
            'NUMDOC' => $row['NUMDOC'],
            'DATEDOC' => $row['DATEDOC'],
            'PLACEDOC' => iconv('windows-1251', 'UTF-8', $row['PLACEDOC']),
            'ACTIVE' => $row['ACTIVE'],
            'NOTE' => iconv('windows-1251', 'UTF-8', $row['NOTE']),
            'SYSNOTE' => $row['SYSNOTE'],
        );
    }
    
    /**
     * Добавить сотрудника
     */
    public function addPerson($data)
    {
        // Генерируем новый ID
        $sql = 'SELECT MAX(ID_PEP) AS MAX_ID FROM PEOPLE';
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'));
        $max_id = (int)$query->get('MAX_ID');
        $new_id = $max_id + 1;
        
        // Экранируем данные
        $surname = iconv('UTF-8', 'windows-1251', $data['surname']);
        $name = iconv('UTF-8', 'windows-1251', $data['name']);
        $patronymic = iconv('UTF-8', 'windows-1251', $data['patronymic']);
        $post = iconv('UTF-8', 'windows-1251', $data['post']);
        $placebirth = iconv('UTF-8', 'windows-1251', $data['placebirth']);
        $placelife = iconv('UTF-8', 'windows-1251', $data['placelife']);
        $placereg = iconv('UTF-8', 'windows-1251', $data['placereg']);
        $placedoc = iconv('UTF-8', 'windows-1251', $data['placedoc']);
        $note = iconv('UTF-8', 'windows-1251', $data['note']);
        $sysnote = iconv('UTF-8', 'windows-1251', $data['sysnote']);
        
        $datebirth = !empty($data['datebirth']) ? "'" . $data['datebirth'] . "'" : 'NULL';
        $datedoc = !empty($data['datedoc']) ? "'" . $data['datedoc'] . "'" : 'NULL';
        
        $sql = 'INSERT INTO PEOPLE (
                    ID_PEP, ID_DB, ID_ORG, SURNAME, NAME, PATRONYMIC,
                    DATEBIRTH, PLACEBIRTH, PLACELIFE, PLACEREG,
                    PHONEHOME, PHONECELLULAR, PHONEWORK,
                    NUMDOC, DATEDOC, PLACEDOC,
                    "ACTIVE", FLAG, LOGIN, PSWD, POST, TABNUM, NOTE, SYSNOTE, TIME_STAMP
                ) VALUES (
                    ' . $new_id . ', 1, ' . (int)$data['id_org'] . ',
                    \'' . $surname . '\', \'' . $name . '\', \'' . $patronymic . '\',
                    ' . $datebirth . ', \'' . $placebirth . '\', \'' . $placelife . '\', \'' . $placereg . '\',
                    \'' . $data['phonehome'] . '\', \'' . $data['phonecellular'] . '\', \'' . $data['phonework'] . '\',
                    \'' . $data['numdoc'] . '\', ' . $datedoc . ', \'' . $placedoc . '\',
                    ' . (int)$data['active'] . ', 0, \'' . $data['login'] . '\', \'\', \'' . $post . '\',
                    \'' . $data['tabnum'] . '\', \'' . $note . '\', \'' . $sysnote . '\', CURRENT_TIMESTAMP
                )';
        
        DB::query(Database::INSERT, $sql)
            ->execute(Database::instance('fb'));
        
        return $new_id;
    }
    
    /**
     * Обновить данные сотрудника
     */
    public function updatePerson($id_pep, $data)
    {
        $id_pep = (int)$id_pep;
        
        $surname = iconv('UTF-8', 'windows-1251', $data['surname']);
        $name = iconv('UTF-8', 'windows-1251', $data['name']);
        $patronymic = iconv('UTF-8', 'windows-1251', $data['patronymic']);
        $post = iconv('UTF-8', 'windows-1251', $data['post']);
        $placebirth = iconv('UTF-8', 'windows-1251', $data['placebirth']);
        $placelife = iconv('UTF-8', 'windows-1251', $data['placelife']);
        $placereg = iconv('UTF-8', 'windows-1251', $data['placereg']);
        $placedoc = iconv('UTF-8', 'windows-1251', $data['placedoc']);
        $note = iconv('UTF-8', 'windows-1251', $data['note']);
        $sysnote = iconv('UTF-8', 'windows-1251', $data['sysnote']);
        
        $datebirth = !empty($data['datebirth']) ? "'" . $data['datebirth'] . "'" : 'NULL';
        $datedoc = !empty($data['datedoc']) ? "'" . $data['datedoc'] . "'" : 'NULL';
        
        $sql = 'UPDATE PEOPLE SET
                    ID_ORG = ' . (int)$data['id_org'] . ',
                    SURNAME = \'' . $surname . '\',
                    NAME = \'' . $name . '\',
                    PATRONYMIC = \'' . $patronymic . '\',
                    DATEBIRTH = ' . $datebirth . ',
                    PLACEBIRTH = \'' . $placebirth . '\',
                    PLACELIFE = \'' . $placelife . '\',
                    PLACEREG = \'' . $placereg . '\',
                    PHONEHOME = \'' . $data['phonehome'] . '\',
                    PHONECELLULAR = \'' . $data['phonecellular'] . '\',
                    PHONEWORK = \'' . $data['phonework'] . '\',
                    NUMDOC = \'' . $data['numdoc'] . '\',
                    DATEDOC = ' . $datedoc . ',
                    PLACEDOC = \'' . $placedoc . '\',
                    "ACTIVE" = ' . (int)$data['active'] . ',
                    POST = \'' . $post . '\',
                    TABNUM = \'' . $data['tabnum'] . '\',
                    LOGIN = \'' . $data['login'] . '\',
                    NOTE = \'' . $note . '\',
                    SYSNOTE = \'' . $sysnote . '\',
                    TIME_STAMP = CURRENT_TIMESTAMP
                WHERE ID_PEP = ' . $id_pep;
        
        DB::query(Database::UPDATE, $sql)
            ->execute(Database::instance('fb'));
    }
    
    /**
     * Удалить сотрудника
     */
    public function deletePerson($id_pep)
    {
        $id_pep = (int)$id_pep;
        
        // Удаляем карты
        $sql = 'DELETE FROM CARD WHERE ID_PEP = ' . $id_pep;
        DB::query(Database::DELETE, $sql)
            ->execute(Database::instance('fb'));
        
        // Удаляем сотрудника
        $sql = 'DELETE FROM PEOPLE WHERE ID_PEP = ' . $id_pep . ' AND ID_PEP != 1';
        DB::query(Database::DELETE, $sql)
            ->execute(Database::instance('fb'));
    }
    
    /**
     * Массовое перемещение сотрудников
     */
    public function movePeople($person_ids, $target_org_id)
    {
        if (empty($person_ids)) {
            return 0;
        }
        
        $ids_str = implode(',', $person_ids);
        $target_org_id = (int)$target_org_id;
        
        $sql = 'UPDATE PEOPLE 
                SET ID_ORG = ' . $target_org_id . ', TIME_STAMP = CURRENT_TIMESTAMP 
                WHERE ID_PEP IN (' . $ids_str . ')';
        
        $result = DB::query(Database::UPDATE, $sql)
            ->execute(Database::instance('fb'));
        
        return count($person_ids);
    }
    
    /**
     * Получить корневые организации
     */
    public function getRootOrganizations()
    {
        $sql = 'SELECT ID_ORG, NAME, ID_PARENT, FLAG 
                FROM ORGANIZATION 
                WHERE ID_PARENT = 1 AND ID_DB = 1
                ORDER BY NAME';
        
        $query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
            ->execute(Database::instance('fb'))
            ->as_array();
        
        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_ORG' => $row['ID_ORG'],
                'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
                'ID_PARENT' => $row['ID_PARENT'],
                'FLAG' => $row['FLAG'],
            );
        }
        
        return $result;
    }
    
    /**
     * Получить подчиненные организации (для дерева)
     */
    public function getChildOrganizations($parent_id)
    {
        $parent_id = (int)$parent_id;
        
        $sql = 'SELECT 
                    o.ID_ORG, 
                    o.NAME, 
                    o.ID_PARENT, 
                    o.FLAG,
                    (SELECT COUNT(*) FROM PEOPLE WHERE ID_ORG = o.ID_ORG AND "ACTIVE" = 1) AS PEOPLE_COUNT
                FROM ORGANIZATION o
                WHERE o.ID_PARENT = ' . $parent_id . ' AND o.ID_DB = 1
                ORDER BY o.NAME';
        
        $query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
            ->execute(Database::instance('fb'))
            ->as_array();
        
        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_ORG' => $row['ID_ORG'],
                'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
                'ID_PARENT' => $row['ID_PARENT'],
                'FLAG' => $row['FLAG'],
                'PEOPLE_COUNT' => $row['PEOPLE_COUNT'],
                'CHILDREN' => $this->getChildOrganizations($row['ID_ORG']),
            );
        }
        
        return $result;
    }
	
	/**
 * Получить всех сотрудников с информацией об организации
 */
public function getAllPeopleWithOrgs()
{
    $sql = 'SELECT 
                p.ID_PEP,
                p.SURNAME,
                p.NAME,
                p.PATRONYMIC,
                p.ID_ORG,
                o.NAME AS ORG_NAME,
                p."ACTIVE",
                p.POST
            FROM PEOPLE p
            JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
            WHERE p.ID_ORG NOT IN (2, 3)
            ORDER BY o.NAME, p.SURNAME, p.NAME';
    
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    $result = array();
    foreach ($query as $row) {
        $result[] = array(
            'ID_PEP' => $row['ID_PEP'],
            'SURNAME' => iconv('windows-1251', 'UTF-8', $row['SURNAME']),
            'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
            'PATRONYMIC' => iconv('windows-1251', 'UTF-8', $row['PATRONYMIC']),
            'ID_ORG' => $row['ID_ORG'],
            'ORG_NAME' => iconv('windows-1251', 'UTF-8', $row['ORG_NAME']),
            'ACTIVE' => $row['ACTIVE'],
            'POST' => iconv('windows-1251', 'UTF-8', $row['POST']),
        );
    }
    
    return $result;
}

/**
 * Получить организации с уровнем вложенности
 */
public function getOrganizationsWithLevel()
{
    $sql = 'WITH RECURSIVE org_tree AS (
                SELECT 
                    ID_ORG, 
                    NAME, 
                    ID_PARENT, 
                    0 AS LEVEL
                FROM ORGANIZATION 
                WHERE ID_PARENT = 1 AND ID_DB = 1
                
                UNION ALL
                
                SELECT 
                    o.ID_ORG, 
                    o.NAME, 
                    o.ID_PARENT, 
                    ot.LEVEL + 1
                FROM ORGANIZATION o
                JOIN org_tree ot ON ot.ID_ORG = o.ID_PARENT
                WHERE o.ID_DB = 1
            )
            SELECT ID_ORG, NAME, ID_PARENT, LEVEL
            FROM org_tree
            ORDER BY LEVEL, NAME';
    
    $query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
        ->execute(Database::instance('fb'))
        ->as_array();
    
    $result = array();
    foreach ($query as $row) {
        $result[] = array(
            'ID_ORG' => $row['ID_ORG'],
            'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
            'ID_PARENT' => $row['ID_PARENT'],
            'LEVEL' => $row['LEVEL'],
        );
    }
    
    return $result;
}

/**
 * Получить структуру организации с сотрудниками (для файлового менеджера)
 */
public function getOrgStructure($org_id = 1)
{
    $org_id = (int)$org_id;
    
    // Получаем информацию об организации
    $sql = 'SELECT ID_ORG, NAME, ID_PARENT, FLAG 
            FROM ORGANIZATION 
            WHERE ID_ORG = ' . $org_id;
    
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    if (empty($query)) {
        return null;
    }
    
    $org = $query[0];
    $result = array(
        'ID_ORG' => $org['ID_ORG'],
        'NAME' => iconv('windows-1251', 'UTF-8', $org['NAME']),
        'ID_PARENT' => $org['ID_PARENT'],
        'FLAG' => $org['FLAG'],
        'CHILDREN' => array(),
        'PEOPLE' => array()
    );
    
    // Получаем сотрудников организации
    $sql = 'SELECT 
                ID_PEP,
                SURNAME,
                NAME,
                PATRONYMIC,
                POST,
                PHONEWORK,
                "ACTIVE"
            FROM PEOPLE 
            WHERE ID_ORG = ' . $org_id . '
            AND ID_ORG NOT IN (2, 3)
            ORDER BY SURNAME, NAME';
    
    $people_query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    foreach ($people_query as $person) {
        $result['PEOPLE'][] = array(
            'ID_PEP' => $person['ID_PEP'],
            'SURNAME' => iconv('windows-1251', 'UTF-8', $person['SURNAME']),
            'NAME' => iconv('windows-1251', 'UTF-8', $person['NAME']),
            'PATRONYMIC' => iconv('windows-1251', 'UTF-8', $person['PATRONYMIC']),
            'POST' => iconv('windows-1251', 'UTF-8', $person['POST']),
            'PHONEWORK' => $person['PHONEWORK'],
            'ACTIVE' => $person['ACTIVE'],
        );
    }
    
    // Получаем подорганизации
    $sql = 'SELECT ID_ORG 
            FROM ORGANIZATION 
            WHERE ID_PARENT = ' . $org_id . '
            AND ID_DB = 1
            ORDER BY NAME';
    
    $children_query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    foreach ($children_query as $child) {
        $child_structure = $this->getOrgStructure($child['ID_ORG']);
        if ($child_structure) {
            $result['CHILDREN'][] = $child_structure;
        }
    }
    
    return $result;
}

/**
 * Получить структуру организации для AJAX (только один уровень)
 */
public function getOrgStructureLevel($org_id = 1)
{
    $org_id = (int)$org_id;
    
    $result = array(
        'ID_ORG' => $org_id,
        'CHILDREN' => array(),
        'PEOPLE' => array()
    );
    
    // Получаем название организации
    $sql = 'SELECT NAME FROM ORGANIZATION WHERE ID_ORG = ' . $org_id;
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    if (!empty($query)) {
        $result['NAME'] = iconv('windows-1251', 'UTF-8', $query[0]['NAME']);
    }
    
    // Получаем сотрудников
    $sql = 'SELECT 
                ID_PEP,
                SURNAME,
                NAME,
                PATRONYMIC,
                POST,
                PHONEWORK,
                "ACTIVE"
            FROM PEOPLE 
            WHERE ID_ORG = ' . $org_id . '
            AND ID_ORG NOT IN (2, 3)
            ORDER BY SURNAME, NAME';
    
    $people_query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    foreach ($people_query as $person) {
        $result['PEOPLE'][] = array(
            'ID_PEP' => $person['ID_PEP'],
            'SURNAME' => iconv('windows-1251', 'UTF-8', $person['SURNAME']),
            'NAME' => iconv('windows-1251', 'UTF-8', $person['NAME']),
            'PATRONYMIC' => iconv('windows-1251', 'UTF-8', $person['PATRONYMIC']),
            'POST' => iconv('windows-1251', 'UTF-8', $person['POST']),
            'PHONEWORK' => $person['PHONEWORK'],
            'ACTIVE' => $person['ACTIVE'],
            'TYPE' => 'person'
        );
    }
    
    // Получаем подорганизации
    $sql = 'SELECT ID_ORG, NAME, FLAG 
            FROM ORGANIZATION 
            WHERE ID_PARENT = ' . $org_id . '
            AND ID_DB = 1
            ORDER BY NAME';
    
    $children_query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
        ->execute(Database::instance('fb'))
        ->as_array();
    
    foreach ($children_query as $child) {
        // Проверяем, есть ли у организации дети или сотрудники
        $has_children = $this->hasChildrenOrPeople($child['ID_ORG']);
        
        $result['CHILDREN'][] = array(
            'ID_ORG' => $child['ID_ORG'],
            'NAME' => iconv('windows-1251', 'UTF-8', $child['NAME']),
            'FLAG' => $child['FLAG'],
            'TYPE' => 'org',
            'HAS_CHILDREN' => $has_children,
            'EXPANDED' => false
        );
    }
    
    return $result;
}

/**
 * Проверить, есть ли у организации дети или сотрудники
 */
public function hasChildrenOrPeople($org_id)
{
    $org_id = (int)$org_id;
    
    // Проверяем сотрудников
    $sql = 'SELECT COUNT(*) AS CNT FROM PEOPLE WHERE ID_ORG = ' . $org_id . ' AND ID_ORG NOT IN (2, 3)';
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'));
    $people_count = (int)$query->get('CNT');
    
    if ($people_count > 0) {
        return true;
    }
    
    // Проверяем подорганизации
    $sql = 'SELECT COUNT(*) AS CNT FROM ORGANIZATION WHERE ID_PARENT = ' . $org_id . ' AND ID_DB = 1';
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'));
    $org_count = (int)$query->get('CNT');
    
    return $org_count > 0;
}

/**
 * Получить карты (идентификаторы) сотрудника
 */
public function getPersonCards($id_pep)
{
    $id_pep = (int)$id_pep;
    
    $sql = 'SELECT 
                c.ID_CARD,
                c.ID_CARDTYPE,
                ct.NAME AS CARDTYPE_NAME,
                ct.SMALLNAME AS CARDTYPE_SMALLNAME,
                c.TIMESTART,
                c.TIMEEND,
                c."ACTIVE",
                c.NOTE
            FROM CARD c
            JOIN CARDTYPE ct ON ct.ID = c.ID_CARDTYPE
            WHERE c.ID_PEP = ' . $id_pep . '
            AND c.ID_DB = 1
            ORDER BY c.ID_CARDTYPE, c.ID_CARD';
    
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    $result = array();
    foreach ($query as $row) {
        $result[] = array(
            'ID_CARD' => $row['ID_CARD'],
            'ID_CARDTYPE' => $row['ID_CARDTYPE'],
            'CARDTYPE_NAME' => iconv('windows-1251', 'UTF-8', $row['CARDTYPE_NAME']),
            'CARDTYPE_SMALLNAME' => iconv('windows-1251', 'UTF-8', $row['CARDTYPE_SMALLNAME']),
            'TIMESTART' => $row['TIMESTART'],
            'TIMEEND' => $row['TIMEEND'],
            'ACTIVE' => $row['ACTIVE'],
            'NOTE' => iconv('windows-1251', 'UTF-8', $row['NOTE']),
        );
    }
    
    return $result;
}

/**
 * Получить структуру организации с сотрудниками и их картами
 */
public function getOrgStructureWithCards($org_id = 1)
{
    $org_id = (int)$org_id;
    
    // Получаем информацию об организации
    $sql = 'SELECT ID_ORG, NAME, ID_PARENT, FLAG 
            FROM ORGANIZATION 
            WHERE ID_ORG = ' . $org_id;
    
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    if (empty($query)) {
        return null;
    }
    
    $org = $query[0];
    $result = array(
        'ID_ORG' => $org['ID_ORG'],
        'NAME' => iconv('windows-1251', 'UTF-8', $org['NAME']),
        'ID_PARENT' => $org['ID_PARENT'],
        'FLAG' => $org['FLAG'],
        'CHILDREN' => array(),
        'PEOPLE' => array()
    );
    
    // Получаем сотрудников с картами
    $sql = 'SELECT 
                p.ID_PEP,
                p.SURNAME,
                p.NAME,
                p.PATRONYMIC,
                p.POST,
                p.PHONEWORK,
                p."ACTIVE"
            FROM PEOPLE p
            WHERE p.ID_ORG = ' . $org_id . '
            AND p.ID_ORG NOT IN (2, 3)
            ORDER BY p.SURNAME, p.NAME';
    
    $people_query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    foreach ($people_query as $person) {
        $person_data = array(
            'ID_PEP' => $person['ID_PEP'],
            'SURNAME' => iconv('windows-1251', 'UTF-8', $person['SURNAME']),
            'NAME' => iconv('windows-1251', 'UTF-8', $person['NAME']),
            'PATRONYMIC' => iconv('windows-1251', 'UTF-8', $person['PATRONYMIC']),
            'POST' => iconv('windows-1251', 'UTF-8', $person['POST']),
            'PHONEWORK' => $person['PHONEWORK'],
            'ACTIVE' => $person['ACTIVE'],
            'CARDS' => $this->getPersonCards($person['ID_PEP'])
        );
        $result['PEOPLE'][] = $person_data;
    }
    
    // Получаем подорганизации
    $sql = 'SELECT ID_ORG 
            FROM ORGANIZATION 
            WHERE ID_PARENT = ' . $org_id . '
            AND ID_DB = 1
            ORDER BY NAME';
    
    $children_query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    foreach ($children_query as $child) {
        $child_structure = $this->getOrgStructureWithCards($child['ID_ORG']);
        if ($child_structure) {
            $result['CHILDREN'][] = $child_structure;
        }
    }
    
    return $result;
}

/**
 * Получить структуру организации для AJAX (один уровень) с картами
 */
public function getOrgStructureLevelWithCards($org_id = 1)
{
    $org_id = (int)$org_id;
    
    $result = array(
        'ID_ORG' => $org_id,
        'CHILDREN' => array(),
        'PEOPLE' => array()
    );
    
    // Получаем название организации
    $sql = 'SELECT NAME FROM ORGANIZATION WHERE ID_ORG = ' . $org_id;
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    if (!empty($query)) {
        $result['NAME'] = iconv('windows-1251', 'UTF-8', $query[0]['NAME']);
    }
    
    // Получаем сотрудников с картами - ДОБАВЛЯЕМ p.ID_ORG
    $sql = 'SELECT 
                p.ID_PEP,
                p.SURNAME,
                p.NAME,
                p.PATRONYMIC,
                p.POST,
                p.PHONEWORK,
                p."ACTIVE",
                p.ID_ORG
            FROM PEOPLE p
            WHERE p.ID_ORG = ' . $org_id . '
            AND p.ID_ORG NOT IN (2, 3)
            ORDER BY p.SURNAME, p.NAME';
    
    $people_query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    foreach ($people_query as $person) {
        $person_data = array(
            'ID_PEP' => $person['ID_PEP'],
            'ID_ORG' => $person['ID_ORG'],  // <-- ДОБАВЛЯЕМ ЭТУ СТРОКУ
            'SURNAME' => iconv('windows-1251', 'UTF-8', $person['SURNAME']),
            'NAME' => iconv('windows-1251', 'UTF-8', $person['NAME']),
            'PATRONYMIC' => iconv('windows-1251', 'UTF-8', $person['PATRONYMIC']),
            'POST' => iconv('windows-1251', 'UTF-8', $person['POST']),
            'PHONEWORK' => $person['PHONEWORK'],
            'ACTIVE' => $person['ACTIVE'],
            'CARDS' => $this->getPersonCards($person['ID_PEP']),
            'TYPE' => 'person'
        );
        $result['PEOPLE'][] = $person_data;
    }
    
    // Получаем подорганизации
    $sql = 'SELECT ID_ORG, NAME, FLAG 
            FROM ORGANIZATION 
            WHERE ID_PARENT = ' . $org_id . '
            AND ID_DB = 1
            ORDER BY NAME';
    
    $children_query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
        ->execute(Database::instance('fb'))
        ->as_array();
    
    foreach ($children_query as $child) {
        $has_children = $this->hasChildrenOrPeople($child['ID_ORG']);
        
        $result['CHILDREN'][] = array(
            'ID_ORG' => $child['ID_ORG'],
            'NAME' => iconv('windows-1251', 'UTF-8', $child['NAME']),
            'FLAG' => $child['FLAG'],
            'TYPE' => 'org',
            'HAS_CHILDREN' => $has_children,
            'EXPANDED' => false
        );
    }
    
    return $result;
}

/**
 * Получить все категории доступа
 */
public function getAllAccessNames()
{
    $sql = 'SELECT ID_ACCESSNAME, NAME, TIME_STAMP 
            FROM ACCESSNAME 
            WHERE ID_DB = 1
            ORDER BY NAME';
    
    $query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
        ->execute(Database::instance('fb'))
        ->as_array();
    
    $result = array();
    foreach ($query as $row) {
        $result[] = array(
            'ID_ACCESSNAME' => $row['ID_ACCESSNAME'],
            'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
            'TIME_STAMP' => $row['TIME_STAMP'],
        );
    }
    
    return $result;
}

/**
 * Получить категории доступа организации
 */
public function getOrgAccessNames($id_org)
{
    $id_org = (int)$id_org;
    
    $sql = 'SELECT ID_ACCESSNAME 
            FROM SS_ACCESSORG 
            WHERE ID_ORG = ' . $id_org . '
            AND ID_DB = 1';
    
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    $result = array();
    foreach ($query as $row) {
        $result[] = $row['ID_ACCESSNAME'];
    }
    
    return $result;
}

/**
 * Получить категории доступа сотрудника
 */
public function getPersonAccessNames($id_pep)
{
    $id_pep = (int)$id_pep;
    
    $sql = 'SELECT ID_ACCESSNAME 
            FROM SS_ACCESSUSER 
            WHERE ID_PEP = ' . $id_pep;
    
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    $result = array();
    foreach ($query as $row) {
        $result[] = $row['ID_ACCESSNAME'];
    }
    
    return $result;
}

/**
 * Получить организацию сотрудника
 */
public function getPersonOrganization($id_pep)
{
    $id_pep = (int)$id_pep;
    
    $sql = 'SELECT ID_ORG FROM PEOPLE WHERE ID_PEP = ' . $id_pep;
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    if (empty($query)) {
        return null;
    }
    
    return $query[0]['ID_ORG'];
}

/**
 * Обновить категории доступа для организации
 */
public function updateOrgAccessNames($id_org, $access_ids)
{
    $id_org = (int)$id_org;
    
    // Удаляем старые
    $sql = 'DELETE FROM SS_ACCESSORG WHERE ID_ORG = ' . $id_org;
    DB::query(Database::DELETE, $sql)
        ->execute(Database::instance('fb'));
    
    // Добавляем новые
    if (!empty($access_ids)) {
        $values = array();
        foreach ($access_ids as $access_id) {
            $access_id = (int)$access_id;
            $values[] = '(GEN_ID(GEN_SS_ACCESSORG, 1), 1, ' . $id_org . ', ' . $access_id . ')';
        }
        
        $sql = 'INSERT INTO SS_ACCESSORG (ID_ACCESSORG, ID_DB, ID_ORG, ID_ACCESSNAME) VALUES ' . implode(',', $values);
        DB::query(Database::INSERT, $sql)
            ->execute(Database::instance('fb'));
    }
}

/**
 * Обновить категории доступа для сотрудника
 */
public function updatePersonAccessNames($id_pep, $access_ids)
{
    $id_pep = (int)$id_pep;
    
    // Удаляем старые
    $sql = 'DELETE FROM SS_ACCESSUSER WHERE ID_PEP = ' . $id_pep;
    DB::query(Database::DELETE, $sql)
        ->execute(Database::instance('fb'));
    
    // Добавляем новые
    if (!empty($access_ids)) {
        $values = array();
        foreach ($access_ids as $access_id) {
            $access_id = (int)$access_id;
            $values[] = '(GEN_ID(GEN_SS_ACCESSUSER, 1), 1, ' . $id_pep . ', ' . $access_id . ')';
        }
        
        $sql = 'INSERT INTO SS_ACCESSUSER (ID_ACCESSUSER, ID_DB, ID_PEP, ID_ACCESSNAME) VALUES ' . implode(',', $values);
        DB::query(Database::INSERT, $sql)
            ->execute(Database::instance('fb'));
    }
}


/**
 * Поиск сотрудников по ФИО
 */
public function searchPeople($query)
{
    $query = trim($query);
    
    if (empty($query) || strlen($query) < 2) {
        return array();
    }
    
    // Экранируем кавычки для безопасности
    $query_safe = str_replace("'", "''", $query);
    
    $sql = 'SELECT 
                p.ID_PEP,
                p.SURNAME,
                p.NAME,
                p.PATRONYMIC,
                p.ID_ORG,
                o.NAME AS ORG_NAME
            FROM PEOPLE p
            JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
            WHERE p.ID_ORG NOT IN (2, 3)
            AND (
                p.SURNAME CONTAINING \'' . $query_safe . '\' OR
                p.NAME CONTAINING \'' . $query_safe . '\' OR
                p.PATRONYMIC CONTAINING \'' . $query_safe . '\'
            )
            ORDER BY p.SURNAME, p.NAME';
    
    $query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
        ->execute(Database::instance('fb'))
        ->as_array();
    
    $result = array();
    foreach ($query as $row) {
        $result[] = array(
            'ID_PEP' => $row['ID_PEP'],
            'SURNAME' => iconv('windows-1251', 'UTF-8', $row['SURNAME']),
            'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
            'PATRONYMIC' => iconv('windows-1251', 'UTF-8', $row['PATRONYMIC']),
            'ID_ORG' => $row['ID_ORG'],
            'ORG_NAME' => iconv('windows-1251', 'UTF-8', $row['ORG_NAME']),
        );
    }
    
    return $result;
}



/**
 * Поиск сотрудников по номеру карты (идентификатору)
 */
public function searchByCard($query)
{
    $query = trim($query);
    
    if (empty($query) || strlen($query) < 2) {
        return array();
    }
    
    // Экранируем кавычки для безопасности
    $query_safe = str_replace("'", "''", $query);
    
    $sql = 'SELECT 
                p.ID_PEP,
                p.SURNAME,
                p.NAME,
                p.PATRONYMIC,
                p.ID_ORG,
                o.NAME AS ORG_NAME,
                c.ID_CARD,
                ct.NAME AS CARDTYPE_NAME
            FROM PEOPLE p
            JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
            JOIN CARD c ON c.ID_PEP = p.ID_PEP
            JOIN CARDTYPE ct ON ct.ID = c.ID_CARDTYPE
            WHERE p.ID_ORG NOT IN (2, 3)
            AND (
                c.ID_CARD CONTAINING \'' . $query_safe . '\'
            )
            ORDER BY p.SURNAME, p.NAME';
    
    $query = DB::query(Database::SELECT, iconv('UTF-8', 'windows-1251', $sql))
        ->execute(Database::instance('fb'))
        ->as_array();
    
    $result = array();
    foreach ($query as $row) {
        $result[] = array(
            'ID_PEP' => $row['ID_PEP'],
            'SURNAME' => iconv('windows-1251', 'UTF-8', $row['SURNAME']),
            'NAME' => iconv('windows-1251', 'UTF-8', $row['NAME']),
            'PATRONYMIC' => iconv('windows-1251', 'UTF-8', $row['PATRONYMIC']),
            'ID_ORG' => $row['ID_ORG'],
            'ORG_NAME' => iconv('windows-1251', 'UTF-8', $row['ORG_NAME']),
            'ID_CARD' => $row['ID_CARD'],
            'CARDTYPE_NAME' => iconv('windows-1251', 'UTF-8', $row['CARDTYPE_NAME']),
        );
    }
    
    return $result;
}

}

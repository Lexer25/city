<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Floorplanm extends Model
{
    /**
     * Преобразование ключей массива из верхнего регистра в нижний
     * и конвертация кодировки из Windows-1251 в UTF-8
     */
    private function convertToUtf8($data)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $newKey = is_string($key) ? strtolower($key) : $key;
                
                if (is_array($value)) {
                    $result[$newKey] = $this->convertToUtf8($value);
                } elseif (is_string($value)) {
                    $result[$newKey] = iconv('Windows-1251', 'UTF-8//IGNORE', $value);
                } else {
                    $result[$newKey] = $value;
                }
            }
            return $result;
        } elseif (is_string($data)) {
            return iconv('Windows-1251', 'UTF-8//IGNORE', $data);
        }
        return $data;
    }

    /**
     * Получить список планов
     */
    public function getFloorplans()
    {
        $sql = 'SELECT fp.id_floorplan, fp.name, fp.description, fp.image, fp.width, fp.height,
                       (SELECT COUNT(*) FROM floorplan_point fp2 WHERE fp2.id_floorplan = fp.id_floorplan) as points_count
                FROM floorplan fp
                ORDER BY fp.name';

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        return $this->convertToUtf8($query);
    }

    /**
     * Получить план по ID
     */
    public function getFloorplanById($id)
    {
        $sql = 'SELECT fp.id_floorplan, fp.name, fp.description, fp.image, fp.width, fp.height
                FROM floorplan fp
                WHERE fp.id_floorplan = ' . intval($id);

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        if (count($query) > 0) {
            $result = $this->convertToUtf8($query);
            return $result[0];
        }

        return null;
    }

    /**
     * Получить все точки прохода для плана
     */
    public function getPointsByFloorplan($floorplanId)
    {
        $sql = 'SELECT fp.id_point, fp.x_pos, fp.y_pos, fp.id_dev, fp.point_type, fp.label,
                       d.name as device_name, d.id_reader
                FROM floorplan_point fp
                LEFT JOIN device d ON fp.id_dev = d.id_dev
                WHERE fp.id_floorplan = ' . intval($floorplanId) . '
                ORDER BY fp.point_type, fp.label';

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        return $this->convertToUtf8($query);
    }

    /**
     * Получить устройства для выбора (только с ридером)
     */
    public function getAvailableDevices()
    {
        $sql = 'SELECT d.id_dev, d.name, d.id_reader
                FROM device d
                WHERE d.id_reader IS NOT NULL
                ORDER BY d.name';

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        return $this->convertToUtf8($query);
    }

    /**
     * Добавить новый план
     */
    public function addFloorplan($name, $description, $image, $width, $height)
    {
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        $nameForDb = addslashes($nameForDb);
        $descForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $description);
        $descForDb = addslashes($descForDb);

        $sql = "INSERT INTO floorplan (name, description, image, width, height)
                VALUES ('{$nameForDb}', '{$descForDb}', '{$image}', " . intval($width) . ", " . intval($height) . ")";

        try {
            $result = DB::query(Database::INSERT, $sql)
                ->execute(Database::instance('fb'));

            $lastId = DB::query(Database::SELECT, "SELECT MAX(id_floorplan) as last_id FROM floorplan")
                ->execute(Database::instance('fb'))
                ->as_array();

            return $lastId[0]['LAST_ID'];
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error adding floorplan: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Обновить план
     */
    public function updateFloorplan($id, $name, $description, $image, $width, $height)
    {
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        $nameForDb = addslashes($nameForDb);
        $descForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $description);
        $descForDb = addslashes($descForDb);

        $sql = "UPDATE floorplan
                SET name = '{$nameForDb}',
                    description = '{$descForDb}',
                    image = '{$image}',
                    width = " . intval($width) . ",
                    height = " . intval($height) . "
                WHERE id_floorplan = " . intval($id);

        try {
            DB::query(Database::UPDATE, $sql)
                ->execute(Database::instance('fb'));

            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error updating floorplan: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Удалить план
     */
    public function deleteFloorplan($id)
    {
        try {
            $db = Database::instance('fb');

            // Удаляем все точки плана
            DB::query(Database::DELETE,
                "DELETE FROM floorplan_point WHERE id_floorplan = " . intval($id))
                ->execute($db);

            // Удаляем сам план
            DB::query(Database::DELETE,
                "DELETE FROM floorplan WHERE id_floorplan = " . intval($id))
                ->execute($db);

            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error deleting floorplan: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Добавить точку на план
     */
    public function addPoint($floorplanId, $x, $y, $deviceId, $point_type = 'door', $label = '')
    {
        $labelForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $label);
        $labelForDb = addslashes($labelForDb);

        $sql = "INSERT INTO floorplan_point (id_floorplan, x_pos, y_pos, id_dev, point_type, label)
                VALUES (" . intval($floorplanId) . ", " . floatval($x) . ", " . floatval($y) . ", " . intval($deviceId) . ", '{$point_type}', '{$labelForDb}')";

        try {
            $result = DB::query(Database::INSERT, $sql)
                ->execute(Database::instance('fb'));

            $lastId = DB::query(Database::SELECT, "SELECT MAX(id_point) as last_id FROM floorplan_point")
                ->execute(Database::instance('fb'))
                ->as_array();

            return $lastId[0]['LAST_ID'];
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error adding point: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Обновить точку
     */
    public function updatePoint($id, $x, $y, $deviceId, $point_type, $label)
    {
        $labelForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $label);
        $labelForDb = addslashes($labelForDb);

        $sql = "UPDATE floorplan_point
                SET x_pos = " . floatval($x) . ",
                    y_pos = " . floatval($y) . ",
                    id_dev = " . intval($deviceId) . ",
                    point_type = '{$point_type}',
                    label = '{$labelForDb}'
                WHERE id_point = " . intval($id);

        try {
            DB::query(Database::UPDATE, $sql)
                ->execute(Database::instance('fb'));

            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error updating point: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Удалить точку
     */
    public function deletePoint($id)
    {
        try {
            $sql = "DELETE FROM floorplan_point WHERE id_point = " . intval($id);
            DB::query(Database::DELETE, $sql)
                ->execute(Database::instance('fb'));

            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error deleting point: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Сохранить позиции точек (массовое обновление)
     */
    public function savePointsPositions($points)
    {
        try {
            $db = Database::instance('fb');

            foreach ($points as $point) {
                $id = (int)$point['id'];
                $x = (float)$point['x'];
                $y = (float)$point['y'];

                $sql = "UPDATE floorplan_point
                        SET x_pos = " . $x . ", y_pos = " . $y . "
                        WHERE id_point = " . $id;

                DB::query(Database::UPDATE, $sql)->execute($db);
            }

            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error saving points positions: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверить, существует ли план
     */
    public function floorplanExists($id)
    {
        $sql = "SELECT COUNT(*) as cnt FROM floorplan WHERE id_floorplan = " . intval($id);

        $result = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        return ($result[0]['CNT'] > 0);
    }
}
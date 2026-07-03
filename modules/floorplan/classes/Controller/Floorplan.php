<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Floorplan extends Controller_Template { 
	
	public $template = 'template';
    public function before()
    {
        parent::before();
		$session = Session::instance();
		  $this->is_admin = Auth::instance()->logged_in('admin');
        View::bind_global('is_admin', $this->is_admin); 
		
    }

    /**
     * Список планов
     */
    public function action_index()
    {
        $model = Model::factory('Floorplanm');
        $floorplans = $model->getFloorplans();

        $content = View::factory('floorplan/index', array(
            'floorplans' => $floorplans,
            'is_admin' => $this->is_admin,
        ));
//echo Debug::vars('27', $this);exit;
        $this->template->content = $content;
    }

    /**
     * Просмотр плана
     */
    public function action_view()
    {
        $id = (int)$this->request->param('id', 0);
        $model = Model::factory('Floorplanm');

        if (!$id || !$model->floorplanExists($id)) {
            $this->redirect('floorplan');
        }

        $floorplan = $model->getFloorplanById($id);
        $points = $model->getPointsByFloorplan($id);

        // Получаем статусы устройств (имитация)
        $deviceStatuses = $this->getDeviceStatuses($points);

        $content = View::factory('floorplan/edit', array(
            'floorplan' => $floorplan,
            'points' => $points,
            'deviceStatuses' => $deviceStatuses,
            'availableDevices' => $model->getAvailableDevices(),
            'is_admin' => $this->is_admin,
            'mode' => 'view',
        ));

        $this->template->full_width = true;
        $this->template->content = $content;
    }

    /**
     * Редактирование плана
     */
    public function action_edit()
    {
        $id = (int)$this->request->param('id', 0);
        $model = Model::factory('Floorplanm');

        if (!$this->is_admin) {
            $this->redirect('floorplan');
        }

        if (!$id || !$model->floorplanExists($id)) {
            $this->redirect('floorplan');
        }

        $floorplan = $model->getFloorplanById($id);
        $points = $model->getPointsByFloorplan($id);

        // Обработка POST запроса
        if ($this->request->method() == HTTP_Request::POST) {
            $post = $this->request->post();
            $action = Arr::get($post, 'action');

            if ($action == 'update_plan') {
                $name = Arr::get($post, 'name');
                $description = Arr::get($post, 'description');
                $width = Arr::get($post, 'width', 800);
                $height = Arr::get($post, 'height', 600);

                $result = $model->updateFloorplan($id, $name, $description, $floorplan['image'], $width, $height);

                if ($result) {
                    Session::instance()->set('message', 'План успешно обновлен');
                    Session::instance()->set('message_type', 'success');
                }
                $this->redirect('floorplan/edit/' . $id);
            }

            if ($action == 'add_point') {
                $x = Arr::get($post, 'x', 0);
                $y = Arr::get($post, 'y', 0);
                $deviceId = Arr::get($post, 'device_id', 0);
                $point_type = Arr::get($post, 'point_type', 'door');
                $label = Arr::get($post, 'label', '');

                if ($deviceId > 0) {
                    $model->addPoint($id, $x, $y, $deviceId, $point_type, $label);
                    Session::instance()->set('message', 'Точка добавлена');
                    Session::instance()->set('message_type', 'success');
                }
                $this->redirect('floorplan/edit/' . $id);
            }

            if ($action == 'delete_point') {
                $pointId = Arr::get($post, 'point_id', 0);
                if ($pointId > 0) {
                    $model->deletePoint($pointId);
                    Session::instance()->set('message', 'Точка удалена');
                    Session::instance()->set('message_type', 'success');
                }
                $this->redirect('floorplan/edit/' . $id);
            }
        }

        $content = View::factory('floorplan/edit', array(
            'floorplan' => $floorplan,
            'points' => $points,
            'availableDevices' => $model->getAvailableDevices(),
            'is_admin' => $this->is_admin,
            'mode' => 'edit',
        ));

        $this->template->full_width = true;
        $this->template->content = $content;
    }

    /**
     * AJAX: Сохранение позиций точек
     */
    public function action_savePositions()
    {
        $this->auto_render = false;
        header('Content-Type: application/json');

        if (!$this->is_admin) {
            echo json_encode(array('success' => false, 'error' => 'Доступ запрещён'));
            return;
        }

        if ($this->request->method() != HTTP_Request::POST) {
            echo json_encode(array('success' => false, 'error' => 'Invalid request method'));
            return;
        }

        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        $points = isset($data['points']) ? $data['points'] : array();

        if (empty($points)) {
            echo json_encode(array('success' => true, 'message' => 'Нет изменений'));
            return;
        }

        $model = Model::factory('Floorplanm');
        $result = $model->savePointsPositions($points);

        echo json_encode(array('success' => $result));
    }

    /**
     * AJAX: Добавление точки
     */
    public function action_addPointAjax()
    {
        $this->auto_render = false;
        header('Content-Type: application/json');

        if (!$this->is_admin) {
            echo json_encode(array('success' => false, 'error' => 'Доступ запрещён'));
            return;
        }

        if ($this->request->method() != HTTP_Request::POST) {
            echo json_encode(array('success' => false, 'error' => 'Invalid request method'));
            return;
        }

        $floorplanId = (int)$this->request->post('floorplan_id');
        $x = (float)$this->request->post('x');
        $y = (float)$this->request->post('y');
        $deviceId = (int)$this->request->post('device_id');
        $point_type = $this->request->post('point_type', 'door');
        $label = $this->request->post('label', '');

        if ($floorplanId <= 0 || $deviceId <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Invalid parameters'));
            return;
        }

        $model = Model::factory('Floorplanm');
        $result = $model->addPoint($floorplanId, $x, $y, $deviceId, $point_type, $label);

        if ($result) {
            echo json_encode(array('success' => true, 'id' => $result));
        } else {
            echo json_encode(array('success' => false, 'error' => 'Ошибка при добавлении точки'));
        }
    }

    /**
     * AJAX: Удаление точки
     */
    public function action_deletePointAjax()
    {
        $this->auto_render = false;
        header('Content-Type: application/json');

        if (!$this->is_admin) {
            echo json_encode(array('success' => false, 'error' => 'Доступ запрещён'));
            return;
        }

        if ($this->request->method() != HTTP_Request::POST) {
            echo json_encode(array('success' => false, 'error' => 'Invalid request method'));
            return;
        }

        $pointId = (int)$this->request->post('point_id');

        if ($pointId <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Invalid point ID'));
            return;
        }

        $model = Model::factory('Floorplanm');
        $result = $model->deletePoint($pointId);

        echo json_encode(array('success' => $result));
    }

    /**
     * Добавление нового плана
     */
    public function action_add()
    {
        if (!$this->is_admin) {
            $this->redirect('floorplan');
        }

        $model = Model::factory('Floorplanm');

        if ($this->request->method() == HTTP_Request::POST) {
            $post = $this->request->post();

            $name = Arr::get($post, 'name');
            $description = Arr::get($post, 'description');

            // Обработка загрузки изображения
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $uploadDir = DOCROOT . 'media/floorplan/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'floorplan_' . time() . '.' . $ext;
                $targetPath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = 'media/floorplan/' . $filename;
                }
            }

            if (empty($name) || empty($image)) {
                $errors = array();
                if (empty($name)) $errors['name'] = 'Название обязательно';
                if (empty($image)) $errors['image'] = 'Изображение обязательно';

                $content = View::factory('floorplan/add', array(
                    'errors' => $errors,
                    'post' => $post,
                    'is_admin' => $this->is_admin,
                ));
                $this->template->content = $content;
                return;
            }

            // Получаем размеры изображения
            $imageInfo = getimagesize($uploadDir . $filename);
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            $result = $model->addFloorplan($name, $description, $image, $width, $height);

            if ($result) {
                Session::instance()->set('message', 'План успешно добавлен');
                Session::instance()->set('message_type', 'success');
                $this->redirect('floorplan/edit/' . $result);
            } else {
                Session::instance()->set('message', 'Ошибка при добавлении плана');
                Session::instance()->set('message_type', 'danger');
            }
        }

        $content = View::factory('floorplan/add', array(
            'errors' => array(),
            'post' => array(),
            'is_admin' => $this->is_admin,
        ));

        $this->template->content = $content;
    }

    /**
     * Удаление плана
     */
    public function action_delete()
    {
        if (!$this->is_admin) {
            $this->redirect('floorplan');
        }

        $id = (int)$this->request->param('id', 0);
        $model = Model::factory('Floorplanm');

        if ($id && $model->floorplanExists($id)) {
            $model->deleteFloorplan($id);
            Session::instance()->set('message', 'План удален');
            Session::instance()->set('message_type', 'success');
        }

        $this->redirect('floorplan');
    }

    /**
     * Получить статусы устройств (имитация)
     */
    private function getDeviceStatuses($points)
    {
        $statuses = array();
        foreach ($points as $point) {
            $deviceId = $point['id_dev'];
            if ($deviceId) {
                // В реальном проекте здесь запрос к API или БД
                $statuses[$deviceId] = array(
                    'status' => 'online',
                    'mode' => 'normal',
                    'last_event' => date('Y-m-d H:i:s'),
                );
            }
        }
        return $statuses;
    }
}

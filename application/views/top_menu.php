<!-- Static navbar -->
<div class="navbar navbar-default navbar-fixed-top disable">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <?= HTML::anchor('dashboard', __('City'), array('class' => 'navbar-brand')) ?>
    </div>
    <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
            <?php
            if (Session::instance()->get('skud_number')) {
                if ((Kohana::$config->load('artonitcity_config')->view_without_auth['load']) || (Auth::instance()->logged_in())) { ?>
                    <li <?php if ($_SESSION['menu_active'] == 'load') echo 'class="active"'; ?>>
                        <?= HTML::anchor('dashboard/load', __('Load')) ?>
                    </li>
                <?php }

                if ((Kohana::$config->load('artonitcity_config')->view_without_auth['load_order']) || (Auth::instance()->logged_in())) { ?>
                    <li <?php if ($_SESSION['menu_active'] == 'load_order') echo 'class="active"'; ?>>
                        <?= HTML::anchor('dashboard/load_order', __('Load_order')) ?>
                    </li>
                <?php }

                if ((Kohana::$config->load('artonitcity_config')->view_without_auth['device_control']) || (Auth::instance()->logged_in())) { ?>
                    <li <?php if ($_SESSION['menu_active'] == 'device_control') echo 'class="active"'; ?>>
                        <?= HTML::anchor('dashboard/device_control', __('device_control')) ?>
                    </li>
                <?php }

                if ((Kohana::$config->load('artonitcity_config')->view_without_auth['events']) || (Auth::instance()->logged_in())) { ?>
                    <li <?php if (Arr::get($_SESSION, 'menu_active') == 'events') echo 'class="active"'; ?>>
                        <?= HTML::anchor('event', __('events')) ?>
                    </li>
                <?php }

                if ((Kohana::$config->load('artonitcity_config')->view_without_auth['people']) || (Auth::instance()->logged_in())) { ?>
                    <li <?php if (Arr::get($_SESSION, 'menu_active') == 'people') echo 'class="active"'; ?>>
                        <?= HTML::anchor('people/peopleInfo', __('people')) ?>
                    </li>
                <?php }

                if ((Kohana::$config->load('artonitcity_config')->view_without_auth['door']) || (Auth::instance()->logged_in())) { ?>
                    <li <?php if (Arr::get($_SESSION, 'menu_active') == 'door') echo 'class="active"'; ?>>
                        <?= HTML::anchor('door/doorInfo', __('door')) ?>
                    </li>
                <?php }

                if ((Kohana::$config->load('artonitcity_config')->view_without_auth['log']) || (Auth::instance()->logged_in())) { ?>
                    <li <?php if (Arr::get($_SESSION, 'menu_active') == 'log') echo 'class="active"'; ?>>
                        <?= HTML::anchor('dashboard/log', __('log')) ?>
                    </li>
                <?php } ?>

                <li><?= HTML::anchor('', __('__')) ?></li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo __('guide'); ?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><?= HTML::anchor('guide', __('guide')) ?></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo __('Настройка'); ?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><?= HTML::anchor('ts', __('Транспортные серверы')) ?></li>
                        <li><?= HTML::anchor('./', __('reserv')) ?></li>
                        <li class="divider"></li>
                        <li><?= HTML::anchor('device', __('Контроллеры')) ?></li>
                    </ul>
                </li>

                <li <?php if (Arr::get($_SESSION, 'menu_active') == 'services') echo 'class="active"'; ?>>
                    <? //= HTML::anchor('dashboard/services', __('services')) ?>
                    <?= HTML::anchor('eximdata', __('Export/Import')) ?>
                </li>

                <li <?php if (Arr::get($_SESSION, 'menu_active') == 'skud') echo 'class="active"'; ?>>
                    <?= HTML::anchor('skud', __('сводная')) ?>
                </li>

                <?php include Kohana::find_file('views', 'apb', 'menu'); ?>
            <?php } ?>
        </ul>

        <ul class="nav navbar-nav navbar-right">
            <li>
                <?php
                if (Session::instance()->get('skud_number')) {
                    if (Auth::Instance()->logged_in()) {
                        echo 'Пользователь ' . Auth::instance()->get_user();
                        echo '<div>' . HTML::anchor('logout', __('logout'), array('onclick' => 'return confirm(\'' . __('confirm.delete') . '\')')) . '</div>';
                    } else {
                        echo Form::open('dashboard', array('method' => 'post', 'class' => 'form-inline')); ?>
                        <div class="form-group">
                            <label for="inputEmail" class="sr-only">Имя</label>
                            <input type="text" class="form-control input-sm" id="inputEmail" placeholder="Имя" name="username">
                        </div>
                        <div class="form-group">
                            <label for="inputPassword" class="sr-only">Пароль</label>
                            <input type="password" class="form-control input-sm" id="inputPassword" placeholder="Пароль" name="password">
                        </div>
                        <div class="checkbox input-sm">
                            <label><input type="checkbox" name="remember"> Запомнить</label>
                        </div>
                        <button type="submit" class="btn btn-primary input-sm">Войти</button>
                        <?php echo Form::close();
                    }
                } ?>
            </li>
        </ul>
    </div>

    <div class="navbar-collapse collapse">
        <?php
        if (!is_null(Session::instance()->get('skud_number'))) {
            echo __('string_about', array(
                'db' => Arr::get(
                    Arr::get(
                        Kohana::$config->load('database')->fb,
                        'connection'
                    ),
                    'dsn'
                ),
                'ver' => Kohana::$config->load('artonitcity_config')->ver,
                'developer' => Kohana::$config->load('artonitcity_config')->developer,
            )) . '<br>';
            echo __('timerefresh', array('tr' => date("d.m.Y H:i", time())));
        } else {
            echo __('no_select_skud');
        }
        ?>
    </div>
</div>
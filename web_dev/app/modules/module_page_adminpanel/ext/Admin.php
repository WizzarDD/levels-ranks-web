<?php
/**
 * @author Anastasia Sidak <m0st1ce.nastya@gmail.com>
 *
 * @link https://steamcommunity.com/profiles/76561198038416053
 * @link https://github.com/M0st1ce
 *
 * @license GNU General Public License Version 3
 */

class Admin {

    function __construct( $General, $Modules, $Db ) {

        // Ведущая проверка.
        ( empty( $_SESSION['steamid32'] ) || empty( $_GET['page'] ) || $_GET['page'] != 'adminpanel' || $_SESSION['steamid32'] != $General->arr_general['admin'] ) && die();

        $this->General = $General;

        $this->Modules = $Modules;

        $this->Db = $Db;
    }

    /**
     * Полностью очистить кэш вэб-приложения вклюсая кэш модулей.
     */
    function action_clear_all_cache() {
        // Ссылки на кэшируемые файлы.
        $cache_files = [ 'modules_cache' => SESSIONS . 'modules_cache.php',
            'translator_cache' => SESSIONS . 'translator_cache.php',
            'css_and_js_actual_library_cache' => SESSIONS . 'actual_library.json',
            'css_cache' => ASSETS_CSS . '/generation/style_generated.min.ver.' . $this->Modules->actual_library['actual_css_ver'] . '.css',
            'js_cache' => ASSETS_JS . '/generation/app_generated.min.ver.' . $this->Modules->actual_library['actual_js_ver'] . '.js'
        ];

        // Очищаем кэш каждого модуля.
        for ( $i = 0; $i < $this->Modules->array_modules_count; $i++ ):
            $module = array_keys( $this->Modules->array_modules )[ $i ];

            // При существовании файла кэша, удалить его.
            file_exists( MODULES . $module . '/temp/cache.php' ) && unlink(MODULES . $module . '/temp/cache.php');
        endfor;

        // Удаляем файл с описанием каждого модуля.
        file_exists( $cache_files['modules_cache'] ) && unlink( $cache_files['modules_cache'] );

        // Удаляем файл с переводами.
        file_exists( $cache_files['translator_cache'] ) && unlink( $cache_files['translator_cache'] );

        // Удаляем файл с информацией о актульаных версиях кэша стилей и JS библиотек.
        file_exists( $cache_files['css_and_js_actual_library_cache'] ) && unlink( $cache_files['css_and_js_actual_library_cache'] );

        // Удаляем файл с генерируемыми стилями.
        file_exists( $cache_files['css_cache'] ) && unlink( $cache_files['css_cache'] );

        // Удаляем файл с генерируемыми JS библиотекой.
        file_exists( $cache_files['js_cache'] ) && unlink( $cache_files['js_cache'] );

        // Обновление страницы.
        refresh();
    }

    /**
     * Очистить порядок загрузки модулей.
     */
    function action_clear_modules_initialization() {
        // Ссылки на кэшируемые файлы.
        $cache_files = ['modules_initialization' => SESSIONS . 'modules_initialization.php'];

        // Удаляем файл с порядком инициализации модулей.
        file_exists( $cache_files['modules_initialization'] ) && unlink( $cache_files['modules_initialization'] );

        // Очистка кэша вэб-приложения.
        $this->action_clear_all_cache();
    }

    /**
     * Редактирования порядка загрузки модулей.
     */
    function edit_modules_initialization() {

        $array = $this->Modules->arr_module_init;

        $data =  json_decode( $_POST['data'], true );

        for ( $i2 = 0, $c = sizeof( $data ); $i2 < $c; $i2++ ) {
            $_data[] = $data[ $i2 ]['id'];
        }

        $_GET['module_page'] == 'sidebar' ? $array['sidebar'] = $_data : $array['page'][ get_section( 'module_page', 'home' ) ]['interface'] = $_data;

        file_put_contents( SESSIONS . 'modules_initialization.php', '<?php return '.var_export_min( $array, true ).";" );
    }

    /**
     * Очистить кэш переводов.
     */
    function action_clear_translator_cache() {
        // Ссылки на кэшируемые файлы.
        $cache_files = ['translator_cache' => SESSIONS . 'translator_cache.php'];

        // Удаляем файл с порядком инициализации модулей.
        file_exists( $cache_files['translator_cache'] ) && unlink( $cache_files['translator_cache'] );

        // Обновление страницы.
        refresh();
    }

    /**
     * Изменение параметров в '/storage/cache/sessions/options.php'.
     */
    function edit_options() {

        $arr = $this->General->arr_general;

        $option = [
            'full_name' => $_POST['full_name'],
            'short_name' => $_POST['short_name'],
            'info' => $_POST['info'],
            'language' => $_POST['language'],
            'web_key' => $_POST['web_key'],
            'admin' => $_POST['admin'],
            'avatars' => (int) $_POST['avatars'],
            'avatars_cache_time' => (int) $_POST['avatars_cache_time']
        ];

        // Обновление файла.
        file_put_contents( SESSIONS . 'options.php', '<?php return '.var_export_min( array_replace($arr, $option), true ).";" );

        // Обновление страницы.
        refresh();
    }

    /**
     * Изменение параметров в '/storage/cache/sessions/options.php'.
     */
    function action_db_add_mods() {

        $db = $Db->db;

        $db += [ $_POST['mod'] => [] ];

        // Обновление файла.
        file_put_contents( SESSIONS . 'db.php', '<?php return '.var_export_min( $db, true ).";" );

        // Обновление страницы.
        refresh();
    }

    /**
     * Добавление сервера в '/storage/cache/sessions/servers_list.php'.
     */
    function action_add_server() {

        $server = [];

        $server[0]['name'] = $_POST['server_name'];
        $server[0]['ip'] = $_POST['server_ip_port'];
        $server[0]['fakeip'] = $_POST['server_ip_port_fake'];
        $server[0]['rcon'] = $_POST['server_rcon'];
        $server[0]['server_stats'] = $_POST['server_stats'];
        $server[0]['server_vip'] = $_POST['server_vip'];
        $server[0]['server_vip_id'] = empty( $_POST['server_vip_id'] ) ? 0 : $_POST['server_vip_id'];
        $server[0]['server_sb'] = $_POST['server_sb'];
        $server[0]['server_shop'] = $_POST['server_shop'];
        $server[0]['server_warnsystem'] = $_POST['server_warnsystem'];

        empty( $this->General->server_list ) || ! is_array( $this->General->server_list ) ? $arr_servers[0] = $server[0] : $arr_servers = array_merge( $this->General->server_list, $server );

        // Обновление файла.
        file_put_contents( SESSIONS . 'servers_list.php', '<?php return '.var_export_min( $arr_servers, true ).";" );

        // Обновление страницы.
        refresh();
    }

    /**
     * Редактирование параметров определенного модуля.
     */
    function edit_module() {

        $Module_data = $this->Modules->array_modules[ $_GET['options'] ];

        // А где цикл то, что за беспредел? :D
        $Module_data['page'] = $_POST['module_page'];
        $Module_data['setting']['status'] = $_POST['module_offon'] == 'on' ? 1 : 0;
        $Module_data['setting']['type'] = (int) $_POST['module_type'];
        $Module_data['setting']['translation'] = $_POST['module_translation'] == 'on' ? 1 : 0;
        $Module_data['setting']['interface'] = $_POST['module_interface'] == 'on' ? 1 : 0;
        $Module_data['setting']['data'] = $_POST['module_data'] == 'on' ? 1 : 0;
        $Module_data['setting']['data_always'] = $_POST['module_data_always'] == 'on' ? 1 : 0;
        $Module_data['setting']['css'] = $_POST['module_css'] == 'on' ? 1 : 0;
        $Module_data['setting']['js'] = $_POST['module_js'] == 'on' ? 1 : 0;
        $Module_data['setting']['cache_enable'] = $_POST['module_cache_enable'] == 'on' ? 1 : 0;
        $Module_data['setting']['cache_time'] = (int) $_POST['module_cache_time'];

        file_put_contents( MODULES . $_GET['options'] . '/description.json', json_encode( $Module_data, JSON_UNESCAPED_UNICODE ) );

        $modules_init = $this->Modules->arr_module_init;

        if( ! empty( $Module_data['sidebar'] ) && ! in_array( $_GET['options'], $modules_init['sidebar'] ) ) {
            $modules_init['sidebar'] =+ $_GET['options'];
        }

        file_put_contents( SESSIONS . '/modules_initialization.php', '<?php return '.var_export_min( $modules_init, true ).";" );

        $this->action_clear_all_cache();
    }
}
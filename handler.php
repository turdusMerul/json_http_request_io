<?php
    /*  
    ----------------------------------------------------------------------------------------
    
    Обработчик входящих HTTP-запросов, передающихся в JSON-формате
    Актуальная версия в репозитории: https://github.com/turdusMerul/json_http_request_io.git
    
    ----------------------------------------------------------------------------------------
    */
    

    # GLPI токены сгенерированные в настройках
    $app_token = '';
    $user_app_token ='';
    
    # Тип источника запроса (значение из справочника, которое запомнили чуть раньше)
    $requesttypes_id = '5';
    
    # Код типа запроса GLPI (1 - Инцидент, 2 - Запрос)
    $type = '2';
    

    # Обработка входящего запроса
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    

    /* # Запись в лог-файл
    $log_file = fopen("income.log", "w") or die("Unable to open file");
    fwrite($log_file, $income);
    fclose($log_file); */


    # Инициализация пользовательской сессии в glpi
    if( $curl = curl_init() ) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: user_token '.$user_app_token, 'App-Token: '.$app_token));
        curl_setopt($curl, CURLOPT_URL, 'https://sd.morozdgkb.ru/apirest.php/initSession');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($curl);
        $session = json_decode($out, true);
        $session_token = $session["session_token"];
        curl_close($curl);
    }

    # Подготовка текста JSON-запроса создания заявки
    $ticket_data = array(
        'input'=> array(
            'name'=>'Заявка от '.$data["user_name"], # заголовок
            'requesttypes_id'=>$requesttypes_id, # id клиента, создающего заявку
            'content'=>$data, # содержимое заявки
            'type'=>$type # тип заявки
        )
    );

    # Отправка запроса
    if( $curl = curl_init() ) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'App-Token: '.$app_token, 'Session-token: '.$session_token));
        curl_setopt($curl, CURLOPT_URL, 'https://sd.morozdgkb.ru/apirest.php/Ticket');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($ticket_data));
        $out = curl_exec($curl);
        curl_close($curl);
    }

    # Закрытие сессии
    if( $curl = curl_init() ) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'App-Token: '.$app_token, 'Session-token: '.$session_token));
        curl_setopt($curl, CURLOPT_URL, 'https://sd.morozdgkb.ru/apirest.php/killSession');
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($curl);
        curl_close($curl);
    }
?>

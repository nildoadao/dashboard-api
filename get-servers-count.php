<?php
include('db-helper.php');

switch ($_SERVER["REQUEST_METHOD"]){
    case 'GET':
        $servers_count = array();
        $servers_count = get_number_of_servers();
        $response_data = json_encode( $servers_count);
        send_response($response_data, 200);
        break;

    default:
        send_response("Método não permitido", 405);
        break;
}

function send_response($content, $status_code){
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code($status_code);
    echo $content;
}

function get_number_of_servers(){
    try{
        $connection = build_connection();
        $servers_count = array();

        $query = "SELECT COUNT(*) as total FROM hostdb WHERE ambiente=?";
        $result = db_query($connection, $query, array('s', 'DEV'));
        $servers_count['DEV'] = $result[0]['total'];

        $query = "SELECT COUNT(*) as total FROM hostdb WHERE ambiente=?";
        $result = db_query($connection, $query, array('s', 'QA'));
        $servers_count['QA'] = $result[0]['total'];

        $query = "SELECT COUNT(*) as total FROM hostdb WHERE ambiente=?";
        $result = db_query($connection, $query, array('s', 'UAT'));
        $servers_count['UAT'] = $result[0]['total'];

        $query = "SELECT COUNT(*) as total FROM hostdb WHERE ambiente=?";
        $result = db_query($connection, $query, array('s', 'CERT'));
        $servers_count['CERT'] = $result[0]['total'];

        $servers_count['TOTAL'] = $servers_count['DEV'] + $servers_count['QA'] + $servers_count['UAT'] + $servers_count['CERT'];
        close_connection($connection);
        return $servers_count;
        

    } catch(Exception $e) {
        send_response("Falha ao conectar no banco" . $e->getMessage(), 500);
        close_connection($connection);
        exit();
    } 
}

?>

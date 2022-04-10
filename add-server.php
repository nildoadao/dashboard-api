<?php
include('db-helper.php');


switch ($_SERVER["REQUEST_METHOD"]){  

    case 'POST':
        if(check_request_body()){            
            add_server();
            send_response("Servidor adicionado com sucesso !", 201);
        }

        else send_response("Solicitação mal formatada", 400);

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


function check_request_body(){
    try{
        $input = (array) json_decode(file_get_contents("php://input"), TRUE);

        if(!isset($input['hostname'])){
            return false;
        }
        if(!isset($input['ambiente'])){
            return false;
        }
        if(!isset($input['sistema'])){
            return false;
        }
        return true;

    } catch(Exception $e){
        send_response("Solicitação mal formatada " . $e->getMessage(), 400);
        exit();
    }
}

function add_server(){ 
    try{
        $connection = build_connection();
        $input = (array) json_decode(file_get_contents("php://input"), TRUE);
        $query = "INSERT INTO hostdb (hostname, ambiente, sistema) VALUES (?,?,?)";
        $result = db_select($connection, $query, array('sss', $input['hostname'], $input['ambiente'], $input['sistema']));
        close_connection($connection);
        return $result;

    } catch(Exception $e){
        send_response("Solicitação mal formatada " . $e->getMessage(), 400);
        close_connection($connection);
        exit();
    }
}

?>

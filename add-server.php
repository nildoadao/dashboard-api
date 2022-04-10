<?php
include('db-helper.php');


switch ($_SERVER["REQUEST_METHOD"]){  

    case 'POST':
        $result = add_server();                      
        send_response(json_encode($result), 201);
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
    $connection = build_connection();
    $query = "SELECT * FROM hostdb WHERE hostname=?";
    $result = db_query($connection, $query, array('s', $input['hostname']));
    close_connection($connection);
        
    if(!empty($result))
        throw new InvalidArgumentException("Servidor com hostaname: " . $input['hostname'] ." já existe.");

    return true;
}

function add_server(){ 
    try{

        if(!check_request_body()){
            send_response("Solicitação mal formatada.", 400);
            exit();
        }

        $connection = build_connection();
        $input = (array) json_decode(file_get_contents("php://input"), TRUE);
        $query = "INSERT INTO hostdb (hostname, ambiente, sistema) VALUES (?,?,?)";
        $result = db_query($connection, $query, array('sss', $input['hostname'], $input['ambiente'], $input['sistema']));
        close_connection($connection);
        return $result;

    } catch(InvalidArgumentException $iex){
        send_response("Falha ao cadastrar servidor: " . $iex->getMessage(), 400);
        close_connection($connection);
        exit();
    } catch(Exception $e){
        send_response("Solicitação mal formatada " . $e->getMessage(), 400);
        close_connection($connection);
        exit();
    }
}

?>

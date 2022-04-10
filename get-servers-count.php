<?php
include('bd.php');

function build_connection(){
    try{
        $connection = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_DATABASE_NAME);
        if (mysqli_connect_errno()){
            echo "Falha ao conectar no banco " . mysqli_connect_error();
            http_response_code(500);
            exit();
        }
        return $connection;
    } catch (Exception $e){
        echo "Falha ao conectar no banco " . $e->getMessage();
        http_response_code(500);
        exit();
    }
}

function close_connection($connection){

    if($connection != null){
        mysqli_close($connection);
    }
}

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
        $result = db_select($connection, $query, "s", "DEV");
        $servers_count['DEV'] = $result[0]['total'];

        $query = "SELECT COUNT(*) as total FROM hostdb WHERE ambiente=?";
        $result = db_select($connection, $query, "s", "QA");
        $servers_count['QA'] = $result[0]['total'];

        $query = "SELECT COUNT(*) as total FROM hostdb WHERE ambiente=?";
        $result = db_select($connection, $query, "s", "UAT");
        $servers_count['UAT'] = $result[0]['total'];

        $query = "SELECT COUNT(*) as total FROM hostdb WHERE ambiente=?";
        $result = db_select($connection, $query, "s", "CERT");
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

function db_select($connection, $query, $param_types, ...$params){
    $statement = $connection->prepare($query);

    if($param_types != ""){
        $bind_string = "";
        $bind_string = $bind_string.join(", ", $params);
        $statement->bind_param($param_types, $bind_string);
    }

    if($statement === false){
        throw new Exception("Falha ao preparar a query");
    }
    
    $statement->execute();
    $statement->store_result();
    $result = array();

    for( $i = 0; $i < $statement->num_rows; $i++ ){
        $metadata = $statement->result_metadata();
        $params = array();
        while ($field = $metadata->fetch_field())
        {
            $params[] = &$result[$i][$field->name];
        }
        call_user_func_array(array($statement, 'bind_result'), $params);
        $statement->fetch();
    }
    $statement->close();
    return $result;
}

?>

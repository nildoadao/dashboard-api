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
        $servers_count['TOTAL'] = get_number_of_servers();
        $servers_count['DEV'] = get_server_by_amb('DEV');
        $servers_count['QA'] = get_server_by_amb('QA');
        $servers_count['CERT'] = get_server_by_amb("CERT");
        $servers_count['UAT'] = get_server_by_amb('UAT');
        $response_data = json_encode( $servers_count);
        echo $response_data;
        break;

    default:
        http_response_code(405);
        echo "Método não permitido";
        break;
}

function get_number_of_servers(){
    $connection = build_connection();
    $query = "SELECT COUNT(*) as total FROM hostdb";
    try{
        $statement = $connection->prepare($query);

        if($statement === false){
            echo "Falha ao preparar query";
            http_response_code(500);
            exit();
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
        close_connection($connection);
        return $result[0]['total'];

    } catch(Exception $e) {
        echo "Falha ao conectar no banco" . $e->getMessage();
        http_response_code(500);
        close_connection($connection);
        exit();
    } 
}

function get_server_by_amb($ambiente){
    $connection = build_connection();
    $query = "SELECT COUNT(*) as total FROM hostdb WHERE ambiente=?";
    try{
        $statement = $connection->prepare($query);
        $statement->bind_param("s", $ambiente);

        if($statement === false){
            echo "Falha ao preparar query";
            http_response_code(500);
            exit();
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
        close_connection($connection);
        return $result[0]['total'];

    } catch(Exception $e) {
        echo "Falha ao conectar no banco" . $e->getMessage();
        http_response_code(500);
        close_connection($connection);
        exit();
    } 
}

?>

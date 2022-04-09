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
        if($_GET['server_id'] != ""){
            $result = get_server_by_id($_GET['server_id']);
            $response_data = json_encode($result);
            echo $response_data;
        }
        elseif(isset($_GET['get_all_servers'])){
            $result = get_number_of_servers();
            $response_data = json_encode($result);
            echo $response_data;
        }
        break;
    
    case 'POST':
        if(valida_user_data()){
            add_server();
            echo "Servidor adicionado com sucesso !";
            http_response_code(202);
        }
        else{
            echo "Solicitação mal formatada";
            http_response_code(400);
        }
        break;

    default:
        http_response_code(400);
        echo "Solicitação mal formatada";
        break;
}

function get_server_by_name($server_name){
    $connection = build_connection();
    $query = "SELECT * FROM hostdb WHERE hostname=?";    

    try{
        $statement = $connection->prepare($query);
        $statement->bind_param("s", $server_name);

        if($statement === false){
            echo "Falha ao conectar no banco";
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
        return $result;

    } catch(Exception $e) {
        echo "Falha ao conectar no banco" . $e->getMessage();
        close_connection($connection);
        http_response_code(500);
        exit();
    } 
}

function valida_user_data(){
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
        http_response_code(400);
        echo "Solicitação mal formatada " . $e->getMessage();
        exit();
    }
}

function add_server(){
    $connection = build_connection();
    $input = (array) json_decode(file_get_contents("php://input"), TRUE);
    $query = "INSERT INTO hostdb (hostname, ambiente, sistema) VALUES (?,?,?)";
    
    try{
        $statement = $connection->prepare($query);
        $statement->bind_param("sss", $input['hostname'], $input['ambiente'], $input['sistema']);

        if($statement === false){
            echo "Falha ao conectar no banco";
            http_response_code(500);
            exit();
        }

        $statement->execute();
        $statement->close();
        close_connection($connection);

    } catch(Exception $e){
        http_response_code(400);
        echo "Solicitação mal formatada " . $e->getMessage();
        exit();
    }
}

function get_server_by_id($server_id){
    $connection = build_connection();
    $query = "SELECT * FROM hostdb WHERE id=?";
    try{
        $statement = $connection->prepare($query);
        $statement->bind_param("i", $server_id);

        if($statement === false){
            echo "Falha ao conectar no banco";
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
        return $result;

    } catch(Exception $e) {
        echo "Falha ao conectar no banco" . $e->getMessage();
        http_response_code(500);
        close_connection($connection);
        exit();
    } 
}

function get_number_of_servers(){
    $connection = build_connection();
    $query = "SELECT COUNT(*) FROM hostdb";
    try{
        $statement = $connection->prepare($query);

        if($statement === false){
            echo "Falha ao conectar no banco";
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
        return $result;

    } catch(Exception $e) {
        echo "Falha ao conectar no banco" . $e->getMessage();
        http_response_code(500);
        close_connection($connection);
        exit();
    } 
}

?>

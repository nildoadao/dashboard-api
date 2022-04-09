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
        $result = get_number_of_servers();
        $response_data = json_encode($result);
        echo $response_data;
        break;

    default:
        http_response_code(405);
        echo "Método não permitido";
        break;
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
                //$params[] = &$result['Total'][$field->name];
                $count = $field->name["COUNT(*)"];
            }
            call_user_func_array(array($statement, 'bind_result'), $params);
            $statement->fetch();
        }
        $result['Total'] = $count;
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

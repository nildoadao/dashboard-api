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

function db_insert($connection, $query, $param_types, ...$params){
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
    $statement->close();
}
?>
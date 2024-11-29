<?php
    include "config/db.php";

    // Header
    header("Content-Type: application/json");

    // HTTP Verb
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    // if existing, gagawin niya i2 (after ng ?) else (after ng :)
    $request = isset($_GET['request']) ? explode("/", trim($_GET['request'], "/")) : [];

    $requestMethod;

    $taskID = isset($_GET["id"]) ? trim($_GET["id"],"/") : null;

    switch($requestMethod){
        case 'POST':
            createTask();
            break;
        case 'GET':
            $taskID ? oneTask($taskID) : allTask();
            break;
        case 'PUT':
            updateTask($taskID);
            break;
        case 'DELETE':
            $taskID ? deleteTask($taskID) : deleteTasks();
            break;
        case 'PATCH':
            patchTask($taskID);
            break;
        default:
            http_response_code(response_code: 405);
            echo json_encode(["message" => "Method Not Existing"]);
            break;
    }

mysqli_close($connection);
?>  

<?php
    function createTask(){
        global $connection;

        $data = json_decode(file_get_contents("php://input"), true);
        $title = $data['title'];
        $description = $data['description'];

        if(!empty($title)) {
            $sql = "INSERT INTO task (title, description) VALUES ('$title', '$description')";

            if(mysqli_query($connection, $sql)){
                http_response_code(response_code: 201);
                echo json_encode(["message" => "Task Created Succesfully"]);
            }else{
                http_response_code(response_code: 500);
                echo json_encode(["message" => "Error Creating Tasks"]);
            }
            
        }else{
            http_response_code(response_code: 400);
            echo json_encode(["message" => "Title Required"]);
        }
    }

    function allTask(){
        global $connection;

        $sql = "SELECT * FROM task";
        $result = mysqli_query($connection, $sql);
        $task = mysqli_fetch_all($result, MYSQLI_ASSOC);

        if(mysqli_num_rows($result) > 0){
            echo json_encode($task);  
        }else{
            http_response_code(response_code: 404);
            echo json_encode(["message" => "No Tasks Found"]);
        }
    }

    function oneTask($taskID){
        global $connection;
        
        $sql = "SELECT * FROM task WHERE id = $taskID";
        $result = mysqli_query($connection, $sql);
        $task = mysqli_fetch_assoc($result);
        
        if($task){
            echo json_encode($task);
        }else{
            http_response_code(response_code: 404);
            echo json_encode(["message" => "Task Not Found"]);
        }
    }
    
    function updateTask($taskID){
        global $connection;
        
        $data = json_decode(file_get_contents("php://input"), true);
        $title = $data['title'];
        $description = $data['description'];
        
        if(!empty($title) && !empty($description)){
            $sql = "UPDATE task SET title='$title', description='$description' WHERE id=$taskID";
            
            if(mysqli_query($connection, $sql)){
                http_response_code(response_code: 200);
                echo json_encode(["message" => "Task Updated Successfully"]);
            }else{
                http_response_code(response_code: 500);
                echo json_encode(["message" => "Error Updating Task"]);
            }
        }else{
            http_response_code(response_code: 400);
            echo json_encode(["message" => "Title and Description Required"]);
        }
    }
    
    function deleteTask($taskID){
        global $connection;
        
        $sql = "DELETE FROM task WHERE id=$taskID";
        
        if(mysqli_query($connection, $sql)){
            http_response_code(response_code: 200);
            echo json_encode(["message" => "Task Deleted Successfully"]);
        }else{
            http_response_code(response_code: 500);
            echo json_encode(["message" => "Error Deleting Task"]);
        }
    }

    function deleteTasks(){
        global $connection;
        
        $sql = "DELETE FROM task";
        
        if(mysqli_query($connection, $sql)){
            http_response_code(response_code: 200);
            echo json_encode(["message" => "Tasks Deleted Successfully"]);
        }else{
            http_response_code(response_code: 500);
            echo json_encode(["message" => "Error Deleting Tasks"]);
        }
    }
    function patchTask($taskID){
        global $connection;
        
        $data = json_decode(file_get_contents("php://input"), true);
        $title = isset($data['title']) ? $data['title'] : '';
        $description = isset($data['description']) ? $data['description'] : '';
        
        if(!empty($title) ||!empty($description)){
            $update_fields = [];
            
            if(!empty($title))
                $update_fields[] = "title='$title'";
            
            if(!empty($description))
                $update_fields[] = "description='$description'";
            
            $sql = "UPDATE task SET ". implode(", ", $update_fields). " WHERE id=$taskID";
            
            if(mysqli_query($connection, $sql)){
                http_response_code(response_code: 200);
                echo json_encode(["message" => "Task Updated Successfully"]);
            }else{
                http_response_code(response_code: 500);
                echo json_encode(["message" => "Error Updating Task"]);
            }
        }else{
            http_response_code(response_code: 400);
            echo json_encode(["message" => "At least Title or Description Required"]);
        }
    }
?>

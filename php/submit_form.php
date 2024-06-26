<?php
session_start();

include("connection.php");
include("functions.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == "POST") {
   
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    switch ($action) {
        case 'register':
            
            handleRegistration($conn);
            break;
        
        case 'login':
          
            handleLogin($conn);
            break;
        
        case 'reset_password':
            
            handlePasswordReset($conn);
            break;
        
        default:
            echo "Invalid action.";
            break;
    }
}

function handleRegistration($conn) {
    $surname = $_POST['surname'];
    $other_names = $_POST['other_names'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $mobile_number = $_POST['mobile_number'];
    $security_question = $_POST['security_question'];
    $security_answer = $_POST['security_answer'];
    

    if (!empty($surname) && !empty($other_names) && !empty($username) && !empty($password) && !empty($email) && !empty($mobile_number) && !empty($security_question) && !empty($security_answer)) {
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
       
        if ($stmt = $conn->prepare("INSERT INTO user (surname, other_names, username, password, email, mobile_number, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param("ssssssss", $surname, $other_names, $username, $hashed_password, $email, $mobile_number, $security_question, $security_answer);
        
          
            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            }
        } else {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
    } else {
        echo "Please fill all the required fields.";
    }
}


function handleLogin($conn) {
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
   
    if (!empty($username) && !empty($password)) {
       
        if ($stmt = $conn->prepare("SELECT * FROM user WHERE username = ?")) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: dashboard.php");
                    exit();
                } else {
                    echo "Invalid username or password.";
                }
            } else {
                echo "Invalid username or password.";
            }
        } else {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
    } else {
        echo "Please fill all the required fields.";
    }
}

function handlePasswordReset($conn) {
    // Retrieve form data
    $username = $_POST['username'];
    $security_question = $_POST['security_question'];
    $security_answer = $_POST['security_answer'];
    $new_password = $_POST['new_password'];
    
    // Validate form data
    if (!empty($username) && !empty($security_question) && !empty($security_answer) && !empty($new_password)) {
        // Prepare an SQL statement to fetch user data based on username and security question
        if ($stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND security_question = ? AND security_answer = ?")) {
            $stmt->bind_param("sss", $username, $security_question, $security_answer);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                // User found, update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                if ($update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?")) {
                    $update_stmt->bind_param("ss", $hashed_password, $username);
                    if ($update_stmt->execute()) {
                        echo "Password reset successful. You can now <a href='login.php'>login</a> with your new password.";
                    } else {
                        echo "Execute failed: (" . $update_stmt->errno . ") " . $update_stmt->error;
                    }
                } else {
                    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                }
            } else {
                echo "Invalid username or security question/answer combination.";
            }
        } else {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
    } else {
        echo "Please fill all the required fields.";
    }
}
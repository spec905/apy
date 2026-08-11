<?php
session_start();
require_once 'cnfg.php';

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $country = $_POST['country'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered';
        $_SESSION['active_form'] = 'register';
        header("Location: acc.php");
        exit();
    }

    $insert = $conn->prepare("INSERT INTO users(name,email,password,country) VALUES(?,?,?,?)");
    $insert->bind_param("ssss", $name, $email, $password, $country);
    $insert->execute();

    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['country'] = $country;
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['country'] = $user['country'];
            header("Location: index.php");
            exit();
        }
    }

    $_SESSION['login_error'] = 'incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: acc.php");
    exit();
}
?>
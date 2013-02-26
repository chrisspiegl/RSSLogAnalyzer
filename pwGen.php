<?php
/**
 * pwGen.php
 *
 * @author Christoph Spiegl <chris@chrissp.com>
 * @package default
 */


if (isset($_GET['pw'])): ?>
    <h1>Your encrypted password is:</h1>
    <br />
    <p><?=sha1($_GET['pw']); ?></p>
    <br />
    <hr />
<?php endif; ?>

<h1>This works via browser 'GET' parameters</h1>
<h2>Usage:</h2>
<ol>
    <li>Copy the following URL and replace <em>MYPASSWORD</em> with your password.</li>
    <li>Copy the passwor that will be shown in your browser window and place it into the config file.</li>
</ol>

<h1><?=$_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']; ?>?pw=MYPASSWORD</h1>

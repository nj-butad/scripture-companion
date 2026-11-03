<?php

if (empty($_SESSION['auth_logged_in'])) {
    ?>
    <a class="account-nav" href="/account">Sign in</a>
    <?php
    return;
}

$accountMenuCsrf = $_SESSION['account_csrf'] ??= bin2hex(random_bytes(32));
?>
<details class="account-menu">
    <summary>My Scripture <span class="account-menu-chevron" aria-hidden="true">⌄</span></summary>
    <nav class="account-menu-panel" aria-label="Your account">
        <a href="/feed">For you</a>
        <a href="/saved">Saved Scripture</a>
        <a href="/notifications">Notifications</a>
        <a href="/settings">Preferences</a>
        <form action="/account/logout" method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($accountMenuCsrf, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Sign out</button>
        </form>
    </nav>
</details>

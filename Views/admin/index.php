<header role="banner">
    <h1>Admin Panel</h1>
    <ul class="utilities">
        <li class="users"><a href="#">My Account</a></li>
        <li class="logout warn"><a href="">Log Out</a></li>
    </ul>
</header>

<nav role='navigation'>
    <ul class="main">
        <li class="dashboard"><a href="admindashboard">Dashboard</a></li>
        <li class="edit"><a href="www.thecoderszone.com/a/">Edit Website</a></li>
        <li class="write"><a href="writenews.php">Write news</a></li>
        <li class="comments"><a href="shorte.st/">Ads</a></li>
        <li class="users"><a href="manageusers.php">Manage Users</a></li>
        <li class="edit"><a href="cpanel.hostinger.co.uk">Hostinger</a></li>
    </ul>
</nav>

<main role="main">
    <section class="panel important">
        <h2>Write Some News</h2>
        <ul>
            <li>Write some news for the users to see.</li>
        </ul>
    </section>

    <section class="panel important">
        <h2>Write a post</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="twothirds">
                News title:<br/>
                <input type="text" name="title" size="40"/><br/><br/>
                Content:<br/>
                <textarea name="newstext" rows="15" cols="67"></textarea><br/>
            </div>
            <div>
                <input type="submit" name="submit" value="Save" />
            </div>
        </form>
    </section>



</main>
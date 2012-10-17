{*
The MIT License

Copyright (c) 2011 Eric Parsons

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Smarty is licensed under the GNU LESSER GENERAL PUBLIC LICENSE
http://www.gnu.org/licenses/lgpl-3.0.txt

V-- Tab to here *}
<!DOCTYPE html>
<html>
    <head>
        <title>{$page_title}</title>
        <link href="/_style/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <link href="/_style/print.css" media="print" rel="stylesheet" type="text/css" />
        <!--[if IE]>
            <link href="/_style/ie.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <![endif]-->
        {foreach $javascript_files as $javascript_file}
        <script src="{$javascript_file}" type="text/javascript"></script>
        {/foreach}
        {foreach $javascript_texts as $javascript_text}
        <script type="text/javascript">{$javascript_text}</script>
        {/foreach}
    </head>
    <body>
        <section id="container">
            <header id="header">
                <img src="/_media/logo_default.png" width="490" height="107" class="site_logo" />
                <div class="login">
                    {if $logged_in}
                        Welcome, {$username} (<a href="/login.php?action=logout">Logout</a>)
                    {else}
                        <form method="post" action="/login.php?action=login&url={$redirect_url}">
                            <span class="username_field">
                                Username: <input type="text" name="username" size="15" />
                            </span>
                            <span class="password_field">
                                Password: <input type="password" name="password" size="15" />
                            </span>
                            <br />
                            <a href="">Register</a>
                            <input type="submit" value="Login" />
                        </form>
                    {/if}
                </div>
            </header>
            <div id="main">
                <nav id="horizontal_nav">
                    <ul>
                        <li><a href="">Account</a></li>
                        <li><a href="/items/view_items.php{if $logged_in}?u={$user_id}{/if}">My Items</a></li>
                    </ul>
                    <div id="quick_search">
                        <form action="/search.php" method="get">
                            <input type="text" name="search" size="15" />
                            <input type="submit" class="search_submit" value="Search" />
                        </form>
                    </div>
                </nav>
                <div id="contentContainer">
                    <nav id="side_nav">
                        <ul>
                            <li><a href="/items/view_items.php{if $logged_in}?u={$user_id}{/if}">View items</a></li>
                            <li><a href="/items/pdf_inventory.php">Export Inventory</a></li>
                            <li><a href="/items/add_item.php">Add item</a></li>
                            <li><a href="/items/add_item_type.php">Add Item Type</a></li>
                            <li><a href="/items/add_attribute.php">Add Attribute</a></li>
                            <li><a href="/items/popular_items.php">Popular Items</a></li>
                            <li><a href="/items/recent_activity.php">Recent Activity</a></li>
                            <li><a href="/items/item_recommendations.php">Recommendations</a></li>
                            <li><a href="/user/history.php">User Activity</a></li>
                        </ul>
                    </nav>
                    <div id="content">
                        <h1>{$page_title}</h1>

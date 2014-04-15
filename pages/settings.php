<div class="wrap">

    <h1>WP Bikmo Products Settings</h1>

    <form method="post" action="options.php">
        <?php settings_fields('wp_plugin_bikmo-products'); ?>
        <table class="form-table"> 
            <col style="width: 300px;">
            <tr valign="top">
                <th scope="row">
                    <label for="clickref">Clickref (provided by <a href="http://bikmo.com">Bikmo</a>)</label>
                </th>
                <td>
                    <input type="text" name="clickref" id="clickref" value="<?php echo get_option('clickref'); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="site-url">Site Url (eg : http://search.bikmo.com)</label>
                </th>
                <td>
                    <input type="text" name="site-url" id="site-url" value="<?php echo get_option('site-url'); ?>" />
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
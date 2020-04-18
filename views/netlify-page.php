<h2>Netlify Deployment Options</h2>

<h3>Netlify</h3>

<form
    name="wp2static-netlify-save-options"
    method="POST"
    action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">

    <?php wp_nonce_field( $view['nonce_action'] ); ?>
    <input name="action" type="hidden" value="wp2static_netlify_save_options" />

<table class="widefat striped">
    <tbody>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['siteID']->name; ?>"
                ><?php echo $view['options']['siteID']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['siteID']->name; ?>"
                    name="<?php echo $view['options']['siteID']->name; ?>"
                    type="text"
                    value="<?php echo $view['options']['siteID']->value !== '' ? $view['options']['siteID']->value : ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['accessToken']->name; ?>"
                ><?php echo $view['options']['accessToken']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['accessToken']->name; ?>"
                    name="<?php echo $view['options']['accessToken']->name; ?>"
                    type="password"
                    value="<?php echo $view['options']['accessToken']->value !== '' ?
                        \WP2Static\CoreOptions::encrypt_decrypt('decrypt', $view['options']['accessToken']->value) :
                        ''; ?>"
                />
            </td>
        </tr>

    </tbody>
</table>

<br>

    <button class="button btn-primary">Save Netlify Options</button>
</form>


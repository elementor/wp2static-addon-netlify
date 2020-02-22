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
                    for="<?php echo $view['options']['netlifyBucket']->name; ?>"
                ><?php echo $view['options']['netlifyBucket']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['netlifyBucket']->name; ?>"
                    name="<?php echo $view['options']['netlifyBucket']->name; ?>"
                    type="text"
                    value="<?php echo $view['options']['netlifyBucket']->value !== '' ? $view['options']['netlifyBucket']->value : ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['netlifyRegion']->name; ?>"
                ><?php echo $view['options']['netlifyRegion']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['netlifyRegion']->name; ?>"
                    name="<?php echo $view['options']['netlifyRegion']->name; ?>"
                    type="text"
                    value="<?php echo $view['options']['netlifyRegion']->value !== '' ? $view['options']['netlifyRegion']->value : ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['netlifyAccessKeyID']->name; ?>"
                ><?php echo $view['options']['netlifyAccessKeyID']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['netlifyAccessKeyID']->name; ?>"
                    name="<?php echo $view['options']['netlifyAccessKeyID']->name; ?>"
                    value="<?php echo $view['options']['netlifyAccessKeyID']->value !== '' ? $view['options']['netlifyAccessKeyID']->value : ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['netlifySecretAccessKey']->name; ?>"
                ><?php echo $view['options']['netlifySecretAccessKey']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['netlifySecretAccessKey']->name; ?>"
                    name="<?php echo $view['options']['netlifySecretAccessKey']->name; ?>"
                    type="password"
                    value="<?php echo $view['options']['netlifySecretAccessKey']->value !== '' ?
                        \WP2StaticNetlify\Controller::encrypt_decrypt('decrypt', $view['options']['netlifySecretAccessKey']->value) :
                        ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['netlifyProfile']->name; ?>"
                ><?php echo $view['options']['netlifyProfile']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['netlifyProfile']->name; ?>"
                    name="<?php echo $view['options']['netlifyProfile']->name; ?>"
                    type="text"
                    value="<?php echo $view['options']['netlifyProfile']->value !== '' ? $view['options']['netlifyProfile']->value : ''; ?>"
                />
            </td>
        </tr>


        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['netlifyRemotePath']->name; ?>"
                ><?php echo $view['options']['netlifyRemotePath']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['netlifyRemotePath']->name; ?>"
                    name="<?php echo $view['options']['netlifyRemotePath']->name; ?>"
                    type="text"
                    value="<?php echo $view['options']['netlifyRemotePath']->value !== '' ? $view['options']['netlifyRemotePath']->value : ''; ?>"
                />
            </td>
        </tr>

    </tbody>
</table>


<h3>CloudFront</h3>

<table class="widefat striped">
    <tbody>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['cfRegion']->name; ?>"
                ><?php echo $view['options']['cfRegion']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['cfRegion']->name; ?>"
                    name="<?php echo $view['options']['cfRegion']->name; ?>"
                    type="text"
                    value="<?php echo $view['options']['cfRegion']->value !== '' ? $view['options']['cfRegion']->value : ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['cfAccessKeyID']->name; ?>"
                ><?php echo $view['options']['cfAccessKeyID']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['cfAccessKeyID']->name; ?>"
                    name="<?php echo $view['options']['cfAccessKeyID']->name; ?>"
                    value="<?php echo $view['options']['cfAccessKeyID']->value !== '' ? $view['options']['cfAccessKeyID']->value : ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['cfSecretAccessKey']->name; ?>"
                ><?php echo $view['options']['cfSecretAccessKey']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['cfSecretAccessKey']->name; ?>"
                    name="<?php echo $view['options']['cfSecretAccessKey']->name; ?>"
                    type="password"
                    value="<?php echo $view['options']['cfSecretAccessKey']->value !== '' ?
                        \WP2StaticNetlify\Controller::encrypt_decrypt('decrypt', $view['options']['cfSecretAccessKey']->value) :
                        ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['cfProfile']->name; ?>"
                ><?php echo $view['options']['cfProfile']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['cfProfile']->name; ?>"
                    name="<?php echo $view['options']['cfProfile']->name; ?>"
                    type="text"
                    value="<?php echo $view['options']['cfProfile']->value !== '' ? $view['options']['cfProfile']->value : ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['cfDistributionID']->name; ?>"
                ><?php echo $view['options']['cfDistributionID']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['cfDistributionID']->name; ?>"
                    name="<?php echo $view['options']['cfDistributionID']->name; ?>"
                    type="text"
                    value="<?php echo $view['options']['cfDistributionID']->value !== '' ? $view['options']['cfDistributionID']->value : ''; ?>"
                />
            </td>
        </tr>


    </tbody>
</table>

<br>

    <button class="button btn-primary">Save Netlify Options</button>
</form>


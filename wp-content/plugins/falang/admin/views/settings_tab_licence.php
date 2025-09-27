<table class="form-table">
    <tbody>
    <tr>
        <th><?php _e('License', 'falang'); ?></th>
        <td>
            <label>
                <input type="text" name="downloadid" size="25" value="<?php esc_attr_e($falang_model->get_option('downloadid','')); ?>" />
                <?php _e('License for Falang/Falang for Elementor/Yootheme/Divi... ', 'falang') ?>
            </label>
        </td>
    </tr>
    </tbody>
</table>
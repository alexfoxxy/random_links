<?php
class LinksRotation {
    public static function rotation_weight_dropdown($rotation_weight, $select_name="url_rotation") {
        ?>
        <select name="<?php echo esc_attr($select_name); ?>">
            <?php for($p=0; $p<=100; $p+=5) { ?>
                <option value="<?php echo esc_attr($p); ?>"<?php selected((int)$p, (int)$rotation_weight); ?>><?php echo esc_html($p); ?>%&nbsp;</option>
            <?php } ?>
        </select>
        <?php
    }

    public static function rotation_row($rotation, $weight, $select_name="url_rotations", $weight_select_name="url_rotation") {
        ?>
        <li>
            <input type="text" class="regular-text" name="<?php echo esc_attr($select_name); ?>" value="<?php echo esc_attr($rotation); ?>" />
            <?php esc_html_e('weight:', 'pretty-link'); ?>
            <?php self::rotation_weight_dropdown($weight); ?>
        </li>
        <?php
    }
}
?>
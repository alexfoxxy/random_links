<div>
    <ol id="prli_link_rotations">
        <li>
            <input style="..." type="text" name="category-url" id="category-url" />
            <?php esc_html_e('weight:', 'default'); ?>
            <?php require_once ('class/LinksRotation.php');
            $target_url_weight = 0;
            LinksRotation::rotation_weight_dropdown((($target_url_weight == 0 || !empty($target_url_weight))?$target_url_weight:'100'),'target_url_weight'); ?>
        </li>
        <?php
        for($i=0;$i<count($url_rotations);$i++) {
            $rotation = ((isset($url_rotations[$i]) && !empty($url_rotations[$i]))?$url_rotations[$i]:'');
            $weight   = (isset($url_rotation_weights[$i])?$url_rotation_weights[$i]:0);
            LinksRotation::rotation_row($rotation, $weight, 'url_rotations[]', 'url_rotation_weights[]');
        }
        ?>
    </ol>
    <div><a id="prli_add_link_rotation" href=""><?php esc_html_e('Add Link Rotation', 'pretty-link'); ?></a></div>
</div>

<ol id="prli_link_rotations">
    <li>
        <input style="..." type="text" name="category-url" id="category-url" />
        <?php esc_html_e('weight:', 'default'); ?>
        <?php require_once ('class/LinksRotation.php');
        $target_url_weight = 0;
        LinksRotation::rotation_weight_dropdown((($target_url_weight == 0 || !empty($target_url_weight))?$target_url_weight:'100'),'target_url_weight'); ?>
    </li>
    <?php
    /*for($i=0;$i<count($url_rotations);$i++) {
        $rotation = ((isset($url_rotations[$i]) && !empty($url_rotations[$i]))?$url_rotations[$i]:'');
        $weight   = (isset($url_rotation_weights[$i])?$url_rotation_weights[$i]:0);
        LinksRotation::rotation_row($rotation, $weight, 'url_rotations[]', 'url_rotation_weights[]');
    }*/
    ?>
</ol>
<div><a id="add_link_rotation" href=""><?php esc_html_e('Add Link Rotation', 'default'); ?></a></div>

<script type="text/javascript">
    $('select').on('change',function(){
        var sum = 0;
        $('#link_rotations select option:selected').each(function(){
            var intVal = parseInt($(this).text());
            sum += intVal;
        });
        if (sum!==100){
            alert('Вся сумма, должна быть 100');
        }else{
            alert('Все хорошо!');
        }
    });
</script>
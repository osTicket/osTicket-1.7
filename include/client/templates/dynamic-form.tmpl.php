    <tr><th colspan="2"> </th></tr>
    <?php
    global $thisclient;
    foreach ($form->getFields() as $field) {
        if ($thisclient) {
            switch ($field->get('name')) {
                case 'name':
                    $field->value = $thisclient->getName();
                    break;
                case 'email':
                    $field->value = $thisclient->getEmail();
                    break;
                case 'phone':
                    $field->value = $thisclient->getPhone();
                    break;
            }
        }
        if ($field->get('private'))
            continue;
        ?>
        <tr><td class="<?php if ($field->get('required')) echo 'required'; ?>">
            <?php echo $field->get('label'); ?>:</td>
            <td><?php $field->render(); ?>
            <?php if ($field->get('required')) { ?>
                <font class="error">*</font>
            <?php 
            }
            foreach ($field->errors() as $e) { ?>
                <font class="error"><?php echo $e; ?></font>
            <?php } ?>
            </td>
        </tr>
        <?php
    }
?>

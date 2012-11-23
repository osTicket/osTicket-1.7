    <tr><th colspan="2">
        <em><strong><?php echo $form->getTitle(); ?><strong></em>
    </th></tr>
    <?php
    foreach ($form->getFields() as $field) {
        ?>
        <tr><td class="<?php if ($field->get('required')) echo 'required'; ?>">
            <?php echo $field->get('label'); ?></td>
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

    <tr><th colspan="2">
        <em><strong><?php echo Format::htmlchars($form->getTitle()); ?></strong>:
        <?php echo Format::htmlchars($form->getInstructions()); ?></em>
    </th></tr>
    <?php
    foreach ($form->getFields() as $field) {
        ?>
        <tr><td class="multi-line <?php if ($field->get('required')) echo 'required'; ?>">
            <?php echo Format::htmlchars($field->get('label')); ?>:</td>
            <td><?php $field->render(); ?>
            <?php if ($field->get('required')) { ?>
                <font class="error">*</font>
            <?php 
            }
            if ($field->get('hint')) { ?>
                <br /><em style="color:gray;display:inline-block"><?php
                    echo Format::htmlchars($field->get('hint')); ?></em>
            <?php
            }
            foreach ($field->errors() as $e) { ?>
                <br />
                <font class="error"><?php echo $e; ?></font>
            <?php } ?>
            </td>
        </tr>
        <?php
    }
?>

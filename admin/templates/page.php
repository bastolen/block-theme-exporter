<div class="wrap">
  <h1><?= __('Block Theme exporter', 'b6n-bte') ?></h1>
  <section>
    <h2><?= __('Import', 'b6n-bte') ?></h2>
    <?php
    $bytes = apply_filters('import_upload_size_limit', wp_max_upload_size());
    $size = size_format($bytes);
    $upload_dir = wp_upload_dir();
    if (!empty($upload_dir['error'])) {
    ?>
      <div class="error">
        <p><?= __('Before you can upload your import file, you will need to fix the following error:'); ?></p>
        <p><strong><?= $upload_dir['error']; ?></strong></p>
      </div>
    <?php } else { ?>
      <form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form" action="<?= esc_url('tools.php?page=b6n-bte'); ?>">
        <p>
          <?=
          sprintf(
            '<label for="upload">%s</label> (%s)',
            __('Choose a file from your computer:'),
            sprintf(__('Maximum size: %s'), $size)
          );
          ?>
          <input type="file" id="upload" name="file" accept="application/json" />
          <input type="hidden" name="action" value="import" />
          <input type="hidden" name="page" value="b6n-bte" />
          <?php wp_nonce_field('bte-import') ?>
        </p>
        <?php submit_button(__('Upload file and import'), 'primary'); ?>
      </form>
    <?php } ?>
  </section>
  <hr />
  <section>
    <h2><?= __('Export', 'b6n-bte') ?></h2>
    <form method="POST" class="wp-export-form" action="<?= esc_url('tools.php?page=b6n-bte'); ?>">
      <p>
        <span><?= __('Choose the items you want to export from this site:', 'b6n-bte') ?></span>
      <div>
        <input type="checkbox" id="styles" name="category[]" value="styles" />
        <label for="styles"><?= __('Global Styles') ?></label>
      </div>
      <div>
        <input type="checkbox" id="parts" name="category[]" value="parts" />
        <label for="parts"><?= __('Template Parts') ?></label>
      </div>
      <div>
        <input type="checkbox" id="templates" name="category[]" value="templates" />
        <label for="templates"><?= __('Templates') ?></label>
      </div>
      <div>
        <input type="checkbox" id="reusables" name="category[]" value="reusables" />
        <label for="reusables"><?= __('Reusable Blocks') ?></label>
      </div>
      </p>
      <input type="hidden" name="page" value="b6n-bte" />
      <input type="hidden" name="action" value="export" />
      <?php wp_nonce_field('bte-export') ?>
      <?php submit_button(__('Export Block theme items', 'b6n-bte'), 'primary'); ?>
    </form>
  </section>
</div>

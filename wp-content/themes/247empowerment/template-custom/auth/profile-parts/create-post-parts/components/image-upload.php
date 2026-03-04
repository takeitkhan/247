<?php
/**
 * Image Upload Component
 * Variables (required):
 * - $upload_id: ID for file input
 * - $preview_container_id: ID for preview container
 * - $preview_id: ID for preview image
 * - $preview_name_id: ID for image name display
 */
?>

<div class="posting-image-section mt-3">
    <div class="image-upload-box" id="image-upload-box<?php echo isset($upload_id) && strpos($upload_id, 'schedule') !== false ? '-schedule' : ''; ?>">
        <input type="file" name="post_image" id="<?php echo esc_attr($upload_id); ?>" class="d-none" accept="image/*,video/*">
        <label for="<?php echo esc_attr($upload_id); ?>" class="image-upload-label">
            <div class="upload-icon">
                <i class="bi bi-image"></i>
            </div>
            <div class="upload-text">
                <p class="mb-1 fw-bold">Add Image or Video</p>
                <p class="small text-muted mb-0">or drag and drop</p>
            </div>
        </label>
    </div>
    
    <!-- Image Preview with Thumbnail -->
    <div class="image-preview-container" id="<?php echo esc_attr($preview_container_id); ?>" style="display: none;">
        <div class="position-relative">
            <img id="<?php echo esc_attr($preview_id); ?>" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px; object-fit: cover; width: 100%;">
            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-image-btn" aria-label="Remove image">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <div class="mt-2 image-info">
            <small id="<?php echo esc_attr($preview_name_id); ?>" class="text-muted"></small>
        </div>
    </div>
</div>

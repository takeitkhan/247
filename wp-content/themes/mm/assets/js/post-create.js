jQuery(document).ready(function ($) {
    $('#createPostForm').on('submit', function (e) {
        e.preventDefault();

        let content = $('textarea[name="post_content"]').val();
        let image = $('#photoUpload')[0].files[0];

        let formData = new FormData(this);
        formData.set('action', 'create_post');
        formData.set('create_post_nonce', ajax_object.nonce);

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success(res) {
                if (res.success) {
                    alert('Post created successfully!');
                    location.reload();
                } else {
                    alert(res.data?.message || 'Something went wrong');
                }
            },
            error(xhr, status, err) {
                console.error('AJAX error:', status, err);
                alert('AJAX error occurred.');
            }
        });
    });
});


jQuery(document).ready(function ($) {
    $('.read-more-text').on('click', function () {
        let $this = $(this);
        let parent = $this.closest('.post-content-text');
        let full = parent.data('full');
        let trimmed = parent.data('trimmed');

        if ($this.hasClass('expanded')) {
            // Collapse to trimmed
            parent.html(trimmed + ' <span class="read-more-text text-primary" style="cursor:pointer;"> Read more</span>');
        } else {
            // Expand to full
            parent.html(full + '<br><span class="read-more-text text-primary expanded" style="cursor:pointer;"> Show less</span>');
        }
    });
});

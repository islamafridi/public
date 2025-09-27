jQuery(document).ready(function($) {
    var ajaxUrl = apktemplatesData.ajaxUrl;
    var isAddingRow = false; // Flag to prevent multiple row additions

    // Clear size field on URL input selection
    $(document).on('select focus', '.download-url', function() {
        var $urlInput = $(this);
        var index = $urlInput.attr('id').replace('download_url_', '');
        var $sizeInput = $('#download_size_' + index);
        $sizeInput.val(''); // Clear size when selecting the input
        console.log('Cleared size for download_size_' + index + ' on select/focus');
    });

    // Event delegation for 'input' and 'paste' events on download-url inputs
    $(document).on('input paste', '.download-url', function(e) {
        var $urlInput = $(this);
        var index = $urlInput.attr('id').replace('download_url_', '');
        var $sizeInput = $('#download_size_' + index);
        var url = $urlInput.val().trim();

        console.log('Event triggered (' + e.type + ') on download_url_' + index + ': ' + url); // Debug log

        if (!url) {
            $sizeInput.val('');
            console.log('URL empty, cleared size for download_size_' + index);
            return;
        }

        if (!url.match(/^https?:\/\/.+/)) {
            $sizeInput.val('Invalid URL');
            console.log('Invalid URL for download_url_' + index);
            return;
        }

        $sizeInput.val('Checking...');

        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            data: {
                action: 'get_file_size',
                url: url
            },
            success: function(response) {
                console.log('AJAX success response for download_url_' + index + ': ', response); // Debug log
                if (response.success) {
                    $sizeInput.val(response.size);
                } else {
                    $sizeInput.val(response.error || 'Error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error for download_url_' + index + ': ', status, error); // Debug log
                $sizeInput.val('Error');
            }
        });
    });

    // Handle adding new rows
    $(document).off('click', '#add-row').on('click', '#add-row', function(e) {
        e.preventDefault(); // Prevent any default behavior
        if (isAddingRow) {
            console.log('Row addition in progress, ignoring click'); // Debug log
            return; // Prevent multiple clicks
        }
        isAddingRow = true;

        console.log('Add row button clicked'); // Debug log

        var $tbody = $('#download-table tbody');
        var $newRow = $tbody.find('.empty-d-row').clone(true).removeClass('empty-d-row').show();
        $tbody.append($newRow); // Append only one row at the bottom

        // Update IDs for all visible rows
        $tbody.find('tr:not(.empty-d-row)').each(function(index) {
            $(this).find('.download-url').attr('id', 'download_url_' + index);
            $(this).find('.download-size').attr('id', 'download_size_' + index);
            console.log('Updated IDs for row ' + index); // Debug log
        });

        isAddingRow = false; // Reset flag
    });

    // Handle row removal
    $(document).on('click', '.remove-btn', function() {
        console.log('Remove row button clicked'); // Debug log
        var $row = $(this).closest('tr');
        $row.remove();
        // Update IDs for remaining rows
        $('#download-table tbody tr:not(.empty-d-row)').each(function(index) {
            $(this).find('.download-url').attr('id', 'download_url_' + index);
            $(this).find('.download-size').attr('id', 'download_size_' + index);
            console.log('Updated IDs after removal for row ' + index); // Debug log
        });
    });

    // Handle set largest size to app size
    $(document).on('click', '#set-largest-size', function() {
        console.log('Set largest size button clicked'); // Debug log

        var sizesInBytes = [];
        $('.download-size').each(function() {
            var sizeStr = $(this).val().trim();
            var bytes = parseSize(sizeStr);
            if (bytes > 0) {
                sizesInBytes.push(bytes);
            }
        });

        if (sizesInBytes.length === 0) {
            $('#wp_sizes_GP').val('0M');
            return;
        }

        var maxBytes = Math.max(...sizesInBytes);
        var formatted = formatAppSize(maxBytes);
        $('#wp_sizes_GP').val(formatted);
    });

    // Parse size string to bytes
    function parseSize(sizeStr) {
        var parts = sizeStr.match(/(\d+\.?\d*)\s*([KMG])B?/i);
        if (!parts) return 0;
        var num = parseFloat(parts[1]);
        var unit = parts[2].toUpperCase();
        if (unit === 'G') return num * 1024 * 1024 * 1024;
        if (unit === 'M') return num * 1024 * 1024;
        if (unit === 'K') return num * 1024;
        return 0;
    }

    // Format bytes to app size string (e.g., '290M' or '1.24G')
    function formatAppSize(bytes) {
        if (bytes >= 1073741824) {
            var gb = bytes / 1073741824;
            gb = Math.round(gb * 100) / 100; // Round to 2 decimals
            if (gb % 1 === 0) {
                return parseInt(gb) + ' GB';
            } else {
                return gb.toFixed(2) + ' GB';
            }
        } else {
            var mb = Math.round(bytes / 1048576); // Round to nearest integer
            return mb + ' MB';
        }
    }
});
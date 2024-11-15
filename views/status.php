<!-- =====================
	Trade Account Status
	===================== -->
<div class="woocommerce-info">
	Have a coupon? <a href="#" class="showcoupon">Click here to enter your code</a>
</div>

<?php
/* Template Name: Trade Account Status */

// Retrieve the 'status' parameter from the query string
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Define default content if no status is provided or if an invalid status is passed
$content = '<h1>Trade Account Status</h1><p>Please provide a valid status.</p>';

switch ($status) {
	case 'success':
		$content = '<h1>Trade Account Success</h1><p>Congratulations! Your trade account has been successfully created.</p>';
		break;
	case 'declined':
		$content = '<h1>Trade Account Declined</h1><p>Unfortunately, your trade account application has been declined.</p>';
		break;
	case 'pending':
		$content = '<h1>Trade Account Pending</h1><p>Your trade account application is currently pending.</p>';
		break;
}

// Output content with header and footer
get_header();
echo $content;
get_footer();

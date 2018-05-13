<?php declare(strict_types=1);
namespace Sgdg\Frontend\Shortcode;

function register() : void
{
	add_shortcode('sgdg', '\\Sgdg\\Frontend\\Shortcode\\render');
}
function render(array $atts = []) : string
{
	wp_enqueue_script('sgdg_masonry');
	wp_enqueue_script('sgdg_imagesloaded');
	wp_enqueue_script('sgdg_imagelightbox_script');
	wp_enqueue_script('sgdg_gallery_init');
	wp_localize_script('sgdg_gallery_init', 'sgdg_jquery_localize', [
		'thumbnail_size' => get_option('sgdg_thumbnail_size', \Sgdg_plugin::DEFAULT_THUMBNAIL_SIZE),
		'thumbnail_spacing' => get_option('sgdg_thumbnail_spacing', \Sgdg_plugin::DEFAULT_THUMBNAIL_SPACING),
		'preview_speed' => get_option('sgdg_preview_speed', \Sgdg_plugin::DEFAULT_PREVIEW_SPEED),
		'preview_arrows' => (get_option('sgdg_preview_arrows', \Sgdg_plugin::DEFAULT_PREVIEW_ARROWS) === '1' ? 'true' : 'false'),
		'preview_closebutton' => (get_option('sgdg_preview_closebutton', \Sgdg_plugin::DEFAULT_PREVIEW_CLOSEBUTTON) === '1' ? 'true' : 'false'),
		'preview_quitOnEnd' => (get_option('sgdg_preview_loop', \Sgdg_plugin::DEFAULT_PREVIEW_LOOP) === '1' ? 'false' : 'true'),
		'preview_activity' => (get_option('sgdg_preview_activity', \Sgdg_plugin::DEFAULT_PREVIEW_ACTIVITY) === '1' ? 'true' : 'false')
	]);
	wp_enqueue_style('sgdg_imagelightbox_style');
	wp_enqueue_style('sgdg_gallery_css');
	wp_add_inline_style('sgdg_gallery_css', '.grid-item { margin-bottom: ' . intval(get_option('sgdg_thumbnail_spacing', \Sgdg_plugin::DEFAULT_THUMBNAIL_SPACING) - 7) . 'px; width: ' . get_option('sgdg_thumbnail_size', \Sgdg_plugin::DEFAULT_THUMBNAIL_SIZE) . 'px; }');
	if(isset($atts['name']))
	{
		$client = \Sgdg\Frontend\GoogleAPILib\getDriveClient();
		$path = get_option('sgdg_root_dir', ['root']);
		$root = end($path);
		$pageToken = null;
		do
		{
			$optParams = [
				'q' => '"' . $root . '" in parents and trashed = false',
				'supportsTeamDrives' => true,
				'includeTeamDriveItems' => true,
				'pageToken' => $pageToken,
				'pageSize' => 1000,
				'fields' => 'nextPageToken, files(id, name)'
			];
			$response = $client->files->listFiles($optParams);
			foreach($response->getFiles() as $file)
			{
				if($file->getName() == $atts['name'])
				{
					return render_gallery($file->getId());
				}
			}
			$pageToken = $response->pageToken;
		}
		while($pageToken != null);
	}
	return esc_html__('No such gallery found.', 'skaut-google-drive-gallery');
}

function render_gallery($id) : string
{
	$client = \Sgdg\Frontend\GoogleAPILib\getDriveClient();
	$ret = '<div class="grid">';
	$pageToken = null;
	do
	{
		$optParams = [
			'q' => '"' . $id . '" in parents and mimeType contains "image/" and trashed = false',
			'supportsTeamDrives' => true,
			'includeTeamDriveItems' => true,
			'pageToken' => $pageToken,
			'pageSize' => 1000,
			'fields' => 'nextPageToken, files(thumbnailLink)'
		];
		$response = $client->files->listFiles($optParams);
		foreach($response->getFiles() as $file)
		{
			$ret .= '<div class="grid-item"><a class="sgdg-grid-a" data-imagelightbox="a" href="' . substr($file->getThumbnailLink(), 0, -3) . get_option('sgdg_preview_size', \Sgdg_plugin::DEFAULT_PREVIEW_SIZE) . '"><img class="sgdg-grid-img" src="' . substr($file->getThumbnailLink(), 0, -4) . 'w' . get_option('sgdg_thumbnail_size', \Sgdg_plugin::DEFAULT_THUMBNAIL_SIZE) . '"></a></div>';
		}
		$pageToken = $response->pageToken;
	}
	while($pageToken != null);
	$ret .= '</div>';
	return $ret;
}
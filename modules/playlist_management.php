<?php
/**
 * 
 * Functionality relating to video playlists
 * 
 */
 
 /*
 * Create a new playlist and add videos
 */
function s3_video_create_playlist()
{
	$pluginSettings = s3_video_check_plugin_settings();	
		
	if ((!empty( filter_input(INPUT_POST, 'playlist_contents') )) && (!empty(  filter_input(INPUT_POST, 'playlist_name') ))) {

		require_once(WP_PLUGIN_DIR . '/s3-video/includes/playlist_management.php');
		
		$playlistManagement = new s3_playlist_management();
		
		$playlistName = sanitize_title( filter_input(INPUT_POST, 'playlist_name') );
		$playlistExists = $playlistManagement->getPlaylistsByTitle($playlistName);

		if (!$playlistExists) {

			$playlistResult = $playlistManagement->createPlaylist($playlistName, filter_input(INPUT_POST, 'playlist_contents') );

			if (!$playlistResult) {
	    		$errorMsg = 'An error occurred whilst creating the play list.';			
			} else {
				$successMsg = 'New playlist saved successfully.';			
			} 

		} else {
	    		$errorMsg = 'A playlist with this name already exists.';					
		}  
	}
	$existingVideos= s3_video_get_all_existing_video($pluginSettings);

	require_once(WP_PLUGIN_DIR . '/s3-video/views/playlist-management/create_playlist.php');	
}
 
/*
 *	Manage existing playlists of S3 based media 
 */
function s3_video_show_playlists()
{
	$pluginSettings = s3_video_check_plugin_settings();			

	require_once(WP_PLUGIN_DIR . '/s3-video/includes/playlist_management.php');
	
	$playlistManagement = new s3_playlist_management();
	
	if (!empty($_GET['delete'])) {
		$playlistId = preg_replace('/[^0-9]/Uis', '', filter_input(INPUT_GET, 'delete') );

		$playlistManagement->deletePlaylist($playlistId);
	}

	$editValue = filter_input(INPUT_GET, 'edit');
	$reorderValue = filter_input(INPUT_GET, 'reorder');

	if ((!empty($editValue)) && (is_numeric($editValue)) || (!empty($reorderValue)) && (is_numeric($reorderValue))) {
		
		if (!empty($editValue)) {
			$playlistId = preg_replace('/[^0-9]/Uis', '', $editValue);
			 
			$playlistContents = filter_input(INPUT_POST, 'playlist_contents');

			if (!empty( $playlistContents )) {
				$playlistManagement->deletePlaylistVideos($playlistId);
				$playlistManagement->updatePlaylistVideos($playlistId, $playlistContents );	
				$playlistUpdated = 1;
			} 

			$existingVideos = $playlistManagement->getPlaylistVideos($playlistId);	
			$s3Videos = s3_video_get_all_existing_video($pluginSettings);
		
			require_once(WP_PLUGIN_DIR . '/s3-video/views/playlist-management/edit_playlist.php');
		} 
		
		if (!empty($reorderValue)) {
			$playlistId = preg_replace('/[^0-9]/Uis', '', $reorderValue);
			$playlistVideos = $playlistManagement->getPlaylistVideos($playlistId);

			require_once(WP_PLUGIN_DIR . '/s3-video/views/playlist-management/reorder_playlist.php');	
		} 	
		
	} else {
		/*
		 * If we don't have a playlist to display a list of them all  
		 */
		$existingPlaylists = $playlistManagement->getAllPlaylists();	

		require_once(WP_PLUGIN_DIR . '/s3-video/views/playlist-management/playlist_management.php');
	}
	
}

/**
 * 
 * Insert a playlist into the editor for a page or post through the media manager
 * 
 */
function s3video_playlist_media_manager()
{
	$playlistId = filter_input(INPUT_POST, 'insertPlaylistId');
	
	if ((isset($playlistId)) && (!empty($playlistId))) {
		
		$insertHtml = "[S3_embed_playlist id='" . filter_input(INPUT_POST, 'insertPlaylistId') . "']";
		media_send_to_editor($insertHtml);
		die();
	}
		
	$pluginSettings = s3_video_check_plugin_settings();
	
	// Load playlist management class
	require_once(WP_PLUGIN_DIR . '/s3-video/includes/playlist_management.php');
	$playlistManagement = new s3_playlist_management();

	// Load all of the existing playlists
	$existingPlaylists = $playlistManagement->getAllPlaylists();	
	require_once(WP_PLUGIN_DIR . '/s3-video/views/playlist-management/media_manager_show_playlists.php');	
} 
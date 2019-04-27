(function( $ ) {
	'use strict';

	$(function() {
    deploy_options['github'] = {
        exportSteps: [
            'github_prepare_export',
            'github_upload_files',
            'finalize_deployment'
        ],
        required_fields: {
          ghToken: 'Please specify your GitHub personal access token in order to deploy to GitHub.',
          ghRepo: 'Please specify your GitHub repository name in order to deploy to GitHub.',
          ghBranch: 'Please specify which branch in your GitHub repository you want to deploy to.',
        },
        repo_field: {
          field: 'ghRepo',
          message: "Please ensure your GitHub repo is specified as USER_OR_ORG_NAME/REPO_NAME\n"
        }
    };

    status_descriptions['github_prepare_export'] = 'Preparing files for GitHub deployment';
    status_descriptions['github_upload_files'] = 'Deploying files via GitHub';
    status_descriptions['cloudfront_invalidate_all_items'] = 'Invalidating CloudFront cache';
  }); // end DOM ready

})( jQuery );

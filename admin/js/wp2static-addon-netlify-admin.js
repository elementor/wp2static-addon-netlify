(function( $ ) {
	'use strict';

	$(function() {
    deploy_options['netlify'] = {
        exportSteps: [
            'netlify_prepare_export',
            'netlify_upload_files',
            'finalize_deployment'
        ],
        required_fields: {
          ghToken: 'Please specify your Netlify personal access token in order to deploy to Netlify.',
          ghRepo: 'Please specify your Netlify repository name in order to deploy to Netlify.',
          ghBranch: 'Please specify which branch in your Netlify repository you want to deploy to.',
        },
        repo_field: {
          field: 'ghRepo',
          message: "Please ensure your Netlify repo is specified as USER_OR_ORG_NAME/REPO_NAME\n"
        }
    };

    status_descriptions['netlify_prepare_export'] = 'Preparing files for Netlify deployment';
    status_descriptions['netlify_upload_files'] = 'Deploying files via Netlify';
    status_descriptions['cloudfront_invalidate_all_items'] = 'Invalidating CloudFront cache';
  }); // end DOM ready

})( jQuery );

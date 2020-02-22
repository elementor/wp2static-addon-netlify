(function( $ ) {
	'use strict';

	$(function() {
    deploy_options['netlify'] = {
      exportSteps: [
          'netlify_prepare_export',
          'netlify_transfer_files',
          'cloudfront_invalidate_all_items',
          'finalize_deployment'
      ],
      required_fields: {
        netlifyKey: 'Please input an Netlify Key in order to authenticate when using the Netlify deployment method.',
        netlifySecret: 'Please input an Netlify Secret in order to authenticate when using the Netlify deployment method.',
        netlifyBucket: 'Please input the name of the Netlify bucket you are trying to deploy to.',
      }
    };

    status_descriptions['netlify_prepare_export'] = 'Preparing files for Netlify deployment';
    status_descriptions['netlify_transfer_files'] = 'Deploying files to Netlify';
    status_descriptions['cloudfront_invalidate_all_items'] = 'Invalidating CloudFront cache';
  }); // end DOM ready

})( jQuery );

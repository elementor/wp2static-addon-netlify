(function( $ ) {
	'use strict';

	$(function() {
    deploy_options['netlify'] = {
        exportSteps: [
            'netlify_do_export',
            'finalize_deployment'
        ],
        required_fields: {
          netlifyPersonalAccessToken: 'Please specify your Netlify personal access token in order to deploy to Netlify.',
          netlifySiteID: 'Please specify the id of your Netlify site you want to deploy to.',
        },
    };
    status_descriptions['netlify_do_export'] = 'Deploying to Netlify';
  }); // end DOM ready

})( jQuery );

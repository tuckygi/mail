/**
 * ownCloud - Mail
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Christoph Wurst 2015
 */

define(function(require) {
	'use strict';

	var Marionette = require('marionette');
	var $ = require('jquery');
	var NavigationView = require('views/navigation');

	return Marionette.LayoutView.extend({
		el: $('#app'),
		regions: {
			navigation: '#app-navigation',
			content: '#app-content'
		},
		initialize: function() {
			this.bindUIElements();
		},
		render: function() {
			// This view doesn't need rendering
		},
		onBeforeShow: function() {
			this.showChildView('navigation', new NavigationView());
		}
	});
});

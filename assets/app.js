/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

const $ = require('jquery');
global.$ = global.jQuery = $;

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
import './styles/main.css';

// start the Stimulus application
import './bootstrap';

const routes = require('./scripts/fos_js_router.json')
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min'
Routing.setRoutingData(routes)
global.Routing = Routing

import './scripts/ImportForm/CSVSettingsGenerator';
import './scripts/ImportForm/SubmitForm';
import './scripts/Mercure/ResultListener';
import './scripts/Mercure/NewProductListener';
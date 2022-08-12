const $ = require('jquery');
global.$ = global.jQuery = $;

const routes = require('./scripts/fos_js_router.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
Routing.setRoutingData(routes);
global.Routing = Routing;

//css
import './styles/app.scss';
import './styles/main.css';

//js
import './bootstrap';
import './scripts/ImportForm/CSVSettingsGenerator';
import './scripts/ImportForm/SubmitForm';
import './scripts/Mercure/ResultListener';
import './scripts/Mercure/NewProductListener';
import './scripts/Mercure/ShortResultListener';